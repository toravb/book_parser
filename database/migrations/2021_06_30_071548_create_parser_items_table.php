<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateParserItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parser_items', function (Blueprint $table) {
            $table->id('ID');
            $table->integer('site_id')->index('site_id');
            $table->boolean('Status')->default(true)->nullable();
            $table->boolean('New')->default(true)->nullable();
            $table->boolean('IsDeleted')->default(false)->nullable();
            $table->timestamp('Last_modified')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
            $table->string('Name', 300)->nullable();
            $table->string('Articul', 50)->nullable();
            $table->string('Url', 300)->nullable()->index('Url');
            $table->boolean('Is_available')->nullable();
            $table->decimal('Price', '10', '0')->nullable();
            $table->decimal('Price_action', '10', '0')->nullable();
            $table->string('Quantity', 50)->nullable();
            $table->json('Params')->nullable();
            $table->text('Series')->nullable();
            $table->text('Components')->nullable();
            $table->text('Accessories')->nullable();
            $table->string('proxy_ip', 30);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parser_items');
    }
}
