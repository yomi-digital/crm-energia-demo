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
