<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserRequest;
use App\Models\User;
use App\Services\AdministratorLimit;
use App\Support\AccountRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['rol' => ['nullable', 'in:comprador,vendedor,moderador,administrador'], 'q' => ['nullable', 'string', 'max:100'], 'estado' => ['nullable', 'in:activo,inactivo']]);
        $users = User::with(['tipos_usuario', 'solicitudVendedor'])
            ->when($data['rol'] ?? null, fn ($query, $role) => $query->whereHas('tipos_usuario', fn ($query) => $query->whereRaw('LOWER(nombre_tipo) = ?', [$role])))
            ->when($data['estado'] ?? null, fn ($query, $state) => $query->where('activo', $state === 'activo'))
            ->when($data['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')->orWhere('nombre_completo', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%')))
            ->latest('id')->paginate(15)->withQueryString();
        $recentUsers = User::with('tipos_usuario')
            ->when($data['rol'] ?? null, fn ($query, $role) => $query->whereHas('tipos_usuario', fn ($query) => $query->whereRaw('LOWER(nombre_tipo) = ?', [$role])))
            ->orderByDesc(DB::raw('COALESCE(fecha_registro, created_at)'))->orderByDesc('id')->limit(6)->get();
        $roleCounts = collect(AccountRoles::LABELS)->map(fn ($label, $role) => User::whereHas('tipos_usuario', fn ($query) => $query->whereRaw('LOWER(nombre_tipo) = ?', [$role]))->count());
        $totalUsers = User::count();

        return view('administrador.usuarios.index', compact('users', 'recentUsers', 'roleCounts', 'totalUsers'));
    }

    public function create()
    {
        $adminCount = app(AdministratorLimit::class)->count();

        return view('administrador.usuarios.create', compact('adminCount'));
    }

    public function store(AdminUserRequest $request)
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($data) {
            $roleId = $data['rol'] === 'administrador' ? app(AdministratorLimit::class)->reserveRole() : AccountRoles::id($data['rol']);
            $user = User::create(['name' => $data['name'], 'nombre_completo' => $data['name'], 'email' => $data['email'], 'password' => $data['password'], 'email_verified_at' => now(), 'fecha_registro' => now(), 'activo' => true]);
            $user->tipos_usuario()->attach($roleId);

            return $user;
        });
        request()->attributes->set('staff_activity', ['accion' => 'Creó una cuenta', 'descripcion' => 'Creó la cuenta de '.$data['rol'].' de '.$user->name.' (usuario #'.$user->id.').', 'tipo_objeto' => 'usuario', 'id_objeto' => $user->id]);

        return redirect()->route('admin.users.index', ['rol' => $data['rol']])->with('success', 'Cuenta de '.$data['rol'].' creada. El correo ya está verificado y puede iniciar sesión.');
    }

    public function edit(User $user)
    {
        $user->load(['tipos_usuario', 'solicitudVendedor']);

        return view('administrador.usuarios.edit', compact('user'));
    }

    public function show(User $user)
    {
        $user->load(['tipos_usuario', 'solicitudVendedor']);

        return view('administrador.usuarios.show', compact('user'));
    }

    public function update(AdminUserRequest $request, User $user)
    {
        $user->fill(['name' => $request->validated('name'), 'nombre_completo' => $request->validated('name'), 'email' => $request->validated('email')]);
        if ($user->isDirty('email') || ! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
        }
        $fields = [];
        if ($user->isDirty(['name', 'nombre_completo'])) {
            $fields[] = 'nombre';
        }
        if ($user->isDirty('email')) {
            $fields[] = 'correo';
        }
        if ($user->isDirty('email_verified_at')) {
            $fields[] = 'verificación del correo';
        }
        if ($fields) {
            $user->save();
            request()->attributes->set('staff_activity', ['accion' => 'Actualizó una cuenta', 'descripcion' => 'Actualizó '.implode(', ', $fields).' de '.$user->name.' (usuario #'.$user->id.').', 'tipo_objeto' => 'usuario', 'id_objeto' => $user->id]);
        } else {
            request()->attributes->set('staff_activity_skip', true);
        }

        return redirect()->route('admin.users.edit', $user)->with('success', 'Datos del usuario actualizados correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            $request->attributes->set('staff_activity_skip', true);

            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }
        $active = DB::transaction(function () use ($request, $user) {
            $accounts = User::whereIn('id', [$request->user()->id, $user->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            abort_unless($accounts[$request->user()->id]->activo, 403);
            $target = $accounts[$user->id];
            $target->update(['activo' => ! $target->activo]);

            return $target->activo;
        });
        $request->attributes->set('staff_activity', ['accion' => $active ? 'Reactivó una cuenta' : 'Desactivó una cuenta', 'descripcion' => ($active ? 'Reactivó' : 'Desactivó').' la cuenta de '.$user->name.' (usuario #'.$user->id.').', 'tipo_objeto' => 'usuario', 'id_objeto' => $user->id]);

        return back()->with('success', $active ? 'Usuario reactivado correctamente.' : 'Usuario desactivado. Ya no podrá iniciar sesión ni continuar con una sesión anterior.');
    }
}
