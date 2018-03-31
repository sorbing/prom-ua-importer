<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsPromTable extends Migration
{
    public function up()
    {
        Schema::create('products_prom', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned();
            $table->string('sku', 20)->nullable();
            $table->integer('code')->unsigned()->unique();
            $table->string('group_name')->nullable();
            $table->string('name');
            $table->decimal('price');
            $table->json('prices')->nullable();
            $table->decimal('discount')->default(0);
            $table->char('currency', 5)->default('');
            $table->smallInteger('quantity')->unsigned()->default(0);
            $table->smallInteger('pre_order_days')->unsigned()->default(0);
            $table->char('unit', 10)->nullable();
            $table->smallInteger('sales_num')->unsigned()->default(0);
            $table->integer('description_length')->unsigned()->default(0);
            $table->longText('description')->nullable();
            $table->string('pack_type', 50)->nullable();
            $table->string('url');
            $table->string('category_url')->nullable();
            $table->string('preview')->nullable();
            $table->longText('images')->nullable();
            $table->string('tags', 1000)->nullable();
            $table->string('labels')->nullable();
            $table->json('params')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('products_prom');
    }
}
