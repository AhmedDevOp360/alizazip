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
        Schema::table('business', function (Blueprint $table) {
            $table->string('nit',20)->nullable()->after('name');
            $table->bigInteger('dv')->nullable()->after('nit');
            $table->string('merchant_registration')->nullable()->after('dv');

            $table->bigInteger('type_document_identification_id')->unsigned()->nullable()->after('merchant_registration');
            
            $table->bigInteger('type_organization_id')->unsigned()->nullable()->after('type_document_identification_id');
            
            $table->bigInteger('type_regime_id')->unsigned()->nullable()->after('type_organization_id');

            $table->string('dian_token')->nullable()->after('type_regime_id');
            $table->string('dian_active')->nullable()->after('dian_token');
            $table->enum('tax_enable', ['active','inactive'])->default('inactive')->after('dian_token');

            $table->unsignedBigInteger('department_id')->nullable()->after('dian_token');

            $table->unsignedBigInteger('municipality_id')->nullable()->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business', function (Blueprint $table) {
            $table->dropColumn('nit');
            $table->dropColumn('dv');
            $table->dropColumn('merchant_registration');
            $table->dropColumn('type_document_identification_id');
            $table->dropColumn('type_regime_id');
            $table->dropColumn('type_organization_id');
            $table->dropColumn('dian_token');
            $table->dropColumn('dian_active');
            $table->dropColumn('tax_enable');
            
        });
    }
};
