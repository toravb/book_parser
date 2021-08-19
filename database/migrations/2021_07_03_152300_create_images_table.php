<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id')->index('site_id');
            $table->integer('item_id')->index('item_id');
            $table->boolean('Status')->nullable();
            $table->boolean('isDeleted')->default(false);
            $table->string('link', 300)->index('link');
            $table->timestamp('date')->nullable();
            $table->boolean('doParse')->default(true);
            $table->string('proxy_ip', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
