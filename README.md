Local development

```
./vendor/bin/sail up
```
in another tab
```
pnpm run dev
```

open in browser at
```
http://0.0.0.0
```

## AI Paperworks Processing

In local development, to simulate the AI scheduler that processes documents automatically in production:

```bash
# Process pending AI paperworks manually (simulates production scheduler)
docker-compose exec laravel.test php artisan aipaperworks:process
```

This command:
- Processes documents with status 0 (pending)
- Sets documents to status 9 (error) if processing fails
- Stuck documents (status 1) can be reset manually via the frontend banners

In production, this runs automatically every minute via the cron scheduler.

### AI Paperworks Rebalancing (assegnazione backoffice)

In local development, per simulare il cron che riassegna le pratiche AI:

```bash
# Ribilancia assegnazioni AI (orfane + scadute)
docker-compose exec laravel.test php artisan aipaperworks:rebalance
```

Questo comando:
- Assegna un backoffice al brand meno carico per tutte le pratiche AI **orfane**  
  (`assigned_backoffice_id` null, `brand_id` valorizzato, `status != 5`)
- Riassegna a un altro backoffice compatibile le pratiche con **assegnazione scaduta**  
  (`assigned_backoffice_id` e `brand_id` valorizzati, `status != 5`, `assignment_expires_at` nel passato, `assignment_status` null/pending)

In produzione, questo comando viene eseguito automaticamente ogni minuto via cron (vedi `App\Console\Kernel`).

## Database Seeding (Alfacom Import)

### Comandi standard (RAM di default PHP, 128MB)

```bash
# Locale
php artisan db:seed --class="Database\\Seeders\\AlfacomSeeder"

# Docker
docker-compose exec laravel.test php artisan db:seed --class="Database\\Seeders\\AlfacomSeeder"
```

### Comandi con RAM personalizzata (consigliato per import massivi)

```bash
# Locale con 3GB di RAM
php -d memory_limit=3G artisan db:seed --class="Database\\Seeders\\AlfacomSeeder"

# Locale con 1GB di RAM
php -d memory_limit=1G artisan db:seed --class="Database\\Seeders\\AlfacomSeeder"

# Locale senza limite di RAM (usa tutta la RAM disponibile)
php -d memory_limit=-1 artisan db:seed --class="Database\\Seeders\\AlfacomSeeder"
```

### Fix post-import sui Customer

```bash
# Locale
php artisan fix:imported_customer

# Con RAM personalizzata
php -d memory_limit=3G artisan fix:imported_customer
```

> **Nota**: Il file `dump.sql` deve essere posizionato in `storage/imports/dump.sql` prima di lanciare il seeder.

# vue

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VS Code](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=johnsoncodehk.volar) (and disable Vetur).

## Type Support for `.vue` Imports in TS

Since TypeScript cannot handle type information for `.vue` imports, they are shimmed to be a generic Vue component type by default. In most cases this is fine if you don't really care about component prop types outside of templates.

However, if you wish to get actual prop types in `.vue` imports (for example to get props validation when using manual `h(...)` calls), you can run `Volar: Switch TS Plugin on/off` from VS Code command palette.

## Customize configuration

See [Vite Configuration Reference](https://vitejs.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Type-Check, Compile and Minify for Production

```sh
npm run build
```

### For run aipaperwork locally, use this command
```sh
./vendor/bin/sail artisan aipaperworks:process
```
