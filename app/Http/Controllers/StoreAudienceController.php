<?php

namespace App\Http\Controllers;

use App\Models\StoreUpdate;
use App\Models\Tienda;
use App\Services\SellerBenefits;
use App\Services\StoreAudience;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreAudienceController extends Controller
{
    public function followed(): RedirectResponse
    {
        return redirect()->route('comprador.favoritos', 'tiendas');
    }

    public function follow(Request $request, Tienda $tienda)
    {
        $request->validate(['following' => ['required', 'boolean']]);
        abort_if($tienda->id_vendedor === $request->user()->id, 403);
        if ($request->boolean('following')) {
            abort_unless($tienda->user?->activo && $tienda->user->tieneTipo('vendedor'), 404);
            DB::table('store_follows')->insertOrIgnore(['user_id' => $request->user()->id, 'id_tienda' => $tienda->getKey(), 'created_at' => now(), 'updated_at' => now()]);
        } else {
            $request->user()->followedStores()->detach($tienda->getKey());
        }

        return back()->with('success', $request->boolean('following') ? 'Ahora sigues esta tienda. Recibirás sus novedades en tus notificaciones de NexoEco.' : 'Dejaste de seguir esta tienda.');
    }

    public function index(Request $request, SellerBenefits $benefits)
    {
        $subscription = $benefits->subscription($request->user());
        $tienda = $request->user()->tiendas()->orderBy('id_tienda')->first();
        $updates = StoreUpdate::where('user_id', $request->user()->id)->latest()->paginate(12);
        $followers = $tienda?->followers()->count() ?? 0;

        return view('vendedor.novedades', compact('subscription', 'tienda', 'updates', 'followers'));
    }

    public function save(Request $request, SellerBenefits $benefits, StoreAudience $audience)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:120'], 'body' => ['required', 'string', 'max:2000'], 'publish_at' => ['nullable', 'date_format:Y-m-d\TH:i']]);
        $subscription = $benefits->require($request->user());
        $store = $request->user()->tiendas()->orderBy('id_tienda')->firstOrFail();
        $publishAt = filled($data['publish_at'] ?? null) ? Carbon::createFromFormat('Y-m-d\TH:i', $data['publish_at'], 'America/Mexico_City')->startOfMinute()->utc() : now();
        if ($publishAt->isFuture() && $subscription->plan !== 'pro') {
            throw ValidationException::withMessages(['publish_at' => 'Programar novedades requiere Pro. Con Impulso puedes publicarlas ahora.']);
        }
        DB::transaction(function () use ($request, $data, $subscription, $store, $publishAt, $audience) {
            $update = StoreUpdate::create(['user_id' => $request->user()->id, 'subscription_id' => $subscription->id, 'id_tienda' => $store->getKey(), 'title' => $data['title'], 'body' => $data['body'], 'publish_at' => $publishAt]);
            $audience->publish($update);
        });

        return back()->with('success', $publishAt->isFuture() ? 'Novedad programada. Se publicará si tu suscripción sigue activa en esa fecha.' : 'Novedad publicada para quienes siguen tu tienda.');
    }

    public function destroy(Request $request, StoreUpdate $update)
    {
        abort_unless($update->user_id === $request->user()->id, 404);
        DB::transaction(function () use ($update) {
            $update = StoreUpdate::whereKey($update->id)->lockForUpdate()->firstOrFail();
            // Remove unread notices too; links must not advertise a withdrawn post.
            DB::table('notifications')->where('data->event', 'store.update')->where('data->update_id', $update->id)->delete();
            $update->delete();
        });

        return back()->with('success', 'Novedad retirada.');
    }
}
