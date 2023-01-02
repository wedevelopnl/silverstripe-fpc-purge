# silverstripe-fpc-purge
This module adds some cache purging capabilities to the website, to support FPC in nginx or apache.

## Requirements
* See `composer.json` requirements
* nginx with Lua module

## Installation

* `composer require wedevelopnl/silverstripe-fpc-purge`

### Configuring nginx

Make sure the Lua module is loaded in nginx. Then update your server configuration:

```
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=fastcgicache:100m max_size=5g inactive=60m use_temp_path=off;
fastcgi_cache_key "$scheme$request_method$host$request_uri";

server {
    location = /purge-cache {
        default_type 'text/plain';
    
        if ($request_method = PURGE) {
            content_by_lua_block {
                os.execute("rm -rf /var/cache/nginx/*")
                ngx.say("Cache cleared");
            }
        }
    }
    
    # ...
}
```

**NOTE:** Consider randomizing or otherwise protecting your URL to prevent abuse.

### Configuring the module

```yaml
---
Name: 'fpc-purge-config'
Only:
  environment: 'live'
---
WeDevelop\FPCPurge\FPCPurgeConfig:
  enabled: true
  endpoints:
    # Purging locally over HTTP
    - host: localhost:80
      method: PURGE
      path: /purge-cache
    # Purging externally over HTTPS
    - host: tls://example.com:443
      method: PURGE
      path: /purge-cache
    # Purging a specific server (useful when load balancing and purging all servers)
    - host: tls://10.0.0.5:443
      http_host: example.com # Required to tell nginx or apache what virtual host you want to connect to
      method: PURGE
      path: /purge-cache

Page:
  extensions:
    - WeDevelop\FPCPurge\Extensions\FPCPurgeExtension
```

Here you can enable the module and configure the endpoint used to purge.

You can test this configuration by going into the SilverStripe admin, then click FPC Purge in the sidebar and click the
Purge Cache button. It should tell you if it was successful.

We also add an extension to Page to purge the cache after publishing a page.

**NOTE:** The purge after publishing opens a connection, then sends a non-blocking request,
should have little to no impact on publishing performance depending on the endpoints.

### Setting up Cache Control

All of the above will not cache anything until you setup cache control.
You can either follow the [official SilverStripe docs](https://docs.silverstripe.org/en/4/developer_guides/performance/http_cache_headers/),
or use the extension included in this module for an easier foolproof implementation.

```yaml
PageController:
    extensions:
        - WeDevelop\FPCPurge\Extensions\FPCPurgeControllerExtension
```

Now you have to add a `updateCacheControl()` method to your PageController and configure the CacheControl headers.

```php
public function updateCacheControl(): void
{
    HTTPCacheControlMiddleware::singleton()
        ->enableCache()
        ->setSharedMaxAge(3600)
        ->setMaxAge(60);
}
```

**Shared Max Age:** the amount of time in seconds this page is allowed to be cached in your FPC (nginx, apache, etc.) \
**Max Age:** the amount of time in seconds this page is allowed to be cached in the browser

If you have another controller that extends the PageController but serves more dynamic data from an API for example,
you can override the CacheControl headers in that controller by overriding the updateCacheControl method.

```php
public function updateCacheControl(): void
{
    HTTPCacheControlMiddleware::singleton()
        ->enableCache()
        ->setSharedMaxAge(600)
        ->setMaxAge(0);
}
```

Here we set the max age to 0 to prevent it from being cached by the browser, and a relatively low shared max age.
This way cache can only be stale for 10 minutes.

### Sessions and CSRF tokens

It's important not to cache pages that are generated within the context of a session, for example a logged in user or
a CSRF token. Luckily, there are two things protecting us from this mistake.

1. SilverStripe will overrule our cache control headers when a session is active.
2. The nginx configuration triggers a bypass when a PHPSESSID is found.

## Default configuration

```yaml
WeDevelop\FPCPurge\FPCPurgeConfig:
  enabled: false
  endpoints: []
```

## License
See [License](LICENSE)

## Maintainers
* [WeDevelop](https://www.wedevelop.nl/) <development@wedevelop.nl>

## Development and contribution
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
See read our [contributing](CONTRIBUTING.md) document for more information.

### Getting started
We advise to use [Docker](https://docker.com)/[Docker compose](https://docs.docker.com/compose/) for development.\
We also included a [Makefile](https://www.gnu.org/software/make/) to simplify some commands

Our development container contains some built-in tools like `PHPCSFixer`.

#### Getting development container up
`make build` to build the Docker container and then run detached.\
If you want to only get the container up, you can simply type `make up`.

You can SSH into the container using `make sh`.

#### All make commands
You can run `make help` to get a list with all available `make` commands.
