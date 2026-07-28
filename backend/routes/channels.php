<?php

use Illuminate\Support\Facades\Broadcast;

/*Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});*/

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return $user->chats()->where('chat_id', $chatId)->exists() && $user->status === 'active';
});

Broadcast::channel('online', function ($user) {
    // Если пользователь авторизован, возвращаем данные, которые увидят другие
    return [
        'id' => $user->id,
        'name' => $user->name,
        //'avatar' => $user->avatar, // если есть
    ];
});
