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
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('merchant_registration')->nullable()->after('name');
            $table->unsignedBigInteger('department_id')->nullable()->after('merchant_registration');
            $table->integer('dv')->nullable()->after('contact_id');

            $table->unsignedBigInteger('municipality_id')->nullable()->after('department_id');

            $table->unsignedBigInteger('country_id')->nullable()->after('municipality_id');

            $table->unsignedBigInteger('type_document_identification_id')->nullable()->after('country_id');
            
            $table->unsignedBigInteger('type_regime_id')->nullable()->after('type_document_identification_id');
            
            $table->unsignedBigInteger('liability_id')->nullable()->after('type_regime_id');
            
            $table->unsignedBigInteger('type_organization_id')->nullable()->after('liability_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            //
        });
    }
};
