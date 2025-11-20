<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_sell_lines', 'location_id')) {
                $table->unsignedInteger('location_id')->nullable()->after('tax_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_sell_lines', 'location_id')) {
                $table->dropColumn('location_id');
            }
        });
    }
};


