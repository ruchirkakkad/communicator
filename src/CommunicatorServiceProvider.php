<?php

namespace Picahoo\Communicator;

use Illuminate\Support\ServiceProvider;

class CommunicatorServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application services.
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/config/communicator.php' => config_path('communicator.php'),
		]);
		$this->mergeConfigFrom(__DIR__ . '/config/communicator.php', 'communicator');
	}

	/**
	 * Register the application services.
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('communicator', function () {
			return new Communicator();
		});
	}
}
