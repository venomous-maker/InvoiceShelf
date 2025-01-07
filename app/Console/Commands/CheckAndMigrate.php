<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CheckAndMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-and-migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $databasePath = config('database.connections.sqlite.database');

        if (!File::exists($databasePath) || $databasePath === ':memory:') {
            $this->error("SQLite database file not found at {$databasePath}");

            if ($this->confirm('Do you want to create the SQLite database file?')) {
                File::put($databasePath, '');

                $this->info("SQLite database file created at {$databasePath}");
            } else {
                $this->info('Operation aborted. No database file was created.');
                return CommandAlias::FAILURE;
            }
        }

        $this->info('Running migrations...');
        Artisan::call('migrate', [], $this->output);

        return CommandAlias::SUCCESS;
    }
}
