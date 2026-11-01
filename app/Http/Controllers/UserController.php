<?php
 
namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
 
class UserController extends Controller
{
    /**
     * Tampilkan daftar user.
     */
    public function index()
    {
        $users = User::orderBy('name')->paginate(10);
        return view('users.index', compact('users'));
    }
 
    /**
     * Tampilkan form tambah user baru.
     */
    public function create()
    {
        return view('users.create');
    }
 
    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['pemilik', 'admin', 'kasir'])],
        ]);
 
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
 
        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }
 
    /**
     * Tampilkan form edit user.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }
 
    /**
     * Update data user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['pemilik', 'admin', 'kasir'])],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);
 
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];
 
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
 
        $user->update($data);
 
        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }
}
