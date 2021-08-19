<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSitemapsTable extends Migration
{
//    /**
//     * Run the migrations.
//     *
//     * @return void
//     */
//    public function up()
//    {
//        Schema::create('sitemaps', function (Blueprint $table) {
//            $table->id();
//            $table->integer('site_id');
//            $table->string('sitemap', 300);
//            $table->boolean('doParse')->default(false);
//            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
//        });
//    }
//
//    /**
//     * Reverse the migrations.
//     *
//     * @return void
//     */
//    public function down()
//    {
//        Schema::dropIfExists('sitemaps');
//    }
}
