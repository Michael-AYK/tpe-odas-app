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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('marchand_id')->nullable()->constrained('marchands')->onDelete('set null');
            $table->enum('role', ['agent', 'administrateur'])->default('agent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['marchand_id']);
            $table->dropColumn('marchand_id');
            $table->dropColumn('role');
        });
    }
};
