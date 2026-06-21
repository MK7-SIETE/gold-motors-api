<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Message;
use App\Models\SiteConfig;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super admin account ────────────────────────
        User::firstOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@goldmotors.zm')],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make(env('SUPER_ADMIN_PASSWORD', 'Platform$uper2025!')),
                'role'      => 'super',
                'is_active' => true,
            ]
        );

        // ── Dealer account ─────────────────────────────
        $dealer = User::firstOrCreate(
            ['email' => 'admin@goldmotors.com'],
            [
                'name'      => 'Gold Motors Admin',
                'password'  => Hash::make('GoldAdmin2025!'),
                'role'      => 'dealer',
                'is_active' => true,
            ]
        );

        // ── Sample cars (only if none exist) ──────────
        if (Car::count() === 0) {
            $cars = [
                [
                    'make' => 'Toyota', 'model' => 'Land Cruiser V8', 'year' => 2020,
                    'price' => 285000, 'mileage' => 45000, 'fuel' => 'Diesel',
                    'transmission' => 'Automatic', 'color' => 'White', 'body_type' => 'SUV',
                    'condition' => 'Foreign Used', 'engine' => '4.5L V8', 'power' => '232hp',
                    'torque' => '650Nm', 'seats' => 7, 'doors' => 4, 'drive' => '4WD',
                    'description' => 'Immaculate Land Cruiser V8 in excellent condition. Full service history.',
                    'features' => ['Leather seats', 'Sunroof', 'Reverse camera', 'Cruise control', 'Climate control'],
                    'is_featured' => true, 'is_available' => true, 'views' => 142,
                ],
                [
                    'make' => 'BMW', 'model' => 'X5 xDrive40i', 'year' => 2021,
                    'price' => 420000, 'mileage' => 32000, 'fuel' => 'Petrol',
                    'transmission' => 'Automatic', 'color' => 'Black', 'body_type' => 'SUV',
                    'condition' => 'Foreign Used', 'engine' => '3.0L Inline-6', 'power' => '340hp',
                    'torque' => '450Nm', 'seats' => 5, 'doors' => 4, 'drive' => 'AWD',
                    'description' => 'Stunning BMW X5 with M-Sport package. Full options including panoramic roof.',
                    'features' => ['M-Sport package', 'Panoramic roof', 'Harman Kardon', 'Head-up display'],
                    'is_featured' => true, 'is_available' => true, 'views' => 218,
                ],
                [
                    'make' => 'Mercedes-Benz', 'model' => 'C300 AMG Line', 'year' => 2019,
                    'price' => 390000, 'mileage' => 61000, 'fuel' => 'Petrol',
                    'transmission' => 'Automatic', 'color' => 'Silver', 'body_type' => 'Sedan',
                    'condition' => 'Foreign Used', 'engine' => '2.0L Turbo', 'power' => '258hp',
                    'torque' => '400Nm', 'seats' => 5, 'doors' => 4, 'drive' => 'RWD',
                    'description' => 'Elegant Mercedes C300 AMG Line with full options.',
                    'features' => ['AMG body kit', 'Burmester sound', 'Memory seats', 'LED headlights'],
                    'is_featured' => false, 'is_available' => true, 'views' => 87,
                ],
                [
                    'make' => 'Toyota', 'model' => 'Hilux Double Cab', 'year' => 2018,
                    'price' => 195000, 'mileage' => 88000, 'fuel' => 'Diesel',
                    'transmission' => 'Manual', 'color' => 'Silver', 'body_type' => 'Pickup',
                    'condition' => 'Foreign Used', 'engine' => '2.8L D-4D', 'power' => '177hp',
                    'torque' => '450Nm', 'seats' => 5, 'doors' => 4, 'drive' => '4WD',
                    'description' => 'Reliable Hilux in great working condition. Perfect for business and adventure.',
                    'features' => ['Canopy', 'Tow bar', 'Bull bar', 'Diff lock'],
                    'is_featured' => false, 'is_available' => true, 'views' => 64,
                ],
                [
                    'make' => 'Volkswagen', 'model' => 'Polo Vivo', 'year' => 2022,
                    'price' => 85000, 'mileage' => 18000, 'fuel' => 'Petrol',
                    'transmission' => 'Manual', 'color' => 'Red', 'body_type' => 'Hatchback',
                    'condition' => 'Local Used', 'engine' => '1.4L', 'power' => '85hp',
                    'torque' => '132Nm', 'seats' => 5, 'doors' => 4, 'drive' => 'FWD',
                    'description' => 'Near-new Polo Vivo with low mileage. Perfect city car.',
                    'features' => ['Bluetooth', 'USB', 'Electric windows', 'ABS'],
                    'is_featured' => false, 'is_available' => true, 'views' => 43,
                ],
                [
                    'make' => 'Land Rover', 'model' => 'Discovery Sport', 'year' => 2020,
                    'price' => 320000, 'mileage' => 52000, 'fuel' => 'Diesel',
                    'transmission' => 'Automatic', 'color' => 'Blue', 'body_type' => 'SUV',
                    'condition' => 'Foreign Used', 'engine' => '2.0L Ingenium', 'power' => '204hp',
                    'torque' => '430Nm', 'seats' => 7, 'doors' => 4, 'drive' => 'AWD',
                    'description' => 'Sophisticated Discovery Sport with 7 seats and full options.',
                    'features' => ['7 seats', 'Terrain response', 'Meridian sound', 'Pano roof'],
                    'is_featured' => true, 'is_available' => true, 'views' => 109,
                ],
            ];

            foreach ($cars as $carData) {
                Car::create(array_merge($carData, ['user_id' => $dealer->id]));
            }
        }

        // ── Sample messages (only if none exist) ──────
        if (Message::count() === 0) {
            $messages = [
                [
                    'name' => 'Mwansa Chilufya', 'email' => 'mwansa@email.com',
                    'phone' => '+260 97 123 4567',
                    'subject' => 'Enquiry about Land Cruiser',
                    'message' => 'Good morning, I am interested in the Toyota Land Cruiser V8. Is it still available?',
                    'car_id' => 1, 'type' => 'car_enquiry', 'is_read' => false,
                ],
                [
                    'name' => 'Grace Banda', 'email' => 'grace.banda@email.com',
                    'phone' => '+260 96 987 6543',
                    'subject' => 'Import request — Toyota Prado',
                    'message' => 'I would like to source a Toyota Prado 2022 from Japan. My budget is K250,000.',
                    'car_id' => null, 'type' => 'import_request', 'is_read' => false,
                ],
                [
                    'name' => 'David Phiri', 'email' => 'd.phiri@company.zm',
                    'phone' => '+260 95 555 0101',
                    'subject' => 'BMW X5 — test drive request',
                    'message' => 'I am interested in the BMW X5. Can I schedule a test drive this Saturday?',
                    'car_id' => 2, 'type' => 'car_enquiry', 'is_read' => true,
                ],
            ];

            foreach ($messages as $msgData) {
                Message::create($msgData);
            }
        }

        // ── Site config ────────────────────────────────
        $configs = [
            'dealership_name' => 'Gold Motors General Dealers Ltd',
            'phone'           => '+260 97X XXX XXX',
            'email'           => 'info@goldmotors.zm',
            'address'         => 'Plot 1234, Cairo Road, Lusaka, Zambia',
            'about'           => 'Gold Motors General Dealers Limited is a Zambian premium pre-owned vehicle dealership.',
            'facebook'        => '',
            'instagram'       => '',
            'twitter'         => '',
        ];

        foreach ($configs as $key => $value) {
            SiteConfig::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}