<?php

declare(strict_types=1);

namespace App\Service;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class MonologFactory
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        $config = $container->get('config')['logger'] ?? [];
        
        $logger = new Logger($config['name'] ?? 'app');
        
        // Add processors
        $logger->pushProcessor(new PsrLogMessageProcessor());
        $logger->pushProcessor(new UidProcessor());
        
        // Add introspection processor for development
        if ($config['debug'] ?? false) {
            $logger->pushProcessor(new IntrospectionProcessor());
        }
        
        // Configure handlers based on environment
        $handlers = $this->createHandlers($config);
        foreach ($handlers as $handler) {
            $logger->pushHandler($handler);
        }
        
        return $logger;
    }
    
    private function createHandlers(array $config): array
    {
        $handlers = [];
        $logPath = $config['path'] ?? 'data/log';
        $level = $config['level'] ?? Level::Info;
        
        // Create log directory if it doesn't exist
        if (!is_dir($logPath)) {
            @mkdir($logPath, 0775, true);
        }
        
        // Check if directory is writable, if not use null handler
        if (!is_writable($logPath)) {
            // In test environment or when log dir is not writable, use NullHandler
            $nullHandler = new NullHandler();
            return [$nullHandler];
        }
        
        // Main rotating file handler for application logs
        $appHandler = new RotatingFileHandler(
            $logPath . '/app.log',
            $config['max_files'] ?? 30,
            $level
        );
        $appHandler->setFormatter($this->createFormatter());
        $handlers[] = $appHandler;
        
        // Error log handler for errors and above
        $errorHandler = new RotatingFileHandler(
            $logPath . '/error.log',
            $config['max_files'] ?? 30,
            Level::Error
        );
        $errorHandler->setFormatter($this->createFormatter());
        $handlers[] = $errorHandler;
        
        // Development: also log to stdout if debug mode
        if ($config['debug'] ?? false) {
            $stdoutHandler = new StreamHandler('php://stdout', Level::Debug);
            $stdoutHandler->setFormatter($this->createFormatter());
            $handlers[] = $stdoutHandler;
        }
        
        return $handlers;
    }
    
    private function createFormatter(): LineFormatter
    {
        $format = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $dateFormat = 'Y-m-d H:i:s';
        
        $formatter = new LineFormatter($format, $dateFormat);
        $formatter->includeStacktraces(true);
        
        return $formatter;
    }
}