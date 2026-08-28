<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateSenderColumn(['user', 'assistant'], 'assistant');
    }

    public function down(): void
    {
        $this->updateSenderColumn(['user', 'ai'], 'user');
    }

    private function updateSenderColumn(array $enum, string $default): void
    {
        Schema::table('chat_messages', function (Blueprint $table) use ($enum, $default) {
            if (DB::getDriverName() === 'mysql') {
                $values = implode("', '", $enum);
                DB::statement("ALTER TABLE chat_messages MODIFY sender ENUM('{$values}') NOT NULL DEFAULT '{$default}'");
            } else {
                $table->dropColumn('sender');
                $table->enum('sender', $enum)->default($default);
            }
        });
    }
};