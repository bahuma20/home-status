# Bahuma Home Status

A webapp for an always-on-device that displays relevant information like temperature,
to do list, alerts, images.

## Development

### Frontend

1. `cd frontend`
2. `npm install`
3. `npm run-script serve`

All changes that are made in the `frontend` directory will be automatically compiled and copied to `public/app`.

Also a live-reload server ist started on `http://localhost:9000`.

To build a production version run `npm run-script build`.

### Backend

1. Install Symfony console
2. Run `composer install`
3. Create the file `.env.local` and copy configurations from `.env` into this file and adjust it
4. `symfony serve`
5. Open the displayed link
6. Go to `/connect/google` to link your google account
7. Go to `/connect/twitch` to link your twitch account
8. Setup a daily cronjob for `/api/photos/sync`
9. Setup a hourly cronjob for `/api/twitch/sync`
