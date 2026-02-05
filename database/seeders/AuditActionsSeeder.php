<?php

namespace Database\Seeders;

use App\Models\AuditAction;
use App\Models\ConsultationRequest;
use App\Models\DoctorDocument;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditActionsSeeder extends Seeder
{
    public function run(): void
    {
        if (AuditAction::count() > 0) {
            return;
        }

        $adminId = User::where('role', 'admin')->value('id');

        foreach (ConsultationRequest::take(10)->get() as $consultation) {
            AuditAction::create([
                'actor_id' => $adminId,
                'action' => 'status_change',
                'target_type' => ConsultationRequest::class,
                'target_id' => $consultation->id,
                'metadata' => ['status' => $consultation->status],
                'ip_address' => fake()->ipv4(),
            ]);
        }

        foreach (DoctorDocument::take(10)->get() as $document) {
            AuditAction::create([
                'actor_id' => $adminId,
                'action' => 'document_review',
                'target_type' => DoctorDocument::class,
                'target_id' => $document->id,
                'metadata' => ['status' => $document->status],
                'ip_address' => fake()->ipv4(),
            ]);
        }
    }
}
