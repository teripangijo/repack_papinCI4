<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The default database group.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     * Properti ini dideklarasikan di sini untuk menghindari error 'dynamic property'.
     */
    public array $default = [];

    /**
     * This database connection is used when
     * running PHPUnit database tests.
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed for simpler testing migrations.
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => 'utf8_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        // Mengisi koneksi 'default' dari file .env
        // Pastikan .env Anda sudah dimuat oleh CodeIgniter
        $this->default = [
            'DSN'      => env('database.default.DSN', ''),
            'hostname' => env('database.default.hostname', 'localhost'),
            'username' => env('database.default.username', ''),
            'password' => env('database.default.password', ''),
            'database' => env('database.default.database', ''),
            'DBDriver' => env('database.default.DBDriver', 'MySQLi'),
            'DBPrefix' => env('database.default.DBPrefix', ''),
            'pConnect' => env('database.default.pConnect', false),
            'DBDebug'  => env('database.default.DBDebug', true),
            'charset'  => env('database.default.charset', 'utf8'),
            'DBCollat' => env('database.default.DBCollat', 'utf8_general_ci'),
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => (int) env('database.default.port', 3306),
        ];

        // Ensure that in the test environment, the test database is used
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}