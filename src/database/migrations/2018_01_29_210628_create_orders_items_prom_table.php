<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersItemsPromTable extends Migration
{
    public function up()
    {
        Schema::create('orders_items_prom', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_code')->unsigned();
            $table->integer('product_code')->unsigned();
            $table->smallInteger('qty')->unsigned();
            $table->decimal('price')->unsigned();

            //$table->timestamp('created_at')->useCurrent();
            //$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders_items_prom');
    }
}
