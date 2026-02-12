<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $serviceRequestId;
    public int $clientId;
    public ?int $driverId;
    public string $status;
    public string $statusLabel;
    public ?string $driverName;
    public ?string $driverPhone;

    /**
     * Create a new event instance.
     */
    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequestId = $serviceRequest->id;
        $this->clientId = $serviceRequest->client_id;
        $this->driverId = $serviceRequest->driver_id;
        $this->status = $serviceRequest->status;
        $this->statusLabel = $serviceRequest->status_label ?? $serviceRequest->status;

        $driver = $serviceRequest->driver;
        $this->driverName = $driver?->name;
        $this->driverPhone = $driver?->phone;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            // Channel for the specific service request
            new PrivateChannel('tracking.service.' . $this->serviceRequestId),
            // Channel for the client to receive all their updates
            new PrivateChannel('client.' . $this->clientId),
        ];

        // Also notify the driver if assigned
        if ($this->driverId) {
            $channels[] = new PrivateChannel('driver.' . $this->driverId);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'status.changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'service_request_id' => $this->serviceRequestId,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'driver_id' => $this->driverId,
            'driver' => $this->driverName ? [
                'name' => $this->driverName,
                'phone' => $this->driverPhone,
            ] : null,
        ];
    }
}
