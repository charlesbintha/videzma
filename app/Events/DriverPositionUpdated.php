<?php

namespace App\Events;

use App\Models\Location;
use App\Models\ServiceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverPositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public int $serviceRequestId;

    public int $driverId;

    public int $locationId;

    public float $latitude;

    public float $longitude;

    public int $timestamp;

    public ?int $distanceMeters;

    public ?int $etaMinutes;

    public string $driverName;

    public ?string $driverPhone;

    /**
     * Create a new event instance.
     */
    public function __construct(
        ServiceRequest $serviceRequest,
        Location $location,
        ?int $distanceMeters = null,
        ?int $etaMinutes = null
    ) {
        $this->serviceRequestId = $serviceRequest->id;
        $this->driverId = $serviceRequest->driver_id;
        $this->locationId = $location->id;
        $this->latitude = (float) $location->latitude;
        $this->longitude = (float) $location->longitude;
        $this->timestamp = $location->recordedAt()?->timestamp ?? now()->timestamp;
        $this->distanceMeters = $distanceMeters;
        $this->etaMinutes = $etaMinutes;

        $driver = $serviceRequest->driver;
        $this->driverName = $driver?->name ?? 'Vidangeur';
        $this->driverPhone = $driver?->phone;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Recheck when the queued broadcast actually runs, after a possible stop or reassignment.
        if (! ServiceRequest::trackingActive()->whereKey($this->serviceRequestId)
            ->where('driver_id', $this->driverId)
            ->where('navigation_started_at', '<=', date('Y-m-d H:i:s', $this->timestamp))->exists()
            || $this->timestamp < now()->subMinutes(2)->timestamp
            || Location::where('user_id', $this->driverId)->newest()->value('id') !== $this->locationId) {
            return [];
        }

        return [
            new PrivateChannel('tracking.service.'.$this->serviceRequestId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'position.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'driver_id' => $this->driverId,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'timestamp' => $this->timestamp,
            'distance' => $this->distanceMeters,
            'eta' => $this->etaMinutes,
            'is_fresh' => true,
            'eta_source' => null,
            'driver' => [
                'name' => $this->driverName,
                'phone' => $this->driverPhone,
            ],
        ];
    }
}
