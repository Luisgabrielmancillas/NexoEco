<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProfilePhotoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validateWithBag('profilePhoto', ['foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:min_width=64,min_height=64,max_width=6000,max_height=6000']], [
            'foto.required' => 'Selecciona una foto de perfil.',
            'foto.image' => 'Selecciona una imagen válida.',
            'foto.mimes' => 'Usa una foto JPG, PNG o WebP.',
            'foto.max' => 'La foto debe pesar como máximo 3 MB.',
            'foto.dimensions' => 'La imagen debe medir entre 64 y 6000 píxeles por lado.',
        ]);
        $user = $request->user();
        $previous = $user->profile_photo_path;
        $path = $request->file('foto')->store('perfiles/'.$user->id, 'local');
        if (! $path) {
            throw ValidationException::withMessages(['foto' => 'No pudimos guardar tu foto. Inténtalo de nuevo.'])->errorBag('profilePhoto');
        }
        try {
            $user->profile_photo_path = $path;
            $user->save();
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
        if ($previous) {
            Storage::disk('local')->delete($previous);
        }

        return back()->with('status', 'photo-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $previous = $request->user()->profile_photo_path;
        $request->user()->profile_photo_path = null;
        $request->user()->save();
        if ($previous) {
            Storage::disk('local')->delete($previous);
        }

        return back()->with('status', 'photo-removed');
    }

    public function show(User $user): StreamedResponse
    {
        abort_unless($user->profile_photo_path && Storage::disk('local')->exists($user->profile_photo_path), 404);

        return Storage::disk('local')->response($user->profile_photo_path, null, ['Cache-Control' => 'private, max-age=3600', 'X-Content-Type-Options' => 'nosniff']);
    }
}
