<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // kirim juga password_confirmation
            'university' => ['nullable', 'string', 'max:100'],
            'program' => ['nullable', 'string', 'max:100'],
            'semester' => ['nullable', 'integer', 'between:1,14'],
            'entry_year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $user = User::create($data); // password di-hash otomatis oleh cast 'hashed'
        $token = $user->createToken('aziwa-app')->plainTextToken;

        return $this->ok(['user' => new UserResource($user), 'token' => $token], 'Registrasi berhasil', 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // Pesan sengaja sama untuk email salah maupun sandi salah
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->fail('Email atau kata sandi salah', 401);
        }

        $token = $user->createToken('aziwa-app')->plainTextToken;

        return $this->ok(['user' => new UserResource($user), 'token' => $token], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok(null, 'Logout berhasil');
    }

    public function me(Request $request)
    {
        return $this->ok(new UserResource($request->user()));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'university' => ['nullable', 'string', 'max:100'],
            'program' => ['nullable', 'string', 'max:100'],
            'semester' => ['nullable', 'integer', 'between:1,14'],
            'entry_year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $user->update($data);

        return $this->ok(new UserResource($user->fresh()), 'Profil diperbarui');
    }
}