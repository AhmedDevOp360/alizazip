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
        Schema::table('products', function (Blueprint $table) {
            // Add new columns for sub-unit pricing
            $table->text('sub_unit_sell_prices')->nullable()->after('sub_unit_ids')->comment('JSON map of unit_id => sell_price');
            $table->text('sub_unit_margins')->nullable()->after('sub_unit_sell_prices')->comment('JSON map of unit_id => margin_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sub_unit_sell_prices', 'sub_unit_margins']);
        });
    }
};
