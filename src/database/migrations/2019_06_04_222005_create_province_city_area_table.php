<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvinceCityAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('province_city_area', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('省市县名称');
            $table->string('parent_id')->default(0)->comment('父级id');
            $table->enum('type', ['province', 'city', 'area', 'street'])->default('province')->comment('类型');
//            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('province_city_area');
    }
}
