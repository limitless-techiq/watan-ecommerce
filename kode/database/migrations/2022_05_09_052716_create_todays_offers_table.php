<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todays_offers', function (Blueprint $table) {
            $table->id();
            $table->string('uid',100)->index()->nullable();
            $table->enum('discount_type',[0,1])->default(1)->comment('Percent  : 1,Flat : 0');
            $table->decimal('amount', 18,8)->default(0);
            $table->text('product_id')->nullable();
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
        Schema::dropIfExists('todays_offers');
    }
};
