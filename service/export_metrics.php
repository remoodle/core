<?php

declare(strict_types=1);

use Core\Config;
use Spiral\Goridge\Relay;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Metrics\CollectorType;
use Spiral\RoadRunner\Metrics\Metrics;

require_once __DIR__ . '/../vendor/autoload.php';

Config::loadConfigs();
$rpc = new RPC(
    Relay::create(Config::get('rpc.connection'))
);

$metrics = new Metrics($rpc);
$collectorType = CollectorType::Gauge;

foreach (((array)$rpc->call('jobs.Stat', null))['stats'] as $queue) {
    $metrics->set($collectorType->value . '_' . $queue['queue'], $queue['active'] ?? 0);
}
