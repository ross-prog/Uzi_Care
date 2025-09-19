<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('distribution_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('medicine_distributions')->onDelete('cascade');
            $table->string('campus'); // Which campus should receive this notification
            $table->string('type'); // 'incoming', 'outgoing', 'status_update'
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['campus', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_notifications');
    }
};
