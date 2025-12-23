<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id(); // PK, bigint, NOT NULL

            $table->unsignedBigInteger('attendance_id'); // FK, NOT NULL

            $table->dateTime('break_start'); // NOT NULL
            $table->dateTime('break_end');   // NOT NULL

            $table->timestamps(); // created_at, updated_at（どちらも NOT NULL）

            // FOREIGN KEY
            $table->foreign('attendance_id')
                ->references('id')
                ->on('attendances')
                ->onDelete('cascade');
            // ※ cascade は要件に応じて変更可能
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('breaks');
    }
}
