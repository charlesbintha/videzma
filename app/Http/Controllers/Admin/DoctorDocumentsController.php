<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorDocument;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DoctorDocumentsController extends Controller
{
    public function index(Request $request)
    {
        $query = DoctorDocument::with(['doctor', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $documents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.doctor-documents.index', compact('documents'));
    }

    public function store(Request $request, User $doctor)
    {
        if ($doctor->role !== 'doctor') {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ]);

        $filename = Str::uuid()->toString().'.'.$request->file('document')->extension();
        $path = $request->file('document')->storeAs(
            'doctor-documents/'.$doctor->id,
            $filename,
            'public'
        );

        DoctorDocument::create([
            'doctor_id' => $doctor->id,
            'type' => $validated['type'],
            'file_path' => $path,
            'status' => 'pending',
            'notes' => $validated['notes'],
        ]);

        $this->syncDoctorVerification($doctor->id);

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('status', 'Document televerse.');
    }

    public function show(DoctorDocument $document)
    {
        $document->load(['doctor', 'reviewer']);

        return view('admin.doctor-documents.show', compact('document'));
    }

    public function download(DoctorDocument $document)
    {
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path);
    }

    public function update(Request $request, DoctorDocument $document)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'notes' => ['nullable', 'string'],
        ]);

        $document->status = $validated['status'];
        $document->notes = $validated['notes'];

        if ($validated['status'] === 'pending') {
            $document->reviewed_by = null;
            $document->reviewed_at = null;
        } else {
            $document->reviewed_by = auth()->id();
            $document->reviewed_at = now();
        }

        $document->save();

        $this->syncDoctorVerification($document->doctor_id);

        return redirect()
            ->route('admin.doctor-documents.show', $document)
            ->with('status', 'Document mis a jour.');
    }

    public function destroy(DoctorDocument $document)
    {
        $doctor = $document->doctor;

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        if ($doctor) {
            $this->syncDoctorVerification($doctor->id);
            return redirect()
                ->route('admin.doctors.show', $doctor)
                ->with('status', 'Document supprime.');
        }

        return redirect()
            ->route('admin.doctor-documents.index')
            ->with('status', 'Document supprime.');
    }

    private function syncDoctorVerification(int $doctorId): void
    {
        $documents = DoctorDocument::where('doctor_id', $doctorId)->get();

        if ($documents->isEmpty()) {
            return;
        }

        $hasRejected = $documents->contains(fn ($doc) => $doc->status === 'rejected');
        $allApproved = $documents->every(fn ($doc) => $doc->status === 'approved');

        $status = 'pending';
        $verifiedAt = null;

        if ($hasRejected) {
            $status = 'rejected';
        } elseif ($allApproved) {
            $status = 'approved';
            $verifiedAt = now();
        }

        DoctorProfile::updateOrCreate(
            ['user_id' => $doctorId],
            ['verification_status' => $status, 'verified_at' => $verifiedAt]
        );
    }
}
