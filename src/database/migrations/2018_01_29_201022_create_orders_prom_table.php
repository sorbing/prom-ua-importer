<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersPromTable extends Migration
{
    public function up()
    {
        Schema::create('orders_prom', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('code')->unsigned()->unique();
            $table->char('status', 10)->nullable();
            $table->dateTime('date');
            $table->decimal('cost');

            $table->string('payment_type', 50)->nullable();
            $table->string('source', 50)->nullable();
            $table->string('buyer_comment', 1000)->nullable();
            $table->string('seller_comment', 1000)->nullable();
            $table->string('labels')->nullable();
            $table->string('cancellation_reason', 50)->nullable();
            $table->string('cancellation_comment')->nullable();

            $table->string('delivery_type', 50)->nullable();
            $table->string('ttn', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('postal_index', 20)->nullable();

            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 13)->nullable();
            $table->string('customer_email', 50)->nullable();
            $table->json('broken_items_json')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders_prom');
    }
}
