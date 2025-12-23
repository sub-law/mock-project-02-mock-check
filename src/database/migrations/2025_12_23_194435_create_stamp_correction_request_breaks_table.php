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

            $table->unsignedBigInteger('stamp_correction_request_id'); // FK, NOT NULL

            $table->dateTime('break_start'); // NOT NULL
            $table->dateTime('break_end');   // NOT NULL

            $table->timestamps(); // created_at, updated_at（どちらも NOT NULL）

            // FOREIGN KEY
            $table->foreign('stamp_correction_request_id', 'scrb_scr_id_fk')
                ->references('id')
                ->on('stamp_correction_requests')
                ->onDelete('cascade');

            // ※ cascade は要件に応じて調整可能
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
