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
        $driver = Schema::connection($this->getConnection())->getConnection()->getDriverName();

        Schema::table('queue_room_files', function (Blueprint $table) use ($driver) {
            if ($driver === 'sqlite') {
                $table->foreignId('room_id')
                    ->default('')
                    ->constrained()
                    ->cascadeOnDelete();
            } else {
                $table->foreignId('room_id')
                    ->after('expired_at')
                    ->constrained()
                    ->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_room_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });
    }
};
