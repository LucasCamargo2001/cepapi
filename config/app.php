<?php

use Cake\Cache\Engine\FileEngine;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Log\Engine\ConsoleLog;
use Cake\Mailer\Transport\MailTransport;
use function Cake\Core\env;
use Cake\Log\Engine\FileLog;

return [
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    'App' => [
        'namespace' => 'App',
        'encoding' => env('APP_ENCODING', 'UTF-8'),
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
        'defaultTimezone' => env('APP_DEFAULT_TIMEZONE', 'UTC'),
        'base' => false,
        'dir' => 'src',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        'fullBaseUrl' => false,
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [ROOT . DS . 'templates' . DS],
            'locales' => [RESOURCES . 'locales' . DS],
        ],
    ],

    'Security' => [
        'salt' => env('SECURITY_SALT'),
    ],

    'Cache' => [
        'default' => [
            'className' => FileEngine::class,
            'path' => CACHE,
        ],

        '_cake_translations_' => [
            'className' => FileEngine::class,
            'prefix' => 'cake_translations_',
            'path' => CACHE . 'persistent' . DS,
            'serialize' => true,
            'duration' => '+1 years',
        ],

        '_cake_model_' => [
            'className' => FileEngine::class,
            'prefix' => 'cake_model_',
            'path' => CACHE . 'models' . DS,
            'serialize' => true,
            'duration' => '+1 years',
        ],

        'cep' => [
            'className' => FileEngine::class,
            'prefix' => 'cep_api_',
            'path' => CACHE . 'cep' . DS,
            'duration' => '+30 days',
            'serialize' => true,
        ],
    ],

    'Error' => [
        'errorLevel' => E_ALL,
        'log' => true,
        'trace' => false,
    ],

    'EmailTransport' => [
        'default' => [
            'className' => MailTransport::class,
            'host' => 'localhost',
            'port' => 25,
            'timeout' => 30,
        ],
    ],

    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => 'you@localhost',
        ],
    ],

    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',
            'encoding' => 'utf8mb4',
            'cacheMetadata' => true,
            'log' => false,
        ],

        'test' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',
            'encoding' => 'utf8mb4',
            'cacheMetadata' => true,
            'log' => false,
        ],
    ],

        'Log' => [
            // Logs normais em arquivo (FICAM SALVOS)
            'debug' => [
                'className' => FileLog::class,
                'path' => LOGS,
                'file' => 'debug',
                'levels' => ['notice', 'info', 'debug'],
            ],

            // Erros em arquivo (FICAM SALVOS)
            'error' => [
                'className' => FileLog::class,
                'path' => LOGS,
                'file' => 'error',
                'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
            ],

            // Logs no terminal (tempo real)
            'stdout_info' => [
                'className' => ConsoleLog::class,
                'stream' => 'php://stdout',
                'levels' => ['info', 'notice'],
            ],

            'stdout_error' => [
                'className' => ConsoleLog::class,
                'stream' => 'php://stderr',
                'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
            ],
        ],


    'Session' => [
        'defaults' => 'php',
    ],

    'TestSuite' => [
        'errorLevel' => null,
        'fixtureStrategy' => null,
    ],
];
