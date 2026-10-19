<?php

namespace App\Observers;

use App\Models\ProductChat;
use App\Notifications\BuyerActivityNotification;
use Chatify\Models\Message;
use Illuminate\Support\Str;

class ProductMessageObserver
{
    public function created(Message $message): void
    {
        $chat = ProductChat::with(['buyer', 'seller', 'producto'])->where('conversation_id', $message->conversation_id)->first();
        if (! $chat || ! in_array((int) $message->user_id, [$chat->buyer_id, $chat->seller_id], true)) {
            return;
        }
        $recipient = (int) $message->user_id === $chat->buyer_id ? $chat->seller : $chat->buyer;
        $sender = (int) $message->user_id === $chat->buyer_id ? $chat->buyer : $chat->seller;
        $recipient?->notify(new BuyerActivityNotification(
            'Nuevo mensaje de '.$sender->name,
            ($chat->producto?->nombre_producto ?? 'Consulta de producto').': '.Str::limit($message->body ?? 'Te envió un archivo.', 180),
            route('chat.show', $chat),
            ['event' => 'chat.message', 'chat_id' => $chat->id, 'message_id' => $message->id],
        ));
    }
}
