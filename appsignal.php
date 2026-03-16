<?php

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Symfony\Component\Dotenv\Dotenv;

if (file_exists(__DIR__ . '/.env.local')) {
    $dotenv = new Dotenv();
    $dotenv->load(__DIR__ . '/.env.local');
}

if (empty($_ENV['APPSIGNAL_APP_NAME']) ||
    empty($_ENV['APPSIGNAL_APP_ENV']) ||
    empty($_ENV['APPSIGNAL_PUSH_API_KEY']) ||
    empty($_ENV['APPSIGNAL_COLLECTOR_URL'])
) {
    // Skip OpenTelemetry configuration if required env vars are not set
    return;
}

$name = $_ENV['APPSIGNAL_APP_NAME'];
$environment = $_ENV['APPSIGNAL_APP_ENV'];
$pushApiKey = $_ENV['APPSIGNAL_PUSH_API_KEY'];

$serviceName = 'Symfony';
$collector = $_ENV['APPSIGNAL_COLLECTOR_URL'];

$revision = trim(shell_exec('git rev-parse HEAD 2>/dev/null')) ?: 'unknown';

$resource = ResourceInfoFactory::defaultResource()->merge(ResourceInfo::create(Attributes::create([
    'service.name' => $serviceName,
    'appsignal.config.name' => $name,
    'appsignal.config.environment' => $environment,
    'appsignal.config.push_api_key' => $pushApiKey,
    'appsignal.config.revision' => $revision,
    'appsignal.config.language_integration' => 'php',
    'appsignal.config.app_path' => __DIR__,
    'host.name' => gethostname(),
])));

$spanExporter = new SpanExporter(
    (new OtlpHttpTransportFactory())->create("$collector/v1/traces", 'application/x-protobuf')
);

$logExporter = new LogsExporter(
    (new OtlpHttpTransportFactory())->create("$collector/v1/logs", 'application/x-protobuf')
);

$reader = new ExportingReader(
    new MetricExporter(
        (new OtlpHttpTransportFactory())->create("$collector/v1/metrics", 'application/x-protobuf')
    )
);

$meterProvider = MeterProvider::builder()
    ->setResource($resource)
    ->addReader($reader)
    ->build();

$tracerProvider = TracerProvider::builder()
    ->addSpanProcessor(
        new SimpleSpanProcessor($spanExporter)
    )
    ->setResource($resource)
    ->setSampler(new ParentBased(new AlwaysOnSampler()))
    ->build();

$loggerProvider = LoggerProvider::builder()
    ->setResource($resource)
    ->addLogRecordProcessor(
        new SimpleLogRecordProcessor($logExporter)
    )
    ->build();

Sdk::builder()
    ->setTracerProvider($tracerProvider)
    ->setMeterProvider($meterProvider)
    ->setLoggerProvider($loggerProvider)
    ->setPropagator(TraceContextPropagator::getInstance())
    ->setAutoShutdown(true)
    ->buildAndRegisterGlobal();