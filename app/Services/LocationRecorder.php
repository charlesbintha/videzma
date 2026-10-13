<?php

namespace App\Services;

use App\Events\DriverPositionUpdated;
use App\Models\Location;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LocationRecorder
{
    public function record(User $user, array $data, ?ServiceRequest $mission = null): Location
    {
        $time = isset($data['captured_at']) ? Carbon::parse($data['captured_at']) : now();
        if ($time->greaterThan(now()->addSeconds(30))) {
            throw ValidationException::withMessages(['captured_at' => 'La date de la position ne peut pas être dans le futur.']);
        }

        return DB::transaction(function () use ($user, $data, $time, $mission) {
            // Serialize incoming GPS packets for the same driver, including concurrent requests.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($mission) {
                $active = ServiceRequest::trackingActive()->whereKey($mission->id)
                    ->where('driver_id', $user->id)->lockForUpdate()->first();
                abort_unless($active, 409, 'La navigation est terminée ou a changé de vidangeur.');
                if ($time->lt($active->navigation_started_at)) {
                    throw ValidationException::withMessages(['captured_at' => 'Cette position précède le début du suivi.']);
                }
            }
            $latest = Location::where('user_id', $user->id)->newest()->first();
            if ($latest?->recordedAt() && $time->lessThan($latest->recordedAt())) {
                throw ValidationException::withMessages(['captured_at' => 'Une position plus récente a déjà été reçue.']);
            }
            $location = $user->locations()->create([
                'latitude' => $data['latitude'], 'longitude' => $data['longitude'],
                'address' => $data['address'] ?? null, 'captured_at' => $time,
            ]);
            if ($user->role === 'driver' && $location->isFresh()) {
                foreach (ServiceRequest::trackingActive()->where('driver_id', $user->id)
                    ->where('navigation_started_at', '<=', $time)->with('driver')->get() as $service) {
                    // No straight-line ETA presented as a road travel time.
                    DriverPositionUpdated::dispatch($service, $location);
                }
            }

            return $location;
        });
    }
}
