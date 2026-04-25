<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            $table->string('full_name')->nullable();
            $table->string('photo_profile')->nullable();
            $table->string('profession')->nullable();
            $table->text('short_bio')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('domicile')->nullable();

            $table->json('social_links')->nullable();
            $table->json('skills')->nullable();
            $table->json('tools')->nullable();
            $table->text('capability_summary')->nullable();
            $table->json('projects')->nullable();
            $table->json('services')->nullable();
            $table->json('testimonials')->nullable();
            $table->json('certificates')->nullable();
            $table->json('faqs')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_portfolios');
    }
};