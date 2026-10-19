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
        $tableNames = config('permission.table_names');

        // Descripción visible del permiso y el grupo al que pertenece (config/constants.php).
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->string('description')->after('name');
            $table->enum('type', array_keys(config('constants.type_model')))->after('description');
            $table->enum('type_description', array_keys(config('constants.type_description')))->after('type');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->dropColumn(['description', 'type', 'type_description']);
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
