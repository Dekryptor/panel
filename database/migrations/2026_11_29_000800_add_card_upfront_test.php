<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('signup_variant', 16)->nullable()->after('trial_last_ends_at')->index();
            $table->timestamp('card_added_at')->nullable()->after('signup_variant');
        });
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropIndex(['signup_variant']);
            $table->dropColumn(['signup_variant', 'card_added_at']);
        });
    }
};
