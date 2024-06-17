<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('friends', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('accepted_at');
        });
    }

    public function down()
    {
        Schema::table('friends', function (Blueprint $table) {
            $table->dropColumn('blocked_at');
        });
    }

};
