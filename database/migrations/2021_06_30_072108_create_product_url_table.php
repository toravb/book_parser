<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductUrlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_url', function (Blueprint $table) {
            $table->id('ID');
            $table->integer('site_id')->index('site_id');
            $table->string('Url', 300)->index('Url');
            $table->boolean('doParsePages')->default(true)->index('doParsePages');
            $table->boolean('doParseImages')->default(true)->index('doParseImages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_url');
    }
}
