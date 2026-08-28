<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compatibility_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_a_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('category_b_id')->constrained('categories')->cascadeOnDelete();
            $table->string('spec_key_a');
            $table->string('spec_key_b');
            $table->enum('rule_type', ['equals', 'gte', 'lte', 'contains']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compatibility_rules');
    }
};
