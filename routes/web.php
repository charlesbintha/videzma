<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\AppointmentsController;
use App\Http\Controllers\Admin\ConsultationsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorDocumentsController;
use App\Http\Controllers\Admin\DoctorsController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\SlaController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\LocationsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\ConsultationsController as DoctorConsultationsController;
use App\Http\Controllers\Doctor\AppointmentsController as DoctorAppointmentsController;
use App\Http\Controllers\Doctor\AvailabilityController as DoctorAvailabilityController;
use App\Http\Controllers\Doctor\ProfileController as DoctorProfileController;

Route::get('/', function () {
    return redirect()->route('login');
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
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('users', [UsersController::class, 'index'])->name('users.index');
    Route::get('doctors/create', [DoctorsController::class, 'create'])->name('doctors.create');
    Route::post('doctors', [DoctorsController::class, 'store'])->name('doctors.store');
    Route::get('doctors', [DoctorsController::class, 'index'])->name('doctors.index');
    Route::get('doctors/{doctor}', [DoctorsController::class, 'show'])->name('doctors.show');
    Route::get('doctors/{doctor}/edit', [DoctorsController::class, 'edit'])->name('doctors.edit');
    Route::put('doctors/{doctor}', [DoctorsController::class, 'update'])->name('doctors.update');
    Route::delete('doctors/{doctor}', [DoctorsController::class, 'destroy'])->name('doctors.destroy');
    Route::post('doctors/{doctor}/documents', [DoctorDocumentsController::class, 'store'])->name('doctors.documents.store');
    Route::get('doctor-documents', [DoctorDocumentsController::class, 'index'])->name('doctor-documents.index');
    Route::get('doctor-documents/{document}/download', [DoctorDocumentsController::class, 'download'])->name('doctor-documents.download');
    Route::get('doctor-documents/{document}', [DoctorDocumentsController::class, 'show'])->name('doctor-documents.show');
    Route::patch('doctor-documents/{document}', [DoctorDocumentsController::class, 'update'])->name('doctor-documents.update');
    Route::delete('doctor-documents/{document}', [DoctorDocumentsController::class, 'destroy'])->name('doctor-documents.destroy');
    Route::get('consultations', [ConsultationsController::class, 'index'])->name('consultations.index');
    Route::get('consultations/{consultation}', [ConsultationsController::class, 'show'])->name('consultations.show');
    Route::patch('consultations/{consultation}', [ConsultationsController::class, 'update'])->name('consultations.update');
    Route::post('consultations/{consultation}/appointment', [ConsultationsController::class, 'storeAppointment'])->name('consultations.appointments.store');
    Route::get('appointments', [AppointmentsController::class, 'index'])->name('appointments.index');
    Route::get('appointments/{appointment}', [AppointmentsController::class, 'show'])->name('appointments.show');
    Route::patch('appointments/{appointment}', [AppointmentsController::class, 'update'])->name('appointments.update');
    Route::get('sla', [SlaController::class, 'index'])->name('sla.index');
    Route::get('locations', [LocationsController::class, 'index'])->name('locations.index');
    Route::get('notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
});

// Doctor routes
Route::prefix('doctor')->name('doctor.')->middleware('doctor.web')->group(function () {
    Route::get('/', [DoctorDashboardController::class, 'index'])->name('dashboard');

    // Consultations
    Route::get('consultations', [DoctorConsultationsController::class, 'index'])->name('consultations.index');
    Route::get('consultations/pending', [DoctorConsultationsController::class, 'pending'])->name('consultations.pending');
    Route::get('consultations/{consultation}', [DoctorConsultationsController::class, 'show'])->name('consultations.show');
    Route::post('consultations/{consultation}/accept', [DoctorConsultationsController::class, 'accept'])->name('consultations.accept');
    Route::post('consultations/{consultation}/reject', [DoctorConsultationsController::class, 'reject'])->name('consultations.reject');
    Route::post('consultations/{consultation}/close', [DoctorConsultationsController::class, 'close'])->name('consultations.close');
    Route::post('consultations/{consultation}/comments', [DoctorConsultationsController::class, 'storeComment'])->name('consultations.comments.store');

    // Appointments
    Route::get('appointments', [DoctorAppointmentsController::class, 'index'])->name('appointments.index');
    Route::get('appointments/create', [DoctorAppointmentsController::class, 'create'])->name('appointments.create');
    Route::post('appointments', [DoctorAppointmentsController::class, 'store'])->name('appointments.store');
    Route::get('appointments/{appointment}', [DoctorAppointmentsController::class, 'show'])->name('appointments.show');
    Route::patch('appointments/{appointment}/reschedule', [DoctorAppointmentsController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('appointments/{appointment}/cancel', [DoctorAppointmentsController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{appointment}/start', [DoctorAppointmentsController::class, 'start'])->name('appointments.start');
    Route::post('appointments/{appointment}/complete', [DoctorAppointmentsController::class, 'complete'])->name('appointments.complete');

    // Availabilities
    Route::get('availabilities', [DoctorAvailabilityController::class, 'index'])->name('availabilities.index');
    Route::get('availabilities/create', [DoctorAvailabilityController::class, 'create'])->name('availabilities.create');
    Route::post('availabilities', [DoctorAvailabilityController::class, 'store'])->name('availabilities.store');
    Route::get('availabilities/{availability}/edit', [DoctorAvailabilityController::class, 'edit'])->name('availabilities.edit');
    Route::patch('availabilities/{availability}', [DoctorAvailabilityController::class, 'update'])->name('availabilities.update');
    Route::delete('availabilities/{availability}', [DoctorAvailabilityController::class, 'destroy'])->name('availabilities.destroy');

    // Profile
    Route::get('profile', [DoctorProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [DoctorProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [DoctorProfileController::class, 'update'])->name('profile.update');
});
