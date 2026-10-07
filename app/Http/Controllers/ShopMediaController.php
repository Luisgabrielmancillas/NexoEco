<?php

namespace App\Http\Controllers;

use App\Models\Tienda;
use Illuminate\Support\Facades\Storage;

class ShopMediaController extends Controller
{
    public function show(Tienda $tienda, string $archivo)
    {
        abort_unless(preg_match('/^[a-f0-9-]+\.(jpg|jpeg|png|webp)$/', $archivo), 404);
        $url = '/media/tiendas/'.$tienda->getKey().'/'.$archivo;
        $user = request()->user();
        $canReview = $user && $user->hasVerifiedEmail() && ($user->id === $tienda->id_vendedor || $user->tieneTipo('moderador') || $user->tieneTipo('administrador'));
        $branding = in_array($url, [$tienda->logo_tienda, $tienda->portada_tienda], true);
        $used = $branding
            || $tienda->productos()->when($canReview, fn ($q) => $q->withTrashed())->where(function ($query) use ($url) {
                $query->where('imagen_url', $url)->orWhereHas('imagenes', fn ($q) => $q->where('imagen_url', $url));
            })->exists();
        abort_unless($used, 404);
        $disk = Storage::disk('local');
        $path = 'tiendas/'.$tienda->getKey().'/'.$archivo;
        abort_unless($disk->exists($path), 404);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($disk->path($path));
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 415);

        $response = response()->file($disk->path($path), ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => $branding ? 'public, max-age=86400' : 'private, no-store']);
        if (! $branding) {
            $response->setPrivate();
        }

        return $response;
    }
}
