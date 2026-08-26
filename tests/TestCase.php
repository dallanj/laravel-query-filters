<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable();
            $table->string('title');
            $table->string('status');
            $table->string('location')->nullable();
            $table->boolean('active')->default(false);
            $table->date('published_at')->nullable();
        });
    }
}
