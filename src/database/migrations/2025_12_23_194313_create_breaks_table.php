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
            $table->id(); // PK

            $table->unsignedBigInteger('attendance_id')->index(); // FK

            $table->dateTime('break_start'); // 休憩開始（必須）
            $table->dateTime('break_end')->nullable(); // ★ 休憩終了（休憩中は null）

            $table->timestamps();

            // 外部キー
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
        Schema::dropIfExists('breaks');
    }
}
