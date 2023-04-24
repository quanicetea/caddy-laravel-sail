<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://laravel.com/docs/9.x"><img src="https://img.shields.io/badge/laravel-9.19-brightgreen.svg" alt="Build Status"></a>
<a href="https://laravel.com/docs/9.x/sail"><img src="https://img.shields.io/badge/sail-1.0.1-brightgreen.svg" alt="Total Downloads"></a>

# Getting Started

## Installing manual

### 1. Create project

```bash
# Create a new Laravel project version 9.* via composer
composer create-project laravel/laravel:^9 example

# Install sail
php artisan sail:install
```

### 2. Initial Configuration

#### .env

```bash
# add two keys 
APP_URL=https://laravel.test
APP_SERVICE=laravel.test
```

#### app.php

```bash
# config/app.php
# add key
'service' => env('APP_SERVICE', 'laravel.test'),
```

#### CaddyProxyController.php

```bash
# Create CaddyProxyController 
php artisan make:controller CaddyProxyController
```

```bash
# Add this function to CaddyProxyController
# app/Http/Controllers/CaddyProxyController.php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaddyProxyController extends Controller
{
    public function verifyDomain(Request $request)
    {
        $authorizedDomains = [
            config('app.service'),           // laravel.test
            'localhost',
            // Add subdomains here
        ];

        if (in_array($request->query('domain'), $authorizedDomains)) {
            return response('Domain Authorized');
        }

        // Abort if there's no 200 response returned above
        abort(503);
    }
}
```

#### web.php

```bash
# Add route to handle verifyDomain()
# routes/web.php
<?php
[...]
use App\Http\Controllers\CaddyProxyController;

[...]
Route::get('/domain-verify', [CaddyProxyController::class, 'verifyDomain')];
```

#### docker-compose.yml

```bash
# Remove port
version: '3'
services:
    laravel.test
        [...]
        # ports:
        #     - '${APP_PORT:-80}:80'
        #     - '${VITE_PORT:-5173}:${VITE_PORT:-5173}'
    [...]
```

```bash
# Add Caddy service and volume
version: '3'
services:
    laravel.test
    [...]
    caddy:
        image: caddy:latest
        restart: unless-stopped
        ports:
            - '${APP_PORT:-80}:80'
            - '${APP_SECURE_PORT:-443}:443'
            - '${VITE_PORT:-5173}:${VITE_PORT:-5173}'
        volumes:
            - './Caddyfile:/etc/caddy/Caddyfile'
            - sail-caddy:/data
            - sail-caddy:/config
        networks:
            - sail
    [...]
volumes:
    [...]
    sail-caddy:
        driver: local
```
#### Caddyfile
```bash
# Create Caddyfile like file below:
# ./Caddyfile
{
    on_demand_tls {
        ask http://laravel.test/domain-verify
    }
    local_certs
}

:443 {
    tls internal {
        on_demand
    }

    reverse_proxy laravel.test {
        header_up Host {host}
        header_up X-Real-IP {remote}
        header_up X-Forwarded-For {remote}
        header_up X-Forwarded-Port {server_port}
        header_up X-Forwarded-Proto {scheme}

        health_timeout 5s
    }
}
```

### 3. Edit your hosts file

```bash
[...]
127.0.0.1       laravel.test
[...]
```
### 4. Install the Laravel dependencies, including Laravel Sail.

```bash
docker run --rm --interactive --tty -v $(pwd):/app composer install --ignore-platform-reqs
```

### 5. Build and run the Docker containers using Laravel Sail.

```bash
./vendor/bin/sail up --build --remove-orphans -d
```

### 6. Run database migrations.

```bash
./vendor/bin/sail artisan migrate
```

### 7. Install NPM dependencies and run the Vite dev script.

```bash
./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev
```

### 8 . Visit the project URL in the browser.

https://laravel.test

##  Or you can clone this project

### First run

You must clone .env from .env.example

```bash
cp .env.example .env
```

And continue configuration from step 3

### For the next run

Just run

```bash
./vendor/bin/sail up -d
```
