<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchNumberToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Add batch_number column
            $table->string('batch_number')->nullable();

            // Add foreign key constraint linking to the purchases table
            $table->foreign('batch_number')
                  ->references('batch_number')
                  ->on('purchases')
                  ->onDelete('cascade')  // Cascade delete products if purchase is deleted
                  ->onUpdate('cascade'); // Cascade update batch_number changes
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
            // Remove foreign key constraint
            $table->dropForeign(['batch_number']);

            // Drop batch_number column
            $table->dropColumn('batch_number');
        });
    }
}
