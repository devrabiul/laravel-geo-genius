<?php

namespace Devrabiul\LaravelGeoGenius\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimezoneColumn extends Command
{
    /**
     * Usage: php artisan geo:add-timezone-column users
     */
    protected $signature = 'geo:add-timezone-column {table : Table name to modify}';

    protected $description = 'Add a nullable timezone column to the given table if it does not exist';

    public function handle(): int
    {
        $table = $this->argument('table');

        if (! Schema::hasTable($table)) {
            $this->error("Table '{$table}' does not exist.");
            return self::FAILURE;
        }

        if (Schema::hasColumn($table, 'timezone')) {
            $this->warn("Column 'timezone' already exists on '{$table}' table.");
            return self::SUCCESS;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('timezone')->nullable()->after('updated_at');
            });

            $this->info("✅ Timezone column added successfully to '{$table}' table.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Error adding timezone column: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}