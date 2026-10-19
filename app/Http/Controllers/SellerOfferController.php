<?php

namespace App\Http\Controllers;

use App\Models\CommercialOffer;
use App\Models\User;
use App\Services\SellerBenefits;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SellerOfferController extends Controller
{
    public function index(Request $request, SellerBenefits $benefits)
    {
        $subscription = $benefits->subscription($request->user());
        $tienda = $request->user()->tiendas()->orderBy('id_tienda')->first();
        $products = $tienda?->productos()->orderBy('nombre_producto')->get(['id_producto', 'nombre_producto']);
        $offers = CommercialOffer::where('user_id', $request->user()->id)->with('producto')->latest()->paginate(12);

        return view('vendedor.promociones', compact('subscription', 'products', 'offers'));
    }

    public function save(Request $request, SellerBenefits $benefits, ?CommercialOffer $offer = null)
    {
        abort_if($offer && $offer->user_id !== $request->user()->id, 404);
        $data = $request->validate([
            'id_producto' => ['required', 'integer'], 'title' => ['required', 'string', 'max:100'], 'description' => ['nullable', 'string', 'max:1500'],
            'discount_percent' => ['nullable', 'integer', 'between:1,90'], 'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'], 'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'enabled' => ['required', 'boolean'],
        ]);
        DB::transaction(function () use ($request, $data, $benefits, $offer) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $subscription = $benefits->require($request->user());
            if ($offer && $offer->subscription_id !== $subscription->id) {
                throw ValidationException::withMessages(['oferta' => 'Esta promoción pertenece a un periodo anterior. Crea una nueva para tu plan actual.']);
            }
            $request->user()->tiendas()->whereHas('productos', fn ($q) => $q->whereKey($data['id_producto']))->firstOrFail();
            $start = filled($data['starts_at'] ?? null) ? Carbon::createFromFormat('Y-m-d\TH:i', $data['starts_at'], 'America/Mexico_City')->startOfMinute()->utc() : now();
            $end = filled($data['ends_at'] ?? null) ? Carbon::createFromFormat('Y-m-d\TH:i', $data['ends_at'], 'America/Mexico_City')->startOfMinute()->utc() : null;
            if ($start->isFuture() && $subscription->plan !== 'pro') {
                throw ValidationException::withMessages(['starts_at' => 'Programar una promoción requiere Pro. Con Impulso puedes publicarla ahora.']);
            }
            if ($end && ($end->lessThanOrEqualTo($start) || $end->isPast())) {
                throw ValidationException::withMessages(['ends_at' => 'El fin debe ser posterior al inicio y a la fecha actual.']);
            }
            $other = CommercialOffer::where('subscription_id', $subscription->id)->where('enabled', true)->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->when($offer, fn ($q) => $q->whereKeyNot($offer->id));
            if ($data['enabled'] && $benefits->reached($subscription, 'offer', (clone $other)->count())) {
                throw ValidationException::withMessages(['oferta' => 'Impulso permite hasta 3 promociones activas. Desactiva una para crear otra.']);
            }
            if ($data['enabled'] && (clone $other)->where('id_producto', $data['id_producto'])->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $start))->when($end, fn ($q) => $q->where('starts_at', '<', $end))->exists()) {
                throw ValidationException::withMessages(['id_producto' => 'Este producto ya tiene una promoción en esas fechas. Desactívala o elige otro periodo.']);
            }
            $fields = array_replace($data, ['starts_at' => $start, 'ends_at' => $end]);
            if ($offer) {
                $offer->update($fields);
            } else {
                $offer = CommercialOffer::create($fields + ['user_id' => $request->user()->id, 'subscription_id' => $subscription->id]);
            }
            app(\App\Services\StoreAudience::class)->offerPublished($offer);
        });

        return back()->with('success', 'Promoción comercial guardada. Es informativa y no modifica el precio ni las condiciones del apartado.');
    }

    public function disable(Request $request, CommercialOffer $offer)
    {
        abort_unless($offer->user_id === $request->user()->id, 404);
        $offer->update(['enabled' => false]);

        return back()->with('success', 'Promoción desactivada.');
    }
}
