<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\AuthFilter; // Import class AuthFilter

class Filters extends BaseFilters
{
    /**
     * Configures aliases for Filter classes.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        // Alias 'auth' adalah konvensi yang umum, namun 'authFilter' juga baik.
        // Kita akan gunakan 'auth' agar lebih konsisten.
        'auth' => AuthFilter::class, 
    ];

    /**
     * List of special required filters.
     */
    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    /**
     * List of filter aliases that are always applied before and after every request.
     * Filter 'auth' akan diterapkan ke SEMUA rute, KECUALI yang disebutkan di 'except'.
     */
    public array $globals = [
        'before' => [
            'auth' => [
                // Gunakan wildcard (*) untuk mengecualikan semua rute di bawah 'auth'.
                // Ini lebih efisien dan anti-error.
                'except' => [
                    '/', // Halaman utama (login)
                    'auth', // Rute /auth jika ada
                    'auth/*' // Semua rute yang diawali dengan auth/
                ]
            ],
            // 'honeypot',
            // 'csrf',
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
        ],
    ];

    /**
     * List of filter aliases that works on a particular HTTP method.
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any before or after URI patterns.
     */
    public array $filters = [];
}
