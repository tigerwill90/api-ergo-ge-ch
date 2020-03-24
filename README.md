# Rest api for ergotherapie-ge.ch

### Infomaniak install

The following folder must never be deleted
- /api/assets
- /api/logs
- /api/pdf
- /api/api_keys

The following folder/file may be persisted between release
- .env

```
php -d allow_url_fopen=On ../.composer/composer install --no-dev --optimize-autoloader
```