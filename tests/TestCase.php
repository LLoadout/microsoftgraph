<?php

namespace LLoadout\Microsoftgraph\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LLoadout\Microsoftgraph\MicrosoftgraphServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LLoadout\\Microsoftgraph\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            MicrosoftgraphServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_microsoftgraph_table.php.stub';
        $migration->up();
        */
    }
}
