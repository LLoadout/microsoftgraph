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
        Storage::extend('onedrive', function ($app, $config) {
            $graph = (new Graph())->setAccessToken($this->getAccessToken());

            $adapter = new OnedriveAdapter($graph, $config['root'], true);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
