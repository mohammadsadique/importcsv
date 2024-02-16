<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('common_fee_collection_headwises', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branchID')->nullable();
            $table->unsignedBigInteger('headID')->nullable();
            $table->unsignedBigInteger('commonFeeCollectionID')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('voucher_type')->nullable();
            $table->double('amount',8,2)->nullable();
            $table->string('receipt_number')->nullable();
            $table->date('transaction_dt')->nullable();
            $table->timestamps();

            $table->foreign('branchID')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('headID')->references('id')->on('fee_types')->onDelete('cascade');
            $table->foreign('commonFeeCollectionID')->references('id')->on('common_fee_collections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_fee_collection_headwises');
    }
};
