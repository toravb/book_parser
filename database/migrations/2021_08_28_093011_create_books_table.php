<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            $table->foreignId('author_id')->nullable();
            $table->foreignId('publisher_id')->nullable();
            $table->foreignId('series_id')->nullable();
            $table->foreignId('year_id')->nullable();
            $table->string('link', 120);
            $table->json('params');
            $table->foreign('author_id')
                ->references('id')
                ->on('authors');
            $table->foreign('publisher_id')
                ->references('id')
                ->on('publishers');
            $table->foreign('series_id')
                ->references('id')
                ->on('series');
            $table->foreign('year_id')
                ->references('id')
                ->on('years');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
