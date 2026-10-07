<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyerNotificationController extends Controller
{
    public function count(Request $request): JsonResponse
    {
        return response()->json(['unread' => $request->user()->unreadNotifications()->count()])->header('Cache-Control', 'no-store, private');
    }

    public function index(Request $request): View
    {
        return view('comprador.notificaciones', ['notificaciones' => $request->user()->notifications()->latest()->paginate(15)]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back()->with('status', 'Notificación marcada como leída.');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Todas tus notificaciones están marcadas como leídas.');
    }
}
