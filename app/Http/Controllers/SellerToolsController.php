<?php

namespace App\Http\Controllers;

use App\Models\Apartado;
use App\Models\ProductChat;
use App\Models\Producto;
use App\Models\SellerAutomation;
use App\Models\SellerQuickReply;
use App\Models\SellerStoreDesign;
use App\Models\User;
use App\Services\SellerBenefits;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SellerToolsController extends Controller
{
    public function index(Request $request, SellerBenefits $benefits)
    {
        $subscription = $benefits->subscription($request->user());
        $replies = SellerQuickReply::where('user_id', $request->user()->id)->orderBy('id')->get();
        $automations = SellerAutomation::where('user_id', $request->user()->id)->get()->keyBy('kind');
        $design = SellerStoreDesign::where('user_id', $request->user()->id)->first();
        $tienda = $request->user()->tiendas()->orderBy('id_tienda')->first();
        $products = $tienda?->productos()->orderBy('nombre_producto')->paginate(12, ['id_producto', 'nombre_producto']);
        $pins = $subscription?->pinnedProducts()->pluck('productos.id_producto')->all() ?? [];

        return view('vendedor.herramientas', compact('subscription', 'replies', 'automations', 'design', 'tienda', 'products', 'pins'));
    }

    public function saveReply(Request $request, SellerBenefits $benefits, ?SellerQuickReply $reply = null)
    {
        abort_if($reply && $reply->user_id !== $request->user()->id, 404);
        $data = $request->validate(['title' => ['required', 'string', 'max:80'], 'body' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($request, $benefits, $reply, $data) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $subscription = $benefits->require($request->user());
            if (! $reply && $benefits->reached($subscription, 'quick_reply', SellerQuickReply::where('user_id', $request->user()->id)->count())) {
                throw ValidationException::withMessages(['respuestas' => 'Impulso permite hasta 5 respuestas rápidas. Elimina una o elige Pro para guardar más.']);
            }
            if ($reply) {
                $reply->update($data);
            } else {
                SellerQuickReply::create($data + ['user_id' => $request->user()->id]);
            }
        });

        return back()->with('success', 'Respuesta rápida guardada. Puedes insertarla en el chat y editarla antes de enviar.');
    }

    public function deleteReply(Request $request, SellerQuickReply $reply)
    {
        abort_unless($reply->user_id === $request->user()->id, 404);
        $reply->delete();

        return back()->with('success', 'Respuesta rápida eliminada.');
    }

    public function automation(Request $request, SellerBenefits $benefits)
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(['welcome', 'away'])], 'body' => ['required', 'string', 'max:2000'], 'enabled' => ['required', 'boolean'],
            'hours.days' => ['required_if:kind,away', 'array', 'min:1', 'max:7'], 'hours.days.*' => ['integer', 'between:1,7', 'distinct'],
            'hours.start' => ['required_if:kind,away', 'date_format:H:i'], 'hours.end' => ['required_if:kind,away', 'date_format:H:i', 'different:hours.start'],
        ]);
        $benefits->require($request->user(), $data['kind'] === 'away');
        SellerAutomation::updateOrCreate(['user_id' => $request->user()->id, 'kind' => $data['kind']], ['body' => $data['body'], 'enabled' => $data['enabled'], 'hours' => $data['kind'] === 'away' ? $data['hours'] : null]);

        return back()->with('success', 'Mensaje automático actualizado. Solo se enviará mientras tu plan esté activo.');
    }

    public function design(Request $request, SellerBenefits $benefits)
    {
        $subscription = $benefits->require($request->user());
        $data = $request->validate(['theme' => ['required', Rule::in(array_keys(SellerStoreDesign::THEMES))], 'tagline' => ['nullable', 'string', 'max:120'], 'story' => ['nullable', 'string', 'max:2000'], 'layout' => ['required', Rule::in(['clasico', 'compacto'])]]);
        if ($subscription->plan !== 'pro' && (filled($data['story'] ?? null) || $data['layout'] !== 'clasico')) {
            throw ValidationException::withMessages(['personalizacion' => 'La historia ampliada y el diseño compacto requieren Pro.']);
        }
        if ($subscription->plan !== 'pro') {
            unset($data['story'], $data['layout']);
        }
        SellerStoreDesign::updateOrCreate(['user_id' => $request->user()->id], $data);

        return back()->with('success', 'Personalización guardada. Abre tu tienda para ver el resultado.');
    }

    public function pin(Request $request, Producto $producto, SellerBenefits $benefits)
    {
        abort_unless($producto->tienda?->id_vendedor === $request->user()->id, 404);
        $request->validate(['pinned' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $producto, $benefits) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $subscription = $benefits->require($request->user());
            if (! $request->boolean('pinned')) {
                $subscription->pinnedProducts()->detach($producto->getKey());

                return;
            }
            if ($subscription->pinnedProducts()->whereKey($producto->getKey())->exists()) {
                return;
            }
            if ($benefits->reached($subscription, 'pin', $subscription->pinnedProducts()->count())) {
                throw ValidationException::withMessages(['fijados' => 'Llegaste al límite de productos fijados de tu plan. Quita uno para fijar otro.']);
            }
            $subscription->pinnedProducts()->attach($producto->getKey());
        });

        return back()->with('success', 'Productos fijados actualizados.');
    }

    public function negotiation(Request $request, ProductChat $chat)
    {
        abort_unless($chat->seller_id === $request->user()->id, 404);
        $data = $request->validate(['negotiation_status' => ['required', Rule::in(array_keys(ProductChat::NEGOTIATION_STATES))], 'negotiation_notes' => ['nullable', 'string', 'max:3000'], 'reported_sale_amount' => ['nullable', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2']]);
        if ($data['negotiation_status'] !== 'concretada') {
            $data['reported_sale_amount'] = null;
        }
        $chat->update($data + ['sale_reported_at' => $data['negotiation_status'] === 'concretada' ? ($chat->sale_reported_at ?? now()) : null]);

        return back()->with('success', 'Negociación actualizada. Esta anotación no confirma pagos ni modifica apartados.');
    }

    public function fulfillment(Request $request, Apartado $apartado, SellerBenefits $benefits)
    {
        abort_unless($apartado->seller_id === $request->user()->id, 404);
        $data = $request->validate([
            'fulfillment_status' => ['required', Rule::in(array_keys(Apartado::FULFILLMENT_STATES))], 'seller_notes' => ['nullable', 'string', 'max:3000'],
            'delivery_at' => ['nullable', 'date_format:Y-m-d\TH:i'], 'follow_up_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ]);
        if ($request->hasAny(['delivery_at', 'follow_up_at'])) {
            $benefits->require($request->user(), true);
            foreach (['delivery_at', 'follow_up_at'] as $field) {
                $data[$field] = filled($data[$field] ?? null) ? Carbon::createFromFormat('Y-m-d\TH:i', $data[$field], 'America/Mexico_City')->startOfMinute()->utc() : null;
            }
        }
        // Deliberately excludes estado, monto, condiciones and all provider payment fields.
        DB::transaction(function () use ($apartado, $data, $request) {
            $locked = Apartado::whereKey($apartado->id)->lockForUpdate()->firstOrFail();
            if ($request->hasAny(['delivery_at', 'follow_up_at'])) {
                $unchanged = $locked->follow_up_at && $data['follow_up_at'] && $locked->follow_up_at->equalTo($data['follow_up_at']);
                if ($data['follow_up_at'] && $data['follow_up_at']->isPast() && ! $unchanged) {
                    throw ValidationException::withMessages(['follow_up_at' => 'El recordatorio debe ser una fecha futura.']);
                }
                if (! $unchanged) {
                    $data['reminded_at'] = null;
                }
            }
            $locked->update($data);
        });

        return back()->with('success', 'Seguimiento de entrega actualizado. El estado del anticipo no cambia.');
    }

    public function calendar(Request $request, SellerBenefits $benefits)
    {
        $subscription = $benefits->subscription($request->user());
        $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = Carbon::createFromFormat('!Y-m', $request->query('month', now('America/Mexico_City')->format('Y-m')), 'America/Mexico_City');
        $appointments = $subscription?->plan === 'pro' ? Apartado::where('seller_id', $request->user()->id)->whereBetween('delivery_at', [$month->copy()->startOfMonth()->utc(), $month->copy()->endOfMonth()->utc()])->with('buyer')->orderBy('delivery_at')->get() : collect();

        return view('vendedor.calendario', compact('subscription', 'month', 'appointments'));
    }
}
