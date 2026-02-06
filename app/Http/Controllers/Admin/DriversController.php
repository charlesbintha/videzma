<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Http\Request;

class DriversController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'driver')->with('driverProfile');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->whereHas('driverProfile', fn($q) => $q->where('verification_status', $status));
        }

        $drivers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.drivers.index', compact('drivers'));
    }

    public function show($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->load(['driverProfile', 'driverDocuments']);
        return view('admin.drivers.show', compact('driver'));
    }

    public function create()
    {
        return view('admin.drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'role' => 'driver',
            'status' => 'active',
            'locale' => 'fr',
        ]);

        // Créer un profil vidangeur vide
        DriverProfile::create([
            'user_id' => $user->id,
            'verification_status' => 'pending',
        ]);

        return redirect()->route('admin.drivers.show', $user->id)
            ->with('success', 'Vidangeur cree avec succes.');
    }

    public function edit($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->load('driverProfile');
        return view('admin.drivers.edit', compact('driver'));
    }

    public function update(Request $request, $id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $driver->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
            'verification_status' => 'nullable|in:pending,approved,rejected',
        ]);

        // Mettre à jour l'utilisateur
        $driver->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
        ]);

        // Mettre à jour le statut de vérification du profil si fourni
        if (isset($validated['verification_status']) && $driver->driverProfile) {
            $driver->driverProfile->update([
                'verification_status' => $validated['verification_status'],
                'verified_at' => $validated['verification_status'] === 'approved' ? now() : null,
            ]);
        }

        return redirect()->route('admin.drivers.show', $driver->id)
            ->with('success', 'Vidangeur mis a jour avec succes.');
    }

    public function destroy($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Vidangeur supprime.');
    }
}
