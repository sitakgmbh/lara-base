<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('push_subscriptions');

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('subscribable');
            $table->string('endpoint', 500);
            $table->string('public_key', 200)->nullable();
            $table->string('auth_token', 100)->nullable();
            $table->string('content_encoding')->nullable();
            $table->string('category', 50)->default('general');
			$table->string('device_name', 100)->nullable();
            $table->timestamps();

            $table->unique(
                ['subscribable_id', 'subscribable_type', 'endpoint', 'category'],
                'push_subs_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};