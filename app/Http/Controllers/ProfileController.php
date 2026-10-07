<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        $request->user()->nombre_completo = $request->validated('name');

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $fields = [];
        if ($request->user()->isDirty(['name', 'nombre_completo'])) {
            $fields[] = 'nombre';
        }
        if ($request->user()->isDirty('email')) {
            $fields[] = 'correo';
        }
        if ($fields) {
            request()->attributes->set('staff_activity', ['accion' => 'Actualizó sus datos personales', 'descripcion' => 'Actualizó su '.implode(' y ', $fields).'.', 'tipo_objeto' => 'usuario', 'id_objeto' => $request->user()->id]);
        } else {
            request()->attributes->set('staff_activity_skip', true);
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ], ['password.required' => 'Escribe tu contraseña para confirmar.', 'password.current_password' => 'La contraseña no es correcta.']);

        $user = $request->user();

        Auth::logout();

        $photo = $user->profile_photo_path;
        DB::transaction(function () use ($user) {
            $user->delete();
            $user->notifications()->delete();
        });
        if ($photo) {
            Storage::disk('local')->delete($photo);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
