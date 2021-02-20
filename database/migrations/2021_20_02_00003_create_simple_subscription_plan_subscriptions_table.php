<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimpleSubscriptionPlanSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('simple_subscription_plan_subscriptions', function (Blueprint $table) {
            
            $table->bigIncrements('id');

            $table->morphs('subscriber');
            $table->unsignedBigInteger('simple_subscription_plan_id');
            $table->string('slug');
            $table->string('name');
            $table->string('description')->nullable();
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('cancels_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->string('timezone')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('simple_subscription_plan_subscriptions');
    }
}
