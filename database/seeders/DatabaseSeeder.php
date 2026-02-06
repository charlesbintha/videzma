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
            ClientProfilesSeeder::class,
            DriverProfilesSeeder::class,
            DriverDocumentsSeeder::class,
            LocationsSeeder::class,
            ServiceRequestsSeeder::class,
            InterventionsSeeder::class,
            NotificationPreferencesSeeder::class,
            DeviceTokensSeeder::class,
            NotificationsSeeder::class,
        ]);
    }
}
