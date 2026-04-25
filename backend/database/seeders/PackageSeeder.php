<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic',
                'price' => 50000,
                'revision_limit' => 1,
                'allowed_fields' => [
                    'full_name',
                    'photo_profile',
                    'profession',
                    'short_bio',
                    'contact_email',
                    'phone_number',
                    'domicile',
                    'social_links',
                ],
                'description' => 'Hanya dapat mengubah detail pribadi dasar.',
                'is_active' => true,
            ],
            [
                'name' => 'Standard',
                'price' => 100000,
                'revision_limit' => 2,
                'allowed_fields' => [
                    'full_name',
                    'photo_profile',
                    'profession',
                    'short_bio',
                    'contact_email',
                    'phone_number',
                    'domicile',
                    'social_links',
                    'skills',
                    'tools',
                    'capability_summary',
                ],
                'description' => 'Dapat mengubah detail pribadi dan data skill.',
                'is_active' => true,
            ],
            [
                'name' => 'Premium',
                'price' => 150000,
                'revision_limit' => 3,
                'allowed_fields' => [
                    'full_name',
                    'photo_profile',
                    'profession',
                    'short_bio',
                    'contact_email',
                    'phone_number',
                    'domicile',
                    'social_links',
                    'skills',
                    'tools',
                    'capability_summary',
                    'projects',
                    'services',
                    'testimonials',
                    'certificates',
                    'faqs',
                ],
                'description' => 'Dapat mengubah semua data dummy sesuai struktur template.',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}