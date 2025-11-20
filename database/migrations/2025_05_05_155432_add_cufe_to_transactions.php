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
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('cufe')->nullable()->after('selling_price_group_id');
            $table->text('qrstr')->nullable()->after('cufe');
            $table->boolean('is_valid')->nullable()->after('cufe');
            $table->string('prefix', 10)->nullable()->after('invoice_no');
            $table->integer('number_invoice')->nullable()->after('prefix');
            $table->string('resolution')->nullable()->after('number_invoice');
            $table->integer('counter_resend')->default(0)->after('resolution');
            $table->string('type_invoice',1)->default(0)->after('ref_no');
            $table->char('is_support_document',1)->default(0)->after('status'); 
            $table->enum('e_invoice',['si','no'])->default('no')->after('counter_resend');
            $table->string('discrepancyresponsedescription', 250)->nullable()->after('is_valid');
            $table->integer('discrepancyresponsecode')->nullable()->after('discrepancyresponsedescription');
            $table->text('rules')->nullable()->after('is_valid');
            $table->integer('status_code')->nullable()->after('rules');
            $table->string('payment_method', 50)->nullable()->after('payment_status');
            $table->integer('invoice_scheme_id')->nullable()->after('rules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('cufe');
            $table->dropColumn('qrstr');
            $table->dropColumn('is_valid');
            $table->dropColumn('prefix');
            $table->dropColumn('number_invoice');
            $table->dropColumn('resolution');
            $table->dropColumn('counter_resend');
            $table->dropColumn('type_invoice');
            $table->dropColumn('is_support_document');
            $table->dropColumn('e_invoice');
            $table->dropColumn('discrepancyresponsedescription');
            $table->dropColumn('discrepancyresponsecode');
            $table->dropColumn('rules');
            $table->dropColumn('status_code');
            $table->dropColumn('payment_method');
        });
    }
};
