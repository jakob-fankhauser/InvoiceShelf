<?php

namespace App\Http\Controllers\V1\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();

        $user->two_factor_secret = $secret;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'InvoiceShelf'),
            $user->email,
            $secret
        );

        $qrCodeSvg = QrCode::format('svg')->size(200)->generate($qrCodeUrl);

        return response()->json([
            'secret' => $secret,
            'qr_code_svg' => $qrCodeSvg,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $google2fa = new Google2FA();

        if (! $user->two_factor_secret) {
            return response()->json(['message' => 'Two-factor authentication has not been enabled.'], 422);
        }

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return response()->json(['message' => 'The provided code is invalid.'], 422);
        }

        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10))->toArray();

        $user->two_factor_confirmed_at = now();
        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        return response()->json([
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->getAuthPassword())) {
            return response()->json(['message' => 'The provided password is incorrect.'], 422);
        }

        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return response()->json(['message' => 'Two-factor authentication has been disabled.']);
    }

    public function challenge(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $userId = $request->session()->get('login.id');

        if (! $userId) {
            return response()->json(['message' => 'No login attempt in progress.'], 422);
        }

        $user = User::findOrFail($userId);

        if ($request->code) {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

            if (! $valid) {
                return response()->json(['message' => 'The provided code is invalid.'], 422);
            }
        } elseif ($request->recovery_code) {
            $recoveryCodes = json_decode($user->two_factor_recovery_codes, true);

            if (! in_array($request->recovery_code, $recoveryCodes)) {
                return response()->json(['message' => 'The provided recovery code is invalid.'], 422);
            }

            $recoveryCodes = array_values(array_diff($recoveryCodes, [$request->recovery_code]));
            $user->two_factor_recovery_codes = json_encode($recoveryCodes);
            $user->save();
        } else {
            return response()->json(['message' => 'A code or recovery code is required.'], 422);
        }

        Auth::login($user, $request->session()->get('login.remember', false));

        $request->session()->forget('login.id');
        $request->session()->forget('login.remember');

        return response()->json(['success' => true]);
    }
}
