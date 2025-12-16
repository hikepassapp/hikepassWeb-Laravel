<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // 1. Ambil Data User (Customer)
    public function getCustomers()
    {
        // Ambil semua user yang rolenya 'customer'
        $users = User::where('role', 'customer')->orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    // 2. Ambil Data Admin
    public function getAdmins()
    {
        // Ambil semua user yang rolenya 'admin'
        $admins = User::where('role', 'admin')->orderBy('created_at', 'desc')->get();
        return response()->json($admins);
    }

    // 3. Tambah User/Admin Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,customer', // Pastikan role valid
            'posisi' => 'nullable|string' // Opsional untuk admin
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'posisi' => $request->posisi
        ]);

        return response()->json(['message' => 'Data berhasil ditambahkan', 'data' => $user], 201);
    }

    // 4. Hapus User/Admin
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user->delete();
        return response()->json(['message' => 'Berhasil dihapus']);
    }
}