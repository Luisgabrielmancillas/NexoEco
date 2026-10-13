<?php

namespace App\Services;

use App\Models\ProductChat;
use App\Models\SellerAutomation;
use Chatify\Actions\Messages\SendMessage;
use Illuminate\Support\Facades\DB;

class SellerChatAutomation
{
    public function deliver(ProductChat $chat, string $kind): void
    {
        $subscription = app(SellerBenefits::class)->subscription($chat->seller);
        if (! $subscription || ($kind === 'away' && $subscription->plan !== 'pro') || ! $chat->buyer->activo) {
            return;
        }
        $automation = SellerAutomation::where('user_id', $chat->seller_id)->where('kind', $kind)->where('enabled', true)->first();
        if (! $automation || ($kind === 'away' && $this->isOpen($automation->hours ?? []))) {
            return;
        }
        $period = $kind === 'welcome' ? 'once' : now('America/Mexico_City')->toDateString();
        DB::transaction(function () use ($chat, $automation, $kind, $period) {
            // Locking the chat serializes welcome and out-of-hours replies with incoming messages.
            ProductChat::whereKey($chat->id)->lockForUpdate()->firstOrFail();
            $sent = DB::table('chat_automation_deliveries')->insertOrIgnore(['chat_id' => $chat->id, 'kind' => $kind, 'period' => $period, 'created_at' => now(), 'updated_at' => now()]);
            if (! $sent) {
                return;
            }
            $body = strtr($automation->body, ['{comprador}' => $chat->buyer->name, '{producto}' => $chat->producto?->nombre_producto ?? 'este producto']);
            app(SendMessage::class)->handle($chat->conversation, $chat->seller, 'Mensaje automático · '.$body);
            $chat->touch();
        });
    }

    public function isOpen(array $hours): bool
    {
        $now = now('America/Mexico_City');
        $day = (string) $now->isoWeekday();
        $days = array_map('strval', $hours['days'] ?? []);
        $start = $hours['start'] ?? '09:00';
        $end = $hours['end'] ?? '18:00';
        $time = $now->format('H:i');
        if ($start < $end) {
            return in_array($day, $days, true) && $time >= $start && $time < $end;
        }

        // An overnight shift belongs to the day on which it started.
        return (in_array($day, $days, true) && $time >= $start)
            || (in_array((string) $now->copy()->subDay()->isoWeekday(), $days, true) && $time < $end);
    }
}
