<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); // PK, bigint, NOT NULL

            $table->unsignedBigInteger('user_id'); // FK, NOT NULL
            $table->date('date'); // NOT NULL

            $table->dateTime('clock_in')->nullable(); // NULL 許可
            $table->dateTime('clock_out')->nullable(); // NULL 許可

            $table->tinyInteger('status'); // NOT NULL

            $table->timestamps(); // created_at, updated_at（どちらも NOT NULL）

            // UNIQUE KEY (user_id, date)
            $table->unique(['user_id', 'date']);

            // FOREIGN KEY
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('attendances');
    }
}
