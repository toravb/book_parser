<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books_pages', function (Blueprint $table) {
            $table->id();
            $table->integer('book_id');
            $table->integer('book_page');
            $table->text('content');
            $table->timestamps();
//            $table->foreign('book_id')
//                ->references('id')
//                ->on('books')
//                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_pages');
    }
}
