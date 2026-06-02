# EtokBike Mobile API Contract

This document records the current backend contract needed by the Android app in
`/home/mhn70/StudioProjects/EtokBike`.

## Current Android App Behavior

The Android app is currently a server-driven JSON UI app. It does not use
Retrofit, OkHttp, or a typed API client. It uses `HttpURLConnection` directly
from `MainActivity`.

Relevant Android files:

- `/home/mhn70/StudioProjects/EtokBike/app/src/main/java/com/etokbike/app/MainActivity.java`
- `/home/mhn70/StudioProjects/EtokBike/app/src/main/assets/mock/manifest.json`
- `/home/mhn70/StudioProjects/EtokBike/app/src/main/assets/mock/screens/*.json`

Startup flow:

1. Load bundled manifest from `mock/manifest.json`.
2. Load bundled screen JSON files listed in the manifest.
3. If `remoteConfig.manifestUrl` is not empty, request that URL.
4. If the remote manifest has a higher `appVersion`, download changed screens
   from each screen's `url`.
5. Validate each downloaded screen and cache it in the app's local SQLite DB.

The app only performs `GET` requests right now. Cart, booking, checkout,
message sending, and program booking actions are currently local UI actions
that show Toast messages or update in-memory state. The Laravel API now has
write endpoints for orders, service bookings, and customer messages so the app
can be moved to real submissions without changing the admin data model. It also
accepts mobile telemetry events so the admin dashboard can show active phone
users, screen usage, button actions, and app error reports.

## Endpoints Needed First

### GET `/api/mobile/manifest`

Returns the server-driven app manifest.

Required top-level fields:

- `schemaVersion`: must be `1` for the current Android app.
- `appId`: expected value is `etokbike`.
- `appVersion`: integer. Must increase when any remote screen version changes.
- `remoteConfig.manifestUrl`: URL for this manifest endpoint.
- `remoteConfig.telemetryUrl`: URL for mobile app telemetry submissions.
- `theme`: app theme data.
- `navigation`: bottom navigation items.
- `screens`: object keyed by screen ID.

Each screen entry must include:

- `version`: integer screen version.
- `asset`: bundled fallback path. Keep this compatible with the Android assets.
- `url`: remote JSON endpoint for that screen.
- `checksum`: optional SHA-256 checksum of the exact response body.

Example:

```json
{
  "schemaVersion": 1,
  "appId": "etokbike",
  "appVersion": 9,
  "remoteConfig": {
    "manifestUrl": "http://127.0.0.1:8001/api/mobile/manifest",
    "telemetryUrl": "http://127.0.0.1:8001/api/mobile/telemetry"
  },
  "theme": {
    "brandName": "EtokBike",
    "locale": "fa-IR",
    "direction": "rtl",
    "colors": {
      "primary": "#D71920",
      "background": "#FFFFFF",
      "surface": "#F7F7F8",
      "text": "#101114",
      "muted": "#626368"
    }
  },
  "navigation": [
    { "id": "home", "label": "خانه", "screenId": "home" },
    { "id": "shop", "label": "فروشگاه", "screenId": "shop" },
    { "id": "services", "label": "خدمات", "screenId": "services" },
    { "id": "events", "label": "برنامه‌ها", "screenId": "events" },
    { "id": "account", "label": "حساب", "screenId": "account" }
  ],
  "screens": {
    "home": {
      "version": 5,
      "asset": "mock/screens/home.json",
      "url": "http://127.0.0.1:8001/api/mobile/screens/home",
      "checksum": ""
    }
  }
}
```

### POST `/api/mobile/telemetry`

Stores app activity and phone-side log events for admin analytics. The endpoint
does not require app authentication; the phone sends a stable anonymous
`device_id` and a per-launch `session_id`. The server also records request IP
address and user agent.

Send one event:

```json
{
  "device_id": "android-id-or-install-id",
  "session_id": "launch-session-id",
  "platform": "android",
  "app_version": "10",
  "event_name": "screen_view",
  "screen_id": "shop",
  "action": "bottom_navigation",
  "metadata": {
    "model": "Pixel 8"
  }
}
```

Or send a batch:

```json
{
  "device_id": "android-id-or-install-id",
  "session_id": "launch-session-id",
  "platform": "android",
  "app_version": "10",
  "events": [
    { "event_name": "app_open", "screen_id": "home" },
    { "event_name": "screen_view", "screen_id": "shop", "action": "bottom_navigation" },
    { "event_name": "error", "screen_id": "home", "action": "config_update", "metadata": { "message": "HTTP 500" } }
  ]
}
```

Response:

```json
{
  "data": {
    "accepted": 3
  }
}
```

Recommended event names:

- `app_open`
- `heartbeat`
- `screen_view`
- `action`
- `config_update`
- `error`

### GET `/api/mobile/screens/{screen}`

Returns a single server-driven screen JSON payload.

Supported screen IDs from the current Android app:

- `home`
- `shop`
- `services`
- `events`
- `account`
- `messages`
- `cart`

Required top-level fields:

- `schemaVersion`: must be `1`.
- `screenId`: must match `{screen}`.
- `version`: integer screen version.
- `title`: screen title.
- `sections`: array of section objects.

Example:

```json
{
  "schemaVersion": 1,
  "screenId": "shop",
  "version": 3,
  "title": "فروشگاه",
  "sections": []
}
```

## Current Screen Sections

The Android renderer recognizes these section types:

- `hero`
- `category_grid`
- `product_row`
- `offer_sections`
- `program_sections`
- `product_list`
- `service_list`
- `schedule_list`
- `activity_list`
- `client_details`
- `purchase_history`
- `ongoing_purchase`
- `message_center`
- `cart_summary`
- `service_booking_form`
- `status_tracker`
- `bike_profile_list`
- `business_info`
- `checkout_note`
- `profile_summary`

Current mock screen composition:

- `home`: `hero`, `category_grid`, `product_row`, `business_info`,
  `business_info`, `status_tracker`, `offer_sections`, `business_info`
- `shop`: `hero`, `category_grid`, `product_list`, `business_info`
- `services`: `hero`, `offer_sections`, `service_booking_form`,
  `status_tracker`, `business_info`
- `events`: `hero`, `category_grid`, `program_sections`, `business_info`
- `account`: `hero`, `profile_summary`, `ongoing_purchase`,
  `bike_profile_list`, `client_details`, `purchase_history`
- `messages`: `hero`, `message_center`
- `cart`: `hero`, `cart_summary`, `business_info`

## Data Areas To Model In Laravel

The admin panel has an `App pages` resource backed by `mobile_screens` and
`mobile_screen_sections`. It seeds from `resources/mobile/screens/*.json` and
lets admins edit page title, title visibility, section order, section
visibility, and each section's `data`, `layout`, and `style` JSON.

Screen-specific builders still inject domain data into known sections. For
example, the `products` section on `shop` is populated from products and
categories, while the `shop-hero`, `shop-shortcuts`, and `shop-benefits`
sections are edited through `App pages`.

Current backend resources:

- Products
- Product categories
- Product filters and availability via screen JSON plus product availability
- Service categories and offerings
- Service booking submissions
- Programs/events
- Program galleries
- Customer messages and message departments
- Orders and order items

Mostly edited through `App pages`:

- Store/business info blocks
- Customer profile data beyond latest order/service contact fields
- Customer bikes beyond service booking bike labels
- Repair/service status trackers
- Cart and checkout summary

## Write API Endpoints

These are available in Laravel. The current Android app does not call them yet.

- `POST /api/orders`
- `POST /api/service-bookings`
- `POST /api/messages`
- `POST /api/mobile/telemetry`

## Future API Endpoints

These are not available yet, but will be needed when the app moves from
mock/local state to authenticated server state.

Authentication and user:

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/user`

Shop and products:

- `GET /api/products`
- `GET /api/products/{id}`

Cart:

- `GET /api/cart`
- `POST /api/cart/items`
- `PATCH /api/cart/items/{id}`
- `DELETE /api/cart/items/{id}`

Programs:

- `POST /api/program-bookings`

Messages:

- `GET /api/messages`

Account:

- `GET /api/account`
- `GET /api/account/bikes`
- `GET /api/account/orders`
- `GET /api/account/repairs`

## Implementation Notes

- Keep `schemaVersion` at `1` until the Android renderer supports another
  schema.
- Increase `appVersion` when the manifest changes or when any screen version
  changes.
- Increase an individual screen `version` when that screen's JSON changes.
- The Android app validates that each screen response has a matching `screenId`.
- The app sends `Accept: application/json`.
- The app has 5 second connect and read timeouts.
- If `checksum` is provided, it must be the SHA-256 hash of the exact JSON
  response body downloaded by Android.
- For local emulator testing, Android cannot use `127.0.0.1` to reach the
  host machine. Use `10.0.2.2` for the Android emulator or the machine's LAN IP
  for a physical device.
