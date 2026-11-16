<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Interface style (visual theme) settings. Additive: the default 'classic'
 * keeps the current look until an administrator picks another style.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('system_settings', 'ui_style')) {
                $table->string('ui_style', 32)->default('classic')->after('default_theme');
            }
            if (! Schema::hasColumn('system_settings', 'ui_styles_enabled')) {
                // null = every style is available to users
                $table->json('ui_styles_enabled')->nullable()->after('ui_style');
            }
            if (! Schema::hasColumn('system_settings', 'ui_style_user_choice')) {
                $table->boolean('ui_style_user_choice')->default(true)->after('ui_styles_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            foreach (['ui_style_user_choice', 'ui_styles_enabled', 'ui_style'] as $column) {
                if (Schema::hasColumn('system_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
