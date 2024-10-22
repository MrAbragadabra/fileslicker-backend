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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_id');
            $table->unsignedBigInteger('complaint_status_id');
            $table->string('description');
            $table->timestamps();

            $table->foreign('upload_id')->references('id')->on('uploads');
            $table->foreign('complaint_status_id')->references('id')->on('complaint_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
