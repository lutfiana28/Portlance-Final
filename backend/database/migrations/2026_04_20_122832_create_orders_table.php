<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade');
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->enum('payment_status', [
                'unpaid',
                'waiting_verification',
                'rejected',
                'paid'
            ])->default('unpaid');
            $table->enum('order_status', [
                'draft',
                'waiting_payment',
                'waiting_payment_verification',
                'need_completion',
                'in_progress',
                'waiting_user_review',
                'revision',
                'completed',
                'closed'
            ])->default('draft');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};