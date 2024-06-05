<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Marchand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Enregistrer un nouvel utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:agent,administrateur',
            'marchand_id' => 'required_if:role,agent|exists:marchands,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'marchand_id' => $request->marchand_id,
        ]);

        return response()->json(['message' => 'Utilisateur enregistré avec succès'], 201);
    }

    /**
     * Authentifier un utilisateur et générer un jeton d'accès.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $marchand = Marchand::where('id', $user->marchand_id)->first();

            if (!$marchand) {
                return response()->json(['message' => 'Aucun marchand associé à cet utilisateur'], 401);
            }

            $token = $user->createToken('accessToken')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'marchand' => $marchand,
                'agent' => [
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
        } else {
            return response()->json(['message' => 'Email ou mot de passe incorrect'], 401);
        }
    }


    /**
     * Afficher la liste de tous les utilisateurs.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }
}
