<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Buat User baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer', // Default role
        ]);

        // Buat Token untuk user tersebut
        $token = $user->createToken('auth_token')->plainTextToken;

        // Kirim respon JSON ke Vue
        return response()->json([
            'message' => 'Register berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    // 2. LOGIN
    public function login(Request $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Login gagal, cek email/password',
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    // 3. LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = random_int(100000, 999999);

        PasswordOtp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp_hash' => Hash::make($otp),
                'expired_at' => now()->addMinutes(5),
            ]
        );

        Mail::to($request->email)
            ->send(new ResetPasswordOtpMail($otp));

        return response()->json([
            'message' => 'OTP berhasil dikirim ke email',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $record = PasswordOtp::where('email', $request->email)->first();

        if (! $record || now()->gt($record->expired_at)) {
            return response()->json(['message' => 'OTP kadaluarsa'], 422);
        }

        if (! Hash::check($request->otp, $record->otp_hash)) {
            return response()->json(['message' => 'OTP salah'], 422);
        }

        return response()->json([
            'message' => 'OTP valid',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $user->password = Hash::make($request->password);
        $user->save();

        PasswordOtp::where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password berhasil direset',
        ]);
    }
}
