<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Minimal Portfolio',
                'slug' => 'minimal-portfolio',
                'description' => 'Template clean, sederhana, dan modern minimal. Cocok untuk mahasiswa atau pemula.',
                'preview_image' => 'templates/minimal-portfolio.jpg',
                'style' => 'minimal',
                'is_active' => true,
            ],
            [
                'name' => 'Creative Portfolio',
                'slug' => 'creative-portfolio',
                'description' => 'Template visual, dinamis, dan ekspresif. Cocok untuk content creator atau designer.',
                'preview_image' => 'templates/creative-portfolio.jpg',
                'style' => 'creative',
                'is_active' => true,
            ],
            [
                'name' => 'Professional Portfolio',
                'slug' => 'professional-portfolio',
                'description' => 'Template formal, elegan, dan profesional. Cocok untuk developer atau freelancer profesional.',
                'preview_image' => 'templates/professional-portfolio.jpg',
                'style' => 'professional',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}