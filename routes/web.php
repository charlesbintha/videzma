<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriversController;
use App\Http\Controllers\Admin\DriverDocumentsController;
use App\Http\Controllers\Admin\InterventionsController;
use App\Http\Controllers\Admin\LocationsController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\ServiceRequestsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\SubscriptionPlansController;
use App\Http\Controllers\Admin\ClientSubscriptionsController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return view('welcome');
});

Route::view('layout-light', 'starter_kit.color_version.layout_light')->name('layout_light');
Route::view('layout-dark', 'starter_kit.color_version.layout_dark')->name('layout_dark');

// starter kit->page layout
Route::view('box-layout', 'starter_kit.page_layout.box_layout')->name('box_layout');
Route::view('rtl-layout', 'starter_kit.page_layout.rtl_layout')->name('rtl_layout');

// hide menu on scroll
Route::view('hide-menu-on-scroll', 'starter_kit.hide_menu_on_scroll')->name('hide_menu_on_scroll');

// footers
Route::view('footer-light', 'starter_kit.footers.footer_light')->name('footer_light');
Route::view('footer-dark', 'starter_kit.footers.footer_dark')->name('footer_dark');
Route::view('footer-fixed', 'starter_kit.footers.footer_fixed')->name('footer_fixed');

Route::get('login', [LoginController::class, 'show'])->name('login');
Route::post('login', [LoginController::class, 'authenticate'])->name('login.submit');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('users', [UsersController::class, 'index'])->name('users.index');

    // Drivers (Vidangeurs)
    Route::get('drivers', [DriversController::class, 'index'])->name('drivers.index');
    Route::get('drivers/create', [DriversController::class, 'create'])->name('drivers.create');
    Route::post('drivers', [DriversController::class, 'store'])->name('drivers.store');
    Route::get('drivers/{driver}', [DriversController::class, 'show'])->name('drivers.show');
    Route::get('drivers/{driver}/edit', [DriversController::class, 'edit'])->name('drivers.edit');
    Route::put('drivers/{driver}', [DriversController::class, 'update'])->name('drivers.update');
    Route::delete('drivers/{driver}', [DriversController::class, 'destroy'])->name('drivers.destroy');

    // Driver Documents
    Route::get('driver-documents', [DriverDocumentsController::class, 'index'])->name('driver-documents.index');
    Route::get('driver-documents/{document}', [DriverDocumentsController::class, 'show'])->name('driver-documents.show');
    Route::get('driver-documents/{document}/download', [DriverDocumentsController::class, 'download'])->name('driver-documents.download');
    Route::patch('driver-documents/{document}', [DriverDocumentsController::class, 'update'])->name('driver-documents.update');
    Route::delete('driver-documents/{document}', [DriverDocumentsController::class, 'destroy'])->name('driver-documents.destroy');

    // Service Requests (Demandes de service)
    Route::get('service-requests', [ServiceRequestsController::class, 'index'])->name('service-requests.index');
    Route::get('service-requests/{serviceRequest}', [ServiceRequestsController::class, 'show'])->name('service-requests.show');
    Route::patch('service-requests/{serviceRequest}', [ServiceRequestsController::class, 'update'])->name('service-requests.update');
    Route::post('service-requests/{serviceRequest}/assign', [ServiceRequestsController::class, 'assignDriver'])->name('service-requests.assign');

    // Interventions
    Route::get('interventions', [InterventionsController::class, 'index'])->name('interventions.index');
    Route::get('interventions/{intervention}', [InterventionsController::class, 'show'])->name('interventions.show');
    Route::patch('interventions/{intervention}', [InterventionsController::class, 'update'])->name('interventions.update');

    // Locations
    Route::get('locations', [LocationsController::class, 'index'])->name('locations.index');

    // Notifications
    Route::get('notifications', [NotificationsController::class, 'index'])->name('notifications.index');

    // Subscription Plans (Forfaits)
    Route::get('subscription-plans', [SubscriptionPlansController::class, 'index'])->name('subscription-plans.index');
    Route::get('subscription-plans/create', [SubscriptionPlansController::class, 'create'])->name('subscription-plans.create');
    Route::post('subscription-plans', [SubscriptionPlansController::class, 'store'])->name('subscription-plans.store');
    Route::get('subscription-plans/{subscriptionPlan}/edit', [SubscriptionPlansController::class, 'edit'])->name('subscription-plans.edit');
    Route::put('subscription-plans/{subscriptionPlan}', [SubscriptionPlansController::class, 'update'])->name('subscription-plans.update');
    Route::delete('subscription-plans/{subscriptionPlan}', [SubscriptionPlansController::class, 'destroy'])->name('subscription-plans.destroy');
    Route::post('subscription-plans/{subscriptionPlan}/toggle', [SubscriptionPlansController::class, 'toggle'])->name('subscription-plans.toggle');

    // Client Subscriptions (Abonnements clients)
    Route::get('client-subscriptions', [ClientSubscriptionsController::class, 'index'])->name('client-subscriptions.index');
    Route::get('client-subscriptions/create', [ClientSubscriptionsController::class, 'create'])->name('client-subscriptions.create');
    Route::post('client-subscriptions', [ClientSubscriptionsController::class, 'store'])->name('client-subscriptions.store');
    Route::get('client-subscriptions/{clientSubscription}', [ClientSubscriptionsController::class, 'show'])->name('client-subscriptions.show');
    Route::get('client-subscriptions/{clientSubscription}/edit', [ClientSubscriptionsController::class, 'edit'])->name('client-subscriptions.edit');
    Route::put('client-subscriptions/{clientSubscription}', [ClientSubscriptionsController::class, 'update'])->name('client-subscriptions.update');
    Route::delete('client-subscriptions/{clientSubscription}', [ClientSubscriptionsController::class, 'destroy'])->name('client-subscriptions.destroy');
    Route::post('client-subscriptions/{clientSubscription}/pause', [ClientSubscriptionsController::class, 'pause'])->name('client-subscriptions.pause');
    Route::post('client-subscriptions/{clientSubscription}/resume', [ClientSubscriptionsController::class, 'resume'])->name('client-subscriptions.resume');
    Route::post('client-subscriptions/{clientSubscription}/renew', [ClientSubscriptionsController::class, 'renew'])->name('client-subscriptions.renew');
    Route::post('client-subscriptions/{clientSubscription}/mark-paid', [ClientSubscriptionsController::class, 'markPaid'])->name('client-subscriptions.mark-paid');
});
