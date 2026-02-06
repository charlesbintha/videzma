<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverDocumentsController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverDocument::with('driver');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $documents = $query->latest()->paginate(15)->withQueryString();

        return view('admin.driver-documents.index', compact('documents'));
    }

    public function show($id)
    {
        $document = DriverDocument::with('driver')->findOrFail($id);
        return view('admin.driver-documents.show', compact('document'));
    }

    public function update(Request $request, $id)
    {
        $document = DriverDocument::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $document->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Si le document est approuvé, vérifier si tous les documents du vidangeur sont approuvés
        if ($validated['status'] === 'approved') {
            $this->checkAndUpdateDriverVerification($document->driver_id);
        }

        return redirect()->route('admin.driver-documents.show', $document->id)
            ->with('success', 'Document mis a jour avec succes.');
    }

    public function download($id)
    {
        $document = DriverDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Fichier non trouve.');
        }

        return Storage::disk('public')->download($document->file_path);
    }

    public function destroy($id)
    {
        $document = DriverDocument::findOrFail($id);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('admin.driver-documents.index')
            ->with('success', 'Document supprime avec succes.');
    }

    /**
     * Vérifie si tous les documents du vidangeur sont approuvés
     * et met à jour le statut de vérification du profil
     */
    private function checkAndUpdateDriverVerification($driverId)
    {
        $pendingOrRejected = DriverDocument::where('driver_id', $driverId)
            ->whereIn('status', ['pending', 'rejected'])
            ->exists();

        if (!$pendingOrRejected) {
            // Tous les documents sont approuvés
            $profile = \App\Models\DriverProfile::where('user_id', $driverId)->first();
            if ($profile && $profile->verification_status !== 'approved') {
                $profile->update([
                    'verification_status' => 'approved',
                    'verified_at' => now(),
                ]);
            }
        }
    }
}
