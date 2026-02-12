<?php

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Tracking Channels
|--------------------------------------------------------------------------
|
| These channels are used for real-time driver tracking.
|
*/

// Channel for tracking a specific service request
// Both the client and the assigned driver can subscribe
Broadcast::channel('tracking.service.{serviceRequestId}', function ($user, $serviceRequestId) {
    $serviceRequest = ServiceRequest::find($serviceRequestId);

    if (!$serviceRequest) {
        return false;
    }

    // Client can track their own service request
    if ($user->id === $serviceRequest->client_id) {
        return true;
    }

    // Driver can track service requests assigned to them
    if ($user->id === $serviceRequest->driver_id) {
        return true;
    }

    // Admins can track any service request
    if ($user->role === 'admin') {
        return true;
    }

    return false;
});

// Channel for client notifications
Broadcast::channel('client.{clientId}', function ($user, $clientId) {
    return (int) $user->id === (int) $clientId;
});

// Channel for driver notifications
Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    return (int) $user->id === (int) $driverId;
});
