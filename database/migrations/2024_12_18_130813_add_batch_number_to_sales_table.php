<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchNumberToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add foreign key constraint for batch_number
            $table->foreign('batch_number')
                ->references('batch_number')
                ->on('purchases')
                ->onDelete('cascade')  // When a purchase is deleted, related sales will be deleted
                ->onUpdate('cascade'); // When a purchase's batch_number is updated, update related sales
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['batch_number']);
        });
    }
}
