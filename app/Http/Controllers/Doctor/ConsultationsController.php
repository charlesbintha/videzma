<?php

namespace App\Http\Controllers\Doctor;

use App\Events\ConsultationAccepted;
use App\Events\ConsultationRejected;
use App\Http\Controllers\Controller;
use App\Models\ConsultationComment;
use App\Models\ConsultationRequest;
use Illuminate\Http\Request;

class ConsultationsController extends Controller
{
    public function index(Request $request)
    {
        $doctor = auth()->user();

        $query = ConsultationRequest::where('doctor_id', $doctor->id)
            ->with(['patient:id,name,phone', 'location:id,address']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $consultations = $query->orderByDesc('requested_at')->paginate(20)->withQueryString();

        return view('doctor.consultations.index', compact('consultations'));
    }

    public function pending()
    {
        $doctor = auth()->user();

        $consultations = ConsultationRequest::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'assigned'])
            ->with(['patient:id,name,phone', 'location:id,address'])
            ->orderBy('sla_due_at')
            ->paginate(20);

        return view('doctor.consultations.pending', compact('consultations'));
    }

    public function show(ConsultationRequest $consultation)
    {
        $doctor = auth()->user();

        if ($consultation->doctor_id !== $doctor->id) {
            abort(403);
        }

        $consultation->load([
            'patient',
            'location',
            'appointment',
            'comments' => fn ($q) => $q->with('author:id,name,role')->orderBy('created_at'),
        ]);

        return view('doctor.consultations.show', compact('consultation'));
    }

    public function accept(ConsultationRequest $consultation)
    {
        $doctor = auth()->user();

        if ($consultation->doctor_id !== $doctor->id) {
            abort(403);
        }

        if (!in_array($consultation->status, ['pending', 'assigned'])) {
            return back()->withErrors(['status' => 'Impossible d\'accepter cette demande.']);
        }

        $consultation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        event(new ConsultationAccepted($consultation));

        return redirect()
            ->route('doctor.consultations.show', $consultation)
            ->with('status', 'Demande acceptee.');
    }

    public function reject(Request $request, ConsultationRequest $consultation)
    {
        $doctor = auth()->user();

        if ($consultation->doctor_id !== $doctor->id) {
            abort(403);
        }

        if (!in_array($consultation->status, ['pending', 'assigned'])) {
            return back()->withErrors(['status' => 'Impossible de rejeter cette demande.']);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $consultation->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'notes' => $validated['reason'] ?? $consultation->notes,
        ]);

        event(new ConsultationRejected($consultation, $validated['reason'] ?? null));

        return redirect()
            ->route('doctor.consultations.index')
            ->with('status', 'Demande rejetee.');
    }

    public function close(Request $request, ConsultationRequest $consultation)
    {
        $doctor = auth()->user();

        if ($consultation->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($consultation->status !== 'accepted') {
            return back()->withErrors(['status' => 'Seule une consultation acceptee peut etre cloturee.']);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $consultation->update([
            'status' => 'closed',
            'closed_at' => now(),
            'notes' => $validated['notes'] ?? $consultation->notes,
        ]);

        return redirect()
            ->route('doctor.consultations.show', $consultation)
            ->with('status', 'Consultation cloturee.');
    }

    public function storeComment(Request $request, ConsultationRequest $consultation)
    {
        $doctor = auth()->user();

        if ($consultation->doctor_id !== $doctor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'is_internal' => ['sometimes', 'boolean'],
        ]);

        ConsultationComment::create([
            'consultation_request_id' => $consultation->id,
            'author_id' => $doctor->id,
            'content' => $validated['content'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        return back()->with('status', 'Commentaire ajoute.');
    }
}
