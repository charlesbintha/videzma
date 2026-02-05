<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $doctor = auth()->user();
        $doctor->load('doctorProfile');

        return view('doctor.profile.show', compact('doctor'));
    }

    public function edit()
    {
        $doctor = auth()->user();
        $doctor->load('doctorProfile');

        return view('doctor.profile.edit', compact('doctor'));
    }

    public function update(Request $request)
    {
        $doctor = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $doctor->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        $doctor->doctorProfile()->updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'specialty' => $validated['specialty'],
                'bio' => $validated['bio'],
            ]
        );

        return redirect()
            ->route('doctor.profile.show')
            ->with('status', 'Profil mis a jour.');
    }
}
