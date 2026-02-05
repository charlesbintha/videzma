<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorsController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'doctor')->with('doctorProfile');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $doctors = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'status' => ['required', Rule::in(['pending', 'active', 'suspended'])],
            'locale' => ['required', 'string', 'max:10'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:50', 'unique:doctor_profiles,license_number'],
            'verification_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'bio' => ['nullable', 'string'],
        ]);

        $doctor = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
            'locale' => $validated['locale'],
            'password' => Hash::make($validated['password']),
            'role' => 'doctor',
        ]);

        $doctor->doctorProfile()->create([
            'specialty' => $validated['specialty'],
            'license_number' => $validated['license_number'],
            'verification_status' => $validated['verification_status'],
            'verified_at' => $validated['verification_status'] === 'approved' ? now() : null,
            'bio' => $validated['bio'],
        ]);

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('status', 'Medecin cree.');
    }

    public function show(User $doctor)
    {
        $this->ensureDoctor($doctor);

        $doctor->load(['doctorProfile', 'doctorDocuments.reviewer']);

        return view('admin.doctors.show', compact('doctor'));
    }

    public function edit(User $doctor)
    {
        $this->ensureDoctor($doctor);

        $doctor->load('doctorProfile');

        return view('admin.doctors.edit', compact('doctor'));
    }

    public function update(Request $request, User $doctor)
    {
        $this->ensureDoctor($doctor);

        $profileId = optional($doctor->doctorProfile)->id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($doctor->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'status' => ['required', Rule::in(['pending', 'active', 'suspended'])],
            'locale' => ['required', 'string', 'max:10'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:50', Rule::unique('doctor_profiles', 'license_number')->ignore($profileId)],
            'verification_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'bio' => ['nullable', 'string'],
        ]);

        $doctor->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
            'locale' => $validated['locale'],
        ]);

        $profileData = [
            'specialty' => $validated['specialty'],
            'license_number' => $validated['license_number'],
            'verification_status' => $validated['verification_status'],
            'bio' => $validated['bio'],
        ];

        if ($validated['verification_status'] === 'approved') {
            $profileData['verified_at'] = $doctor->doctorProfile?->verified_at ?? now();
        } else {
            $profileData['verified_at'] = null;
        }

        $doctor->doctorProfile()->updateOrCreate(
            ['user_id' => $doctor->id],
            $profileData
        );

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('status', 'Profil medecin mis a jour.');
    }

    public function destroy(User $doctor)
    {
        $this->ensureDoctor($doctor);

        $doctor->delete();

        return redirect()
            ->route('admin.doctors.index')
            ->with('status', 'Medecin supprime.');
    }

    private function ensureDoctor(User $doctor): void
    {
        if ($doctor->role !== 'doctor') {
            abort(404);
        }
    }
}
