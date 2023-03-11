<?php

namespace LLoadout\Microsoftgraph;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MicrosoftgraphServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('microsoftgraph');

        $this->app['router']->get('graph/connect', [
            'uses' => '\LLoadout\Microsoftgraph\Microsoftgraph@connect',
            'as' => 'graph.connect',
        ])->middleware('web');

        $this->app['router']->get('graph/callback', [
            'uses' => '\LLoadout\Microsoftgraph\Microsoftgraph@callback',
            'as' => 'graph.callback',
        ])->middleware('web');

        $config = $this->app['config']->get('services', []);
        $this->app['config']->set('services', array_merge(['microsoft' => [
            'client_id' => env('MS_CLIENT_ID'),
            'client_secret' => env('MS_CLIENT_SECRET'),
            'redirect' => env('MS_REDIRECT_URL'),
        ]], $config));

        $config = $this->app['config']->get('mail', []);
        $this->app['config']->set('mail.mailers', array_merge(['microsoftgraph' => [
            'transport' => 'microsoftgraph',
        ]], $config['mailers']));
    }
}
