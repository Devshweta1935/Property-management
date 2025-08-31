<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample agent
        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1-555-0123',
            'company_name' => 'Doe Real Estate',
            'license_number' => 'RE123456',
            'status' => 'active',
        ]);

        // Create additional sample agents
        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1-555-0456',
            'company_name' => 'Smith Properties',
            'license_number' => 'RE789012',
            'status' => 'active',
        ]);
    }
}
