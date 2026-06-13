<?php

namespace App\Providers;

use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // NeonDB: old libpq doesn't support SNI — inject endpoint ID into DSN options
        $this->app->bind('db.connector.pgsql', function () {
            return new class extends PostgresConnector {
                protected function getDsn(array $config): string
                {
                    $dsn = parent::getDsn($config);
                    if (!empty($config['neon_endpoint'])) {
                        $dsn .= ';options=endpoint=' . $config['neon_endpoint'];
                    }
                    return $dsn;
                }
            };
        });
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
