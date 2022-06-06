<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text('name')->nullable();
            $table->text('type_job_id')->nullable();
            $table->integer('num_indicator_id')->nullable();
            $table->float('min_norm_numeric_coef_product', 10,4)->default('1.0000');
            $table->float('norm_numeric_coef_product', 10, 4)->nullable('1.0000');
            $table->integer('edin_rascenka')->nullable();
            $table->string('blocked')->default('Нет');

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
        Schema::dropIfExists('products');
    }
}
