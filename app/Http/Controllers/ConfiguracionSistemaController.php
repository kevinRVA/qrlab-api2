<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfiguracionSistemaController extends Controller
{
    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================
    public function index()
    {
        return view('admin.configuracion');
    }

    // =========================================================================
    // API: COORDINADORES
    // =========================================================================

    public function getCoordinators()
    {
        // Obtener usuarios con rol 'coordinador' y sus laboratorios asignados
        $coordinators = \App\Models\User::with('coordinatorLabs')
            ->where('role', \App\Models\User::ROLE_COORDINATOR)
            ->orderBy('name')
            ->get();

        return response()->json($coordinators);
    }

    public function storeCoordinator(\App\Http\Requests\StoreCoordinatorRequest $request)
    {
        $validated = $request->validated();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => \App\Models\User::ROLE_COORDINATOR,
        ];

        if (!empty($validated['user_code'])) {
            $userData['user_code'] = $validated['user_code'];
        }

        $user = \App\Models\User::create($userData);

        return response()->json(['ok' => true, 'message' => 'Coordinador creado con éxito.', 'user' => $user]);
    }

    public function updateCoordinator(\App\Http\Requests\UpdateCoordinatorRequest $request, $id)
    {
        $user = \App\Models\User::where('role', \App\Models\User::ROLE_COORDINATOR)->findOrFail($id);

        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_code' => $validated['user_code'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->update($data);

        return response()->json(['ok' => true, 'message' => 'Coordinador actualizado con éxito.']);
    }

    public function destroyCoordinator($id)
    {
        $user = \App\Models\User::where('role', \App\Models\User::ROLE_COORDINATOR)->findOrFail($id);
        // Al borrar el usuario, sus relaciones en coordinator_labs se borran si hay onDelete('cascade')
        // Por seguridad, hacemos detach primero:
        $user->coordinatorLabs()->detach();
        $user->delete();

        return response()->json(['ok' => true, 'message' => 'Coordinador eliminado.']);
    }

    // =========================================================================
    // API: LABORATORIOS
    // =========================================================================

    public function getLabs()
    {
        $labs = \App\Models\Laboratory::orderBy('name')->get();
        return response()->json($labs);
    }

    public function storeLab(\App\Http\Requests\StoreLabRequest $request)
    {
        $validated = $request->validated();

        $lab = \App\Models\Laboratory::create([
            'name' => $validated['name'],
        ]);

        return response()->json(['ok' => true, 'message' => 'Laboratorio creado con éxito.', 'lab' => $lab]);
    }

    public function updateLab(\App\Http\Requests\UpdateLabRequest $request, $id)
    {
        $lab = \App\Models\Laboratory::findOrFail($id);

        $validated = $request->validated();

        $lab->update(['name' => $validated['name']]);

        return response()->json(['ok' => true, 'message' => 'Laboratorio actualizado con éxito.']);
    }

    public function destroyLab($id)
    {
        $lab = \App\Models\Laboratory::findOrFail($id);

        // Quitar asignaciones a coordinadores
        \Illuminate\Support\Facades\DB::table('coordinator_labs')->where('laboratory_id', $lab->id)->delete();

        $lab->delete();

        return response()->json(['ok' => true, 'message' => 'Laboratorio eliminado.']);
    }

    // =========================================================================
    // API: ASIGNACIÓN DE LABORATORIOS A COORDINADOR
    // =========================================================================

    public function assignLabs(Request $request, $id)
    {
        $user = \App\Models\User::where('role', \App\Models\User::ROLE_COORDINATOR)->findOrFail($id);

        $validated = $request->validate([
            'labs' => 'array',
            'labs.*' => 'exists:laboratories,id',
        ]);

        $labs = $validated['labs'] ?? [];
        $user->coordinatorLabs()->sync($labs);

        return response()->json(['ok' => true, 'message' => 'Laboratorios asignados con éxito.']);
    }
}