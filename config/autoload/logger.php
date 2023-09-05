<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use function Hyperf\Support\env;

$logDir = BASE_PATH . "/runtime/logs/";

$formatter = [
    'class'       => LogstashFormatter::class,
    'constructor' => [
        'applicationName' => 'custom_service',
    ],
];


$handlers = [
    'default' => [
        'handlers' => [
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/info.log',
                    'level'    => Level::Info,
                ],
                'formatter' => $formatter,
            ],
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/error.log',
                    'level'    => Level::Error,
                ],
                'formatter' => $formatter,
            ],
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/warning.log',
                    'level'    => Level::Warning,
                ],
                'formatter' => $formatter,
            ],
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/critical.log',
                    'level'    => Level::Critical,
                ],
                'formatter' => $formatter,
            ],
        ],
    ],
    'task' => [
        'handlers' => [
            [
                'class'       => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $logDir . '/task.log',
                    'level'    => Level::Info,
                ],
                'formatter' => $formatter,
            ],
        ],
    ],
];

$appEnv = env('APP_ENV', 'production');
if ($appEnv !== 'production') {
    $handlers['default']['handlers'][] = [
        'class'       => RotatingFileHandler::class,
        'constructor' => [
            'filename' => $logDir . '/debug.log',
            'level'    => Level::Debug,
        ],
        'formatter' => $formatter,
    ];
}

return $handlers;
