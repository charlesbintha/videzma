<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            UsersSeeder::class,
            PatientProfilesSeeder::class,
            DoctorProfilesSeeder::class,
            DoctorDocumentsSeeder::class,
            LocationsSeeder::class,
            SlaSeeder::class,
            ConsultationsSeeder::class,
            AppointmentsSeeder::class,
            AvailabilitiesSeeder::class,
            NotificationPreferencesSeeder::class,
            DeviceTokensSeeder::class,
            NotificationsSeeder::class,
            NavigationSeeder::class,
            AuditActionsSeeder::class,
        ]);
    }
}
