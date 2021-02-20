<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimpleSubscriptionPlanSubscriptionsTable extends Migration
{
    public function up()
    {
        // Laravel Str::plural('usage) -> usages
        Schema::create('simple_subscription_plan_subscription_usages', function (Blueprint $table) {
            
            $table->bigIncrements('id');

            $table->morphs('subscriber');
            $table->unsignedBigInteger('simple_subscription_plan_subscription_id')->unsigned();
            $table->unsignedBigInteger('simple_subscription_plan_feature_id')->unsigned();
            $table->smallInteger('used')->unsigned();
            $table->dateTime('valid_until')->nullable();
            $table->string('timezone')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('simple_subscription_plan_subscription_usages');
    }
}
