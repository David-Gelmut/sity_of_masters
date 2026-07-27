<?php

namespace App\Http\Controllers\Chat;

use App\Events\ChatDeleted;
use App\Events\ClearChat;
use App\Events\CreateChat;
use App\Events\GroupChatCreated;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{

    public function createGroup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'user_ids' => 'required|array|min:1', # ID участников, кого добавляем
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $userIds = array_merge([auth()->id()], $validated['user_ids']);

        $group = $this->createGroupChat($validated['title'], $userIds, auth()->id());

         broadcast(new GroupChatCreated($userIds, $group->id))->toOthers();

        return response()->json(['status' => 'success', 'data' => $group->toArray()], 201);

    }

    public function createGroupChat(string $title, array $userIds, int $creatorId): Chat
    {
        return DB::transaction(function () use ($title, $userIds, $creatorId) {

            $chat = Chat::create([
                'type' => 'group',
                'title' => $title,
                'creator_id' => $creatorId,
            ]);

            // Привязываем всех участников к группе через промежуточную таблицу
            foreach ($userIds as $userId) {
                $chat->users()->attach($userId, [
                    'role' => $userId === $creatorId ? 'admin' : 'member'
                ]);
            }

            return $chat->load('users');
        });
    }

    public function getChats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $chats = $request->user()->chats()
            ->with([
               /* 'users' => function ($query) use ($userId) {
                $query
                    ->select('users.id', 'users.name', 'users.email', 'users.role','users.avatar_path')
                    ->join('chats', 'chats.id', '=', 'chat_user.chat_id')
                    ->where(function ($subQuery) use ($userId) {
                        $subQuery->where('chats.type', 'group') // Если группа — разрешаем всех
                        ->orWhere('users.id', '!=', $userId); // Если peer — убираем текущего юзера
                    });
            },*/
                // Подгружаем только одно самое последнее сообщение вместе с файлами
                'messages' => function ($query) {
                    $query->latest()->with('attachments')->limit(1);
                }
            ])
            ->get()
            ->map(function ($chat) use ($userId, $request) {

                $chat->load(['users' => function ($query) use ($userId, $chat) {
                    $query->select('users.id', 'users.name', 'users.email', 'users.role', 'users.avatar_path');
                    $query->when($chat->type !== 'group', function ($q) use ($userId) {
                        return $q->where('users.id', '!=', $userId);
                    });
                }]);

                $lastMessage = $chat->messages->first();

                if ($lastMessage && $lastMessage->text) {
                    try {
                        $lastMessage->text = Crypt::decryptString($lastMessage->text);
                    } catch (\Exception $e) {
                        // Если сообщение старое или не зашифровано, оставляем как есть
                    }
                }

                $chat->last_message = $lastMessage;

                // Считаем сообщения, отправленные НЕ мной, у которых read_at равен null
                $chat->unread_count = $chat
                    ->messages()
                    ->where('user_id', '!=', $userId)
                    ->whereNull('read_at')
                    ->count();

                //$chat->setRelation('users', $chat->users->first());

                return $chat;
            });

        return response()->json($chats);
    }

    /**
     * Создать новый чат или вернуть существующий.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id' // ID пользователя, с кем создаем чат
        ]);

        $myId = $request->user()->id;
        $recipientId = (int)$request->user_id;

        // Защита: нельзя создать чат с самим собой
        if ($myId === $recipientId) {
            return response()->json(['message' => 'Вы не можете создать чат с самим собой.'], 422);
        }

        // 1. ПРОВЕРКА: Ищем, есть ли уже общий чат между этими двумя пользователями
        // Проверяем чаты текущего пользователя, у которых среди участников есть recipientId
        $existingChat = $request->user()->chats()
            ->whereHas('users', function ($query) use ($recipientId) {
                $query->where('users.id', $recipientId);
            })
            ->where('type', '!=','group')
            ->first();

        // 2. Если чат уже существует — просто возвращаем его данные во Vue
        if ($existingChat) {
            return response()->json([
                'message' => 'Диалог уже существует.',
                'chat_id' => $existingChat->id
            ]);
        }

        // 3. Если чата нет — создаем новую запись в таблице chats
        $chat = Chat::create([
            'title' => null // Для личных чатов название можно оставить null
        ]);

        // 4. Связываем обоих пользователей с этим чатом в таблице chat_user
        $chat->users()->attach([$myId, $recipientId]);

        broadcast(new CreateChat($chat))->toOthers();

        return response()->json([
            'message' => 'Новый диалог успешно создан.',
            'chat_id' => $chat->id
        ], 201);
    }

    /**
     * Получить список всех пользователей системы для вкладки "Контакты".
     */
    public function getUsers(Request $request): JsonResponse
    {
        $currentUserId = $request->user()->id;

        // Выбираем всех активных пользователей, кроме текущего залогиненного
        $users = \App\Models\User::query()->select('id', 'name', 'email', 'role', 'avatar_path')
            ->where('id', '!=', $currentUserId)
            ->where('status', 'active')
            ->get();

        return response()->json($users);
    }

    /**
     * Очистить историю сообщений в чате.
     */
    public function clearMessages(Request $request, $id): JsonResponse
    {
        $chat = $request->user()->chats()->findOrFail($id);
        //$forAll = $request->input('for_all', false);
        $userId = auth()->id();

       // if($forAll){
            \App\Models\Message::query()
                ->where('chat_id', $chat->id)
                ->delete();
    /*    } else  {
            \App\Models\Message::query()
                ->where('chat_id', $chat->id)
                ->where('user_id', $userId)
                ->delete();
        }*/

        broadcast(new ClearChat($chat))->toOthers();

        return response()->json(['message' => 'История чата успешно очищена.']);
    }

    /**
     * Полностью удалить чат для всех участников.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $chat = $request->user()->chats()->findOrFail($id);
        //$forAll = $request->input('for_all', false);
        $userId = auth()->id();

        // 1. Проверяем права на полное удаление "Для всех" в группах
        if ($chat->type === 'group') {
            $isAdmin = $chat->users()
                ->where('users.id', $userId)
                ->where('chat_user.role', 'admin')
                ->exists();
            if (!$isAdmin) {
                return response()->json(['message' => 'Только админ может уничтожить группу для всех'], 403);
            }
        }
/*
        if ($forAll) {
            // УДАЛЕНИЕ ДЛЯ ВСЕХ: Физически стираем чат и связи из MySQL
            $chat->delete(); // Каскадно удалит сообщения и связи chat_user

            // broadcast(new ChatDeletedForAll($chat->id))->toOthers();
        } else {
            // УДАЛЕНИЕ ТОЛЬКО У СЕБЯ: Просто отвязываем текущего юзера от чата в сводной таблице
            $chat->users()->detach($userId);
        }*/
        $userIds = $chat->users->pluck('id')->toArray();
        $chat->delete();

        broadcast(new ChatDeleted($id, $userIds));

        return response()->json(['status' => 'success']);



       /* $chat = $request->user()->chats()->findOrFail($id);
        $chat->delete(); // Каскадно удалит связи в chat_user и сообщения

        return response()->json(['message' => 'Чат успешно удален.']);*/
    }

    public function sync(Request $request, $chatId): JsonResponse
    {
        $lastId = $request->query('last_id');

        if (!$lastId) {
            return response()->json([]);
        }

        // Вытаскиваем сообщения, которые появились в базе позже, чем есть у клиента
        $newMessages = Message::where('chat_id', $chatId)
            ->where('id', '>', $lastId)
            ->with('user') // подгружаем автора сообщения для Vue шаблона
            ->orderBy('id', 'asc')
            ->get();

        $newMessages->transform(function ($msg) {
            if ($msg->text) {
                try {
                    $msg->text = Crypt::decryptString($msg->text);
                } catch (\Exception $e) {
                    // резервный откат
                }
            }
            return $msg;
        });

        return response()->json($newMessages);
    }

    public function removeUser(Chat $chat, User $user): JsonResponse
    {
        // Проверяем, является ли тот, кто делает запрос, админом группы
        $isCurrentUserAdmin = $chat->users()
            ->where('users.id', auth()->id())
            ->where('chat_user.role', 'admin')
            ->exists();

        if (!$isCurrentUserAdmin) {
            return response()->json(['message' => 'У вас недостаточно прав (Требуется роль Админ)'], 403);
        }

        // Если всё ок — отвязываем пользователя от чата в MySQL
        $chat->users()->detach($user->id);

        return response()->json(['status' => 'success']);
    }

}
