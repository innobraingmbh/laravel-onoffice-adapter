<?php

declare(strict_types=1);

/*
 * Probe bootstrap.
 *
 * Boots a minimal Laravel container with the package's service provider
 * registered and onOffice credentials loaded from the package-root .env.
 * Use it from one-off scratch files to call the real API while building:
 *
 *     require __DIR__ . '/../bootstrap/probe.php';
 *
 *     use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;
 *     dump(AppointmentRepository::query()->dateRange(...)->get());
 *
 * Set ON_OFFICE_TOKEN / ON_OFFICE_SECRET in .env at the package root.
 */

use Dotenv\Dotenv;
use Innobrain\OnOfficeAdapter\OnOfficeAdapterServiceProvider;
use Orchestra\Testbench\Foundation\Application as TestbenchApplication;

require_once __DIR__.'/../vendor/autoload.php';

$packageRoot = dirname(__DIR__);

if (is_file($packageRoot.'/.env')) {
    Dotenv::createMutable($packageRoot)->safeLoad();
}

$token = $_ENV['ON_OFFICE_TOKEN'] ?? getenv('ON_OFFICE_TOKEN') ?: null;
$secret = $_ENV['ON_OFFICE_SECRET'] ?? getenv('ON_OFFICE_SECRET') ?: null;

if ($token === null || $secret === null || $token === '' || $secret === '') {
    fwrite(STDERR, "probe: ON_OFFICE_TOKEN / ON_OFFICE_SECRET missing — set them in {$packageRoot}/.env\n");
    exit(1);
}

$app = TestbenchApplication::create();

$app['config']->set('onoffice.token', $token);
$app['config']->set('onoffice.secret', $secret);

$app->register(OnOfficeAdapterServiceProvider::class);
$app->boot();

return $app;
