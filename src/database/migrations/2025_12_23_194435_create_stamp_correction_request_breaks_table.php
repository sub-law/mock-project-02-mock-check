<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_request_breaks', function (Blueprint $table) {
            $table->id(); // PK, bigint, NOT NULL

            $table->unsignedBigInteger('stamp_correction_request_id');

            $table->dateTime('break_start')->comment('休憩開始');

            $table->dateTime('break_end')->nullable()->comment('休憩終了（null = 終了していない）');

            $table->timestamps();
            
            $table->foreign('stamp_correction_request_id', 'scrb_scr_id_fk')
            ->references('id')
            ->on('stamp_correction_requests')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stamp_correction_request_breaks');
    }
}
