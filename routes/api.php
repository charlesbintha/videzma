<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ServiceRequestsController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\DriversController;
use App\Http\Controllers\Api\Driver\ServiceRequestsController as DriverServiceRequestsController;
use App\Http\Controllers\Api\Driver\CommentsController as DriverCommentsController;
use App\Http\Controllers\Api\Driver\AvailabilityController as DriverAvailabilityController;
use App\Http\Controllers\Api\Driver\ProfileController as DriverProfileController;
use App\Http\Controllers\Api\Driver\NavigationController as DriverNavigationController;
use App\Http\Controllers\Api\TrackingController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::patch('auth/me', [AuthController::class, 'update']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Locations
    Route::post('locations', [LocationController::class, 'store']);
    Route::get('clients/{client}/locations', [LocationController::class, 'index']);
    Route::get('clients/{client}/locations/latest', [LocationController::class, 'latest']);

    // Service Requests (Client)
    Route::get('services', [ServiceRequestsController::class, 'index']);
    Route::post('services', [ServiceRequestsController::class, 'store']);
    Route::get('services/{serviceRequest}', [ServiceRequestsController::class, 'show']);
    Route::post('services/{serviceRequest}/cancel', [ServiceRequestsController::class, 'cancel']);
    Route::post('services/{serviceRequest}/rate', [ServiceRequestsController::class, 'rate']);

    // Notifications
    Route::get('notifications', [NotificationsController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationsController::class, 'unreadCount']);
    Route::post('notifications/{notification}/read', [NotificationsController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationsController::class, 'markAllAsRead']);

    // Route calculation
    Route::get('clients/{client}/route', [RouteController::class, 'fastest']);

    // Drivers listing and pricing
    Route::get('drivers/available', [DriversController::class, 'available']);
    Route::get('drivers/{driver}', [DriversController::class, 'show']);
    Route::post('drivers/estimate-price', [DriversController::class, 'estimatePrice']);

    // Track driver position (for clients) - optimized for polling
    Route::get('tracking/{serviceRequest}', [TrackingController::class, 'getDriverPosition']);
    Route::get('tracking/{serviceRequest}/status', [TrackingController::class, 'checkTrackingStatus']);

    // Legacy endpoint (kept for backwards compatibility)
    Route::get('services/{serviceRequest}/driver-position', [DriverNavigationController::class, 'getDriverPosition']);

    // Device tokens
    Route::post('device-tokens', [AuthController::class, 'registerDeviceToken']);
    Route::delete('device-tokens', [AuthController::class, 'removeDeviceToken']);
});

// Driver routes
Route::middleware(['auth:sanctum', 'driver'])->prefix('driver')->group(function () {
    // Profile
    Route::get('profile', [DriverProfileController::class, 'show']);
    Route::patch('profile', [DriverProfileController::class, 'update']);

    // Service Requests
    Route::get('services', [DriverServiceRequestsController::class, 'index']);
    Route::get('services/pending', [DriverServiceRequestsController::class, 'pending']);
    Route::get('services/{serviceRequest}', [DriverServiceRequestsController::class, 'show']);
    Route::post('services/{serviceRequest}/accept', [DriverServiceRequestsController::class, 'accept']);
    Route::post('services/{serviceRequest}/reject', [DriverServiceRequestsController::class, 'reject']);
    Route::post('services/{serviceRequest}/start', [DriverServiceRequestsController::class, 'start']);
    Route::post('services/{serviceRequest}/complete', [DriverServiceRequestsController::class, 'complete']);

    // Comments
    Route::get('services/{serviceRequest}/comments', [DriverCommentsController::class, 'index']);
    Route::post('services/{serviceRequest}/comments', [DriverCommentsController::class, 'store']);

    // Availabilities
    Route::get('availabilities', [DriverAvailabilityController::class, 'index']);
    Route::post('availabilities', [DriverAvailabilityController::class, 'store']);
    Route::patch('availabilities/{availability}', [DriverAvailabilityController::class, 'update']);
    Route::delete('availabilities/{availability}', [DriverAvailabilityController::class, 'destroy']);

    // Navigation
    Route::post('navigation/start', [DriverNavigationController::class, 'start']);
    Route::post('navigation/position', [DriverNavigationController::class, 'updatePosition']);
    Route::post('navigation/refresh', [DriverNavigationController::class, 'refreshRoute']);
    Route::post('navigation/stop', [DriverNavigationController::class, 'stop']);
});
