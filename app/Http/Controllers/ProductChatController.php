<?php

namespace App\Http\Controllers;

use App\Models\ProductChat;
use App\Models\Producto;
use Chatify\Actions\Messages\SendMessage;
use Chatify\Models\Conversation;
use Chatify\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductChatController extends Controller
{
    public function index(Request $request)
    {
        $sellerArea = $request->routeIs('vendedor.*');
        $request->validate(['estado' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(ProductChat::NEGOTIATION_STATES))]]);
        $chats = ProductChat::where($sellerArea ? 'seller_id' : 'buyer_id', $request->user()->id)
            ->select('product_chats.*')
            ->selectSub(Message::select('body')->whereColumn('conversation_id', 'product_chats.conversation_id')->orderByDesc('created_at')->orderByDesc('id')->limit(1), 'last_message_body')
            ->with(['producto.imagenPrincipal', 'buyer', 'seller'])
            ->when($sellerArea && $request->filled('estado'), fn ($q) => $q->where('negotiation_status', $request->query('estado')))
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('user_id', '!=', $request->user()->id)
                ->whereRaw('ch_messages.created_at > COALESCE((SELECT last_read_at FROM ch_conversation_participants WHERE conversation_id = product_chats.conversation_id AND user_id = ?), ?)', [$request->user()->id, '1970-01-01 00:00:00'])])
            ->latest('updated_at')->paginate(20)->withQueryString();

        return view('chat.index', compact('chats', 'sellerArea'));
    }

    public function start(Request $request, Producto $producto)
    {
        $seller = $producto->tienda->user;
        abort_unless($seller && $seller->activo && $seller->tieneTipo('vendedor'), 404);
        abort_if($seller->id === $request->user()->id, 403, 'No puedes iniciar un chat contigo mismo.');
        $chat = DB::transaction(function () use ($request, $producto, $seller) {
            Producto::whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            $existing = ProductChat::where('id_producto', $producto->getKey())->where('buyer_id', $request->user()->id)->first();
            if ($existing) {
                return $existing;
            }
            $conversation = Conversation::create(['type' => 'direct', 'name' => $producto->nombre_producto, 'created_by' => $request->user()->id]);
            foreach ([$request->user()->id, $seller->id] as $id) {
                $conversation->participants()->create(['user_id' => $id, 'role' => 'member']);
            }

            $chat = ProductChat::create(['id_producto' => $producto->getKey(), 'buyer_id' => $request->user()->id, 'seller_id' => $seller->id, 'conversation_id' => $conversation->id]);
            app(\App\Services\SellerChatAutomation::class)->deliver($chat, 'welcome');

            return $chat;
        });

        return redirect()->route('chat.show', $chat);
    }

    private function authorizeChat(Request $request, ProductChat $chat): void
    {
        abort_unless(ProductChat::forUser($request->user())->whereKey($chat->id)->exists(), 404);
    }

    public function show(Request $request, ProductChat $chat)
    {
        $this->authorizeChat($request, $chat);
        $chat->load(['producto', 'buyer', 'seller']);

        $sellerArea = $chat->seller_id === $request->user()->id;

        $subscription = $sellerArea ? app(\App\Services\SellerBenefits::class)->subscription($request->user()) : null;
        $quickReplies = $subscription ? \App\Models\SellerQuickReply::where('user_id', $request->user()->id)->orderBy('id')
            ->when($subscription->plan === 'impulso', fn ($q) => $q->limit(5))->get() : collect();

        return view('chat.show', compact('chat', 'sellerArea', 'subscription', 'quickReplies'));
    }

    public function messages(Request $request, ProductChat $chat)
    {
        $this->authorizeChat($request, $chat);
        $request->validate(['before' => ['nullable', 'uuid']]);
        $query = $chat->conversation->messages();
        $direction = $request->filled('before') ? 'before' : null;
        if ($direction) {
            $cursor = $chat->conversation->messages()->whereKey($request->input($direction))->firstOrFail();
            $operator = $direction === 'before' ? '<' : '>';
            $query->where(fn ($q) => $q->where('created_at', $operator, $cursor->created_at)->orWhere(fn ($q) => $q->where('created_at', $cursor->created_at)->where('id', $operator, $cursor->id)));
        }
        $messages = $query->orderByDesc('created_at')->orderByDesc('id')->limit(50)->get()->reverse()->values();
        $chat->conversation->participants()->where('user_id', $request->user()->id)->update(['last_read_at' => now()]);
        $request->user()->unreadNotifications()->where('data->event', 'chat.message')
            ->where('data->chat_id', $chat->id)->whereIn('data->message_id', $messages->pluck('id'))->update(['read_at' => now()]);

        return response()->json(['messages' => $messages->map(fn ($m) => ['id' => $m->id, 'body' => $m->body, 'mine' => $m->user_id === $request->user()->id, 'time' => $m->created_at->toIso8601String()])]);
    }

    public function send(Request $request, ProductChat $chat, SendMessage $send)
    {
        $this->authorizeChat($request, $chat);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        abort_unless($chat->buyer->activo && $chat->seller->activo, 403);
        DB::transaction(function () use ($send, $chat, $request, $data) {
            ProductChat::whereKey($chat->id)->lockForUpdate()->firstOrFail();
            $send->handle($chat->conversation, $request->user(), $data['body']);
            if ($request->user()->id === $chat->buyer_id) {
                app(\App\Services\SellerChatAutomation::class)->deliver($chat, 'away');
            }
            $chat->touch();
        });

        return response()->json(['sent' => true]);
    }
}
