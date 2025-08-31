<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sample agent users
        $agents = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567890',
                'company_name' => 'Real Estate Pro',
                'license_number' => 'RE123456',
                'status' => 'active',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567891',
                'company_name' => 'Smith Properties',
                'license_number' => 'RE123457',
                'status' => 'active',
            ],
        ];

        foreach ($agents as $agentData) {
            $agent = User::create($agentData);

            // Create sample properties for each agent
            $properties = [
                [
                    'title' => 'Beautiful Family Home',
                    'description' => 'Spacious 3-bedroom home with garden and modern amenities',
                    'address' => '123 Main Street',
                    'city' => 'Anytown',
                    'state' => 'CA',
                    'zip_code' => '90210',
                    'country' => 'USA',
                    'price' => 750000,
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'square_feet' => 1800,
                    'property_type' => 'house',
                    'status' => 'available',
                    'features' => ['garden', 'garage', 'fireplace'],
                    'images' => ['https://example.com/image1.jpg'],
                    'is_featured' => true,
                ],
                [
                    'title' => 'Modern Downtown Apartment',
                    'description' => 'Luxury 2-bedroom apartment in the heart of downtown',
                    'address' => '456 Downtown Ave',
                    'city' => 'Anytown',
                    'state' => 'CA',
                    'zip_code' => '90211',
                    'country' => 'USA',
                    'price' => 450000,
                    'bedrooms' => 2,
                    'bathrooms' => 2,
                    'square_feet' => 1200,
                    'property_type' => 'apartment',
                    'status' => 'available',
                    'features' => ['gym', 'pool', 'parking'],
                    'images' => ['https://example.com/image2.jpg'],
                    'is_featured' => false,
                ],
            ];

            foreach ($properties as $propertyData) {
                $agent->properties()->create($propertyData);
            }
        }

        $this->command->info('Sample data seeded successfully!');
        $this->command->info('Sample agents:');
        $this->command->info('- john@example.com / password123');
        $this->command->info('- jane@example.com / password123');
    }
}
