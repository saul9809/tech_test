<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Listar todos los usuarios (para asignar owners)
     */
    public function index()
    {
        // Solo admin y pm pueden ver la lista de usuarios
        Gate::authorize('view-users');

        $users = User::select('id', 'name', 'email', 'role')->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}
