<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'reverb';

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.' . $this->message->chat_id)
        ];

        // Находим второго участника чата
        $recipientId = \Illuminate\Support\Facades\DB::table('chat_user')
            ->where('chat_id', $this->message->chat_id)
            ->where('user_id', '!=', $this->message->user_id)
            ->value('user_id');

        if ($recipientId) {
            // Добавляем персональный канал получателя в список рассылки Reverb
            $channels[] = new PrivateChannel('user.' . $recipientId);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        $decryptedText = '';

        if ($this->message->text) {
            try {
                // Расшифровываем перед отправкой в Reverb
                $decryptedText = Crypt::decryptString($this->message->text);
            } catch (\Exception $e) {
                $decryptedText = $this->message->text; // если упало, шлем оригинал
            }
        }

        if ($this->message->parent) {
            try {
                $this->message->parent->text = Crypt::decryptString($this->message->parent->text);
            } catch (\Exception $e) {
                // Если сообщение старое или не зашифровано, оставляем как есть
            }
        }

        return [
            'id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'text' => $decryptedText,
            'user_id' => $this->message->user_id,
            'user_name' => $this->message->user->name,
            'user' => $this->message->user,
            'parent' => $this->message->parent,
            'attachments' => $this->message->attachments,
            'created_at' => $this->message->created_at->toIso8601String(),
            'updated_at' => $this->message->updated_at->toIso8601String(),
        ];
    }
}
