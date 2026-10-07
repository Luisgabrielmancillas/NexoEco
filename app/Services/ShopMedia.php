<?php

namespace App\Services;

use App\Models\Tienda;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopMedia
{
    public function upload(Tienda $store, UploadedFile $file): string
    {
        $name = Str::uuid().'.'.$file->extension();
        if (! $file->storeAs('tiendas/'.$store->getKey(), $name, 'local')) {
            throw new \RuntimeException('No se pudo guardar la imagen de la tienda.');
        }

        return '/media/tiendas/'.$store->getKey().'/'.$name;
    }

    public function remove(Tienda $store, ?string $url): void
    {
        $prefix = '/media/tiendas/'.$store->getKey().'/';
        if ($url && str_starts_with($url, $prefix)) {
            $name = substr($url, strlen($prefix));
            if (preg_match('/^[a-f0-9-]+\.(jpg|jpeg|png|webp)$/', $name)) {
                Storage::disk('local')->delete('tiendas/'.$store->getKey().'/'.$name);
            }
        }
    }
}
