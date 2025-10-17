<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimesheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // $table->year('year');//->nullable();
            // $table->tinyInteger('month');//->nullable();
            // $table->tinyInteger('day');//->nullable();
            $table->date('date');//->nullable();

            $table->time('punch_in');    //->nullable();
            $table->time('punch_out')->nullable();//->useCurrent()
            $table->time('break1_in')->nullable();
            $table->time('break1_out')->nullable();
            $table->time('break2_in')->nullable();
            $table->time('break2_out')->nullable();
            $table->string('remark')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timesheets');
    }
}
