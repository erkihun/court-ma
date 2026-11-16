<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional custom colours for the sidebar, top bar and footer ("chrome").
 * Additive: null means "use the interface style's default", so nothing changes
 * until an administrator picks a colour.
 */
return new class extends Migration
{
    private const COLUMNS = ['chrome_sidebar_bg', 'chrome_topbar_bg', 'chrome_footer_bg'];

    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $after = Schema::hasColumn('system_settings', 'ui_style_user_choice') ? 'ui_style_user_choice' : null;

            foreach (self::COLUMNS as $column) {
                if (! Schema::hasColumn('system_settings', $column)) {
                    $definition = $table->string($column, 7)->nullable();
                    if ($after !== null) {
                        $definition->after($after);
                        $after = $column;
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            foreach (self::COLUMNS as $column) {
                if (Schema::hasColumn('system_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
