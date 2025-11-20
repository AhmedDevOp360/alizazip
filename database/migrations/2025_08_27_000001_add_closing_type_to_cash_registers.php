<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_registers', 'closing_type')) {
                $table->string('closing_type', 32)->nullable()->after('status'); // 'pos' | 'general'
            }
        });
    }

    public function down()
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            if (Schema::hasColumn('cash_registers', 'closing_type')) {
                $table->dropColumn('closing_type');
            }
        });
    }
};


