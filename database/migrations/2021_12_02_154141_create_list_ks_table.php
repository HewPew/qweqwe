<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListKsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('list_ks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('anketa_id')->nullable();
            $table->integer('product_id');
            $table->text('product_name');
            $table->decimal('count_indicator', 255, 9)->nullable();
            $table->float('ks_natur')->nullable();

            $table->longText('name_job')->nullable();
            $table->longText('types')->nullable();
            $table->longText('products')->nullable();
            $table->longText('construct_system')->nullable();
            $table->longText('lvl')->nullable();
            $table->float('height')->nullable();
            $table->integer('count_floors')->nullable();
            $table->float('max_height_floor')->nullable();
            $table->bigInteger('count_buildings')->nullable();

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
        Schema::dropIfExists('list_ks');
    }
}
