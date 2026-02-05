<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index()
    {
        $doctor = auth()->user();

        $availabilities = Availability::where('doctor_id', $doctor->id)
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->paginate(20);

        return view('doctor.availabilities.index', compact('availabilities'));
    }

    public function create()
    {
        return view('doctor.availabilities.create');
    }

    public function store(Request $request)
    {
        $doctor = auth()->user();

        $validated = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        Availability::create([
            'doctor_id' => $doctor->id,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'note' => $validated['note'] ?? null,
            'is_booked' => false,
        ]);

        return redirect()
            ->route('doctor.availabilities.index')
            ->with('status', 'Creneau cree.');
    }

    public function edit(Availability $availability)
    {
        $doctor = auth()->user();

        if ($availability->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($availability->is_booked) {
            return back()->withErrors(['availability' => 'Ce creneau est deja reserve.']);
        }

        return view('doctor.availabilities.edit', compact('availability'));
    }

    public function update(Request $request, Availability $availability)
    {
        $doctor = auth()->user();

        if ($availability->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($availability->is_booked) {
            return back()->withErrors(['availability' => 'Ce creneau est deja reserve.']);
        }

        $validated = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $availability->update($validated);

        return redirect()
            ->route('doctor.availabilities.index')
            ->with('status', 'Creneau modifie.');
    }

    public function destroy(Availability $availability)
    {
        $doctor = auth()->user();

        if ($availability->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($availability->is_booked) {
            return back()->withErrors(['availability' => 'Ce creneau est deja reserve.']);
        }

        $availability->delete();

        return redirect()
            ->route('doctor.availabilities.index')
            ->with('status', 'Creneau supprime.');
    }
}
