<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id(); // PK, bigint, NOT NULL

            $table->unsignedBigInteger('user_id');        // FK, NOT NULL
            $table->unsignedBigInteger('attendance_id');  // FK, NOT NULL

            $table->dateTime('requested_clock_in');   // NOT NULL
            $table->dateTime('requested_clock_out');  // NOT NULL

            $table->string('note'); // NOT NULL
            $table->tinyInteger('status'); // NOT NULL

            $table->string('admin_comment')->nullable(); // NULL 許可

            $table->timestamps(); // created_at, updated_at（どちらも NOT NULL）

            // FOREIGN KEYS
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('attendance_id')
                ->references('id')
                ->on('attendances')
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
        Schema::dropIfExists('stamp_correction_requests');
    }
}
