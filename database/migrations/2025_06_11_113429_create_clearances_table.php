<?php

use App\Models\User;
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
        Schema::create('clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('jenis');
            $table->text('alasan');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');
            $table->string('bukti');
            $table->text('details');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearances');
    }
};
