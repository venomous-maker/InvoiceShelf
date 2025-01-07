<?php

namespace App\Providers;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Override the migrate command
        $this->app->extend(MigrateCommand::class, function ($command, $app) {
            return new class($app['migrator'], $app['events']) extends MigrateCommand {
                protected function execute(InputInterface $input, OutputInterface $output): int
                {
                    $databasePath = config('database.connections.sqlite.database');

                    if ($databasePath === '') {
                        $databasePath = database_path('database.sqlite');
                    }

                    if (!File::exists($databasePath) || $databasePath === ':memory:') {
                        $this->output->writeln("<error>SQLite database file not found at {$databasePath}</error>");

                        if ($this->confirm('Do you want to create the SQLite database file?', true)) {
                            File::put($databasePath, '');
                            $this->output->writeln("<info>SQLite database file created at {$databasePath}</info>");
                        } else {
                            $this->output->writeln('<info>Operation aborted. No database file was created.</info>');
                            return 1; // Exit with error
                        }
                    }

                    return parent::execute($input, $output);
                }
            };
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
