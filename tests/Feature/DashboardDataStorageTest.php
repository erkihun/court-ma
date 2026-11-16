<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('dashboard counts only the application database tables', function (): void {
    $admin = User::factory()->create(['status' => 'active']);
    $admin->roles()->attach(Role::query()->firstOrCreate(['name' => 'admin']));

    $expected = DB::connection()->getDriverName() === 'mysql'
        ? (int) DB::selectOne(
            'select count(*) as aggregate from information_schema.tables where table_schema = ? and table_type = ?',
            [DB::connection()->getDatabaseName(), 'BASE TABLE']
        )->aggregate
        : count(Schema::getTables());

    // Schema::getTables() without a schema lists every database on the MySQL
    // server; the dashboard must report this application's tables only.
    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('dataStorage', fn (array $storage): bool => $storage['tables'] === $expected && $expected > 0);
});
