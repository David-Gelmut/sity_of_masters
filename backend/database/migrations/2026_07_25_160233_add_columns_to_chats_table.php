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
        Schema::table('chats', function (Blueprint $table) {
            $table->string('type')->default('peer')->after('title'); // 'peer' (один на один) или 'group' (группа)
            $table->string('avatar_path')->nullable()->after('title'); # Аватар группы
            $table->foreignId('creator_id')->nullable()->after('title')->constrained('users')->onDelete('set null'); # Кто создал
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('type');
            $table->dropColumn('avatar_path');
            $table->dropColumn('creator_id');
        });
    }
};
