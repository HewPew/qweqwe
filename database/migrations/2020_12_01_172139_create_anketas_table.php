<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnketasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anketas', function (Blueprint $table) {
            $table->bigIncrements('id');

            /**
             * Общие поля
             */
            $table->string('type_anketa')->default('protokol');
            $table->integer('id_deal')->nullable();
            $table->longText('directions')->nullable();
            $table->text('name_job')->nullable();
            $table->decimal('squere', 255, 9)->nullable();
            $table->text('address')->nullable();

            $table->longText('types')->nullable();
            $table->longText('products')->nullable();
            $table->longText('construct_system')->nullable();
            $table->longText('lvl')->nullable();
            $table->float('height')->nullable();
            $table->integer('count_floors')->nullable();
            $table->float('max_height_floor')->nullable();
            $table->bigInteger('count_buildings')->nullable();

            $table->decimal('sum_ks', 255, 2)->nullable();
            $table->decimal('sum_kd', 255, 2)->nullable();
            $table->decimal('kd', 255, 2)->nullable();
            $table->decimal('kd_final', 255, 2)->nullable();

            $table->string('user_name')->default(0);
            $table->date('date')->nullable();

            /**
             * Системные поля
             */
            $table->integer('in_cart')->default(0);
            $table->longText('json_calc')->nullable();
            $table->longText('full_data')->nullable();

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
        Schema::dropIfExists('anketas');
    }
}
