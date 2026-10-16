<?php

namespace App\Http\Controllers\V1\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Ohne diese Prüfung hebelte der Token-Login die 2FA komplett aus:
        // Das Passwort allein reichte für einen vollwertigen API-Token.
        if ($user->hasTwoFactorEnabled()) {
            $code = (string) $request->input('code', '');

            if ($code === '' || ! (new Google2FA)->verifyKey($user->two_factor_secret, $code)) {
                throw ValidationException::withMessages([
                    'code' => ['A valid two-factor authentication code is required.'],
                ]);
            }
        }

        return response()->json([
            'type' => 'Bearer',
            'token' => $user->createToken($request->device_name)->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function check()
    {
        return Auth::check();
    }
}
