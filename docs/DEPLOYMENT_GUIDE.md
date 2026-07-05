# Panduan Deployment NURISK (Production Readiness)

Dokumen ini merupakan panduan final untuk mendeploy aplikasi NURISK ke environment production (Fase 3).

## [DEP-001] Konfigurasi Production (Nginx & PHP-FPM / Laravel Octane)

Karena tingginya intensitas sinkronisasi dari mobile dan fitur realtime, sangat direkomendasikan menggunakan **Laravel Octane (Swoole/FrankenPHP)**.

### Nginx Proxy untuk Octane:

```nginx
server {
    listen 80;
    server_name nurisk.example.com;
    server_tokens off;
    root /var/www/nurisk/public;

    index index.php;

    charset utf-8;

    location /index.php {
        try_files /not_exists @octane;
    }

    location / {
        try_files $uri $uri/ @octane;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log off;
    error_log  /var/log/nginx/nurisk-error.log error;

    error_page 404 /index.php;

    location @octane {
        set $suffix "";

        if ($uri = /index.php) {
            set $suffix ?$query_string;
        }

        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;

        proxy_pass http://127.0.0.1:8000$suffix;
    }
}
```

## [DEP-002] Supervisor Queue Workers & Reverb

Jalankan background workers dengan Supervisor.

### /etc/supervisor/conf.d/nurisk-worker.conf

```ini
[program:nurisk-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nurisk/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/nurisk/storage/logs/worker.log
stopwaitsecs=3600
```

### /etc/supervisor/conf.d/nurisk-reverb.conf (Websocket Server)

```ini
[program:nurisk-reverb]
command=php /var/www/nurisk/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/nurisk/storage/logs/reverb.log
```

## [DEP-003] Disaster Recovery & Database Backup

Penjadwalan Backup via Cron dan Artisan Schedule.
Gunakan package `spatie/laravel-backup`.

1. Install: `composer require spatie/laravel-backup`
2. Konfigurasi cron di server:
   ```bash
   * * * * * cd /var/www/nurisk && php artisan schedule:run >> /dev/null 2>&1
   ```
3. Konfigurasi `app/Console/Kernel.php` (atau `routes/console.php` di Laravel 11):
   ```php
   use Illuminate\Support\Facades\Schedule;

   Schedule::command('backup:run')->dailyAt('02:00');
   Schedule::command('backup:clean')->dailyAt('03:00');
   ```

Pastikan disk tujuan diatur di `config/backup.php` (rekomendasi: S3/Object Storage).
