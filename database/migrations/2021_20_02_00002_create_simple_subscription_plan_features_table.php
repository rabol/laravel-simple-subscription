<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimpleSubscriptionPlanFeaturesTable extends Migration
{
    public function up()
    {
        Schema::create('simple_subscription_plan_features', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('simple_subscription_plan_id');

            $table->string('slug');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('value')->nullable();
            $table->smallInteger('resettable_period')->unsigned()->default(0)->nullable();
            $table->string('resettable_interval')->default('month')->nullable();
            $table->smallInteger('sort_order')->unsigned()->default(0)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('simple_subscription_plan_features');
    }
}
