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
        Schema::create('webrtc_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->constrained('streams');
            $table->string('sender_type'); // 'host' or 'guest'
            $table->string('receiver_type'); // 'host' or 'guest'
            $table->text('data'); // JSON: SDP or ICE
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webrtc_signals');
    }
};
