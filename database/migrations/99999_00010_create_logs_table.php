<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
		Schema::create('logs', function (Blueprint $table) {
			$table->id();
			$table->string('category');
			$table->string('level');
			$table->text('message');
			$table->text('context')->nullable();
			$table->timestamp('created_at');

			$table->index('level');
			$table->index('category');
			$table->index('created_at');
			$table->index(['level', 'category']);
		});
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};