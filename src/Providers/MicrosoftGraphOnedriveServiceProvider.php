<?php

namespace LLoadout\Microsoftgraph\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use LLoadout\Microsoftgraph\OnedriveManager\OnedriveAdapter;
use LLoadout\Microsoftgraph\Traits\Authenticate;
use Microsoft\Graph\Graph;

class MicrosoftGraphOnedriveServiceProvider extends ServiceProvider
{
    use Authenticate;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Laravel 13 rebinds the Storage::extend() closure to the FilesystemManager,
        // so $this inside the closure is no longer this provider. Capture it explicitly.
        $provider = $this;

        Storage::extend('onedrive', static function ($app, $config) use ($provider) {
            $graph = (new Graph())->setAccessToken($provider->resolveAccessToken());

            $adapter = new OnedriveAdapter($graph, $config['root'] ?: 'root', true);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }

    /**
     * Resolve the Microsoft Graph access token from the current session.
     *
     * @return string
     */
    public function resolveAccessToken()
    {
        return $this->getAccessToken();
    }
}
