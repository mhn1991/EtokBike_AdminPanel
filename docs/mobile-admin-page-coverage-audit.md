# Mobile Admin Page Coverage Audit

Date: 2026-06-07

This audit compares the Android app screens against the Laravel/Filament admin
panel. The Android app is currently server-driven for screen content, but many
button actions are still sample/local UI actions.

## Implementation Update

After this audit, the main gaps were implemented in the admin/backend and the
Android app:

- Products, service offerings, programs, and program galleries now support
  admin image upload and Android `imageUrl` rendering.
- Android now submits cart add, checkout, service booking, message send, and
  program booking actions to backend endpoints when remote config URLs are
  available.
- Program bookings, mobile cart items, service time slots, delivery methods,
  store profile, customer profiles, and bike profiles now have backend models
  and admin/API support where needed.
- Mobile state now exposes cart and unread message counts for Android badges.
- Basic API auth endpoints now exist for future login UI integration.

Remaining work is narrower: a proper Android login/profile UI, fully
user-scoped mobile conversations/cart/account data, and richer structured
editors for arbitrary `App pages` sections beyond the dedicated admin resources
added here.

## Screens Checked

The Android app and mobile API currently support these screens:

- `home`
- `shop`
- `services`
- `events`
- `account`
- `messages`
- `cart`

The renderer supports these section types:

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

## Cross-Cutting Findings

### 1. Image handling is incomplete

Admin models already have `image_url` fields for products, service offerings,
programs, and program gallery items. Filament forms expose those fields as URL
text inputs only.

Missing:

- Admin file upload widgets for product, service, program, and gallery images.
- Storage handling that saves uploaded files to a public disk and stores the
  public URL/path.
- Android rendering of `imageUrl`. The Android app currently ignores `imageUrl`
  and renders text/color thumbnails instead.

Impact: adding a product photo in the admin panel is not possible today, and
even manually entering an image URL will not show the photo in the Android app.

### 2. Android write actions are not wired

The backend already has write endpoints for orders, service bookings, customer
messages, and telemetry. The Android app still handles these actions locally:

- Add to cart increments an in-memory counter.
- Checkout shows a toast.
- Service booking submit shows a toast.
- Message send clears the input and shows a toast.
- Program booking shows a toast.

Missing:

- Android calls to `POST /api/orders`.
- Android calls to `POST /api/service-bookings`.
- Android calls to `POST /api/messages`.
- A backend endpoint/admin resource for program bookings.

### 3. `App pages` works, but is too raw for normal admins

The admin panel has an `App pages` resource backed by `mobile_screens` and
`mobile_screen_sections`. It can update page titles, visibility, section order,
section visibility, and raw JSON section payloads.

This is technically enough for heroes, shortcut cards, business info blocks,
delivery options, static form copy, and policy blocks. It is not a comfortable
workflow for most admins because the fields are edited as JSON.

Missing:

- Structured repeaters/forms for common section payloads such as hero stats,
  shortcut cards, business info cards, delivery options, and policy cards.
- Safer validation per section type.

### 4. Account data is not truly customer-specific

The account screen is currently built from the latest order and/or latest
service booking. There is no real mobile authentication flow or per-customer
account model in the Android app.

Missing:

- Mobile login/session support.
- Customer profile/address management.
- Customer bike profiles as first-class records.
- Per-customer orders, repairs, messages, and purchase history.

### 5. Top-bar state is not admin/API driven

The Android top bar has a hardcoded message badge and an in-memory cart count.
Those values are not driven by the mobile API or admin data.

Missing:

- Dynamic unread message count.
- Dynamic cart count from a persisted cart.

## Page-by-Page Coverage

### Home

Sections:

- `home-hero` (`hero`)
- `quick-actions` (`category_grid`)
- `featured-bikes` (`product_row`)
- `service-promise` (`business_info`)
- `store-status` (`business_info`)
- `customer-status` (`status_tracker`)
- `weekly-programs` (`offer_sections`)
- `store-info` (`business_info`)

Admin coverage:

- Featured bikes are covered by `Products` and `Product categories`.
- Weekly programs are covered by `Program categories` and `Programs`.
- Customer status is covered by active `Orders` and `Service bookings`.
- Hero, quick actions, service promise, store status, and store info are only
  editable through raw `App pages` JSON.

Gaps:

- No structured admin form for homepage hero, quick actions, store status, or
  store info.
- Product and program images cannot be uploaded and are not rendered.
- Customer status is not per authenticated user.

### Shop

Sections:

- `shop-hero` (`hero`)
- `shop-shortcuts` (`category_grid`)
- `products` (`product_list`)
- `shop-benefits` (`business_info`)

Admin coverage:

- Product categories, product details, availability, price, stock label,
  featured flag, sort order, and app thumbnail text/color are covered.
- Product filters and labels are editable only through `App pages` JSON.
- Shop hero, shortcuts, and benefits are editable only through `App pages` JSON.

Gaps:

- No product photo upload; only an `image_url` text input exists.
- Android ignores `imageUrl`.
- Add-to-cart is local/in-memory and does not create a real cart item.
- Checkout is not connected from the app to `POST /api/orders`.
- There is no product detail page or product detail admin surface beyond the
  fields already shown in list cards.

### Services

Sections:

- `services-hero` (`hero`)
- `service-booking` (`offer_sections`)
- `booking-form` (`service_booking_form`)
- `repair-status` (`status_tracker`)
- `service-notes` (`business_info`)

Admin coverage:

- Service categories and service offerings are covered.
- Service booking submissions and status updates are covered once records exist.
- Repair status is built from active service bookings.
- Booking form text, time slots, and service notes are editable only through
  `App pages` JSON.

Gaps:

- Android does not submit the service booking form to the existing API.
- No structured admin resource for service time slots/availability.
- No service image upload and no Android image rendering.
- Bike options are inferred from prior bookings instead of real customer bike
  records.

### Events

Sections:

- `events-hero` (`hero`)
- `program-guide` (`category_grid`)
- `events-list` (`program_sections`)
- `event-policy` (`business_info`)

Admin coverage:

- Program categories are covered.
- Programs are covered with title, subtitle, date, status, labels, detail copy,
  capacity fields, and active/sort status.
- Finished program galleries are covered as relation records.
- Hero, guide cards, and policy cards are editable only through `App pages`
  JSON.

Gaps:

- No program image/gallery upload; only URL text inputs exist.
- Android ignores program and gallery `imageUrl`.
- Program booking is a toast only.
- No program booking endpoint/resource.
- Capacity and reserved counts exist on `Program` but are not currently used to
  create reservations or update remaining capacity.

### Account

Sections:

- `account-hero` (`hero`)
- `profile-summary` (`profile_summary`)
- `ongoing-purchases` (`ongoing_purchase`)
- `bike-profiles` (`bike_profile_list`)
- `client-details` (`client_details`)
- `purchase-history` (`purchase_history`)

Admin coverage:

- Ongoing purchases and purchase history can be derived from `Orders` and
  `Service bookings`.
- Contact details are derived from the latest order or booking.
- Bike profiles are inferred from service booking bike labels.
- Account hero/profile copy is editable only through `App pages` JSON.

Gaps:

- No authenticated mobile account.
- No dedicated customer profile, delivery address, or bike profile admin model.
- Account screen data can show the latest global customer data, not the current
  mobile user.
- No user-scoped order/service/message history API.

### Messages

Sections:

- `messages-hero` (`hero`)
- `message-center` (`message_center`)

Admin coverage:

- Message departments are covered.
- Customer messages are covered.
- Unread labels and message summaries are built from message records.
- Messages hero is editable only through `App pages` JSON.

Gaps:

- Android message composer does not call `POST /api/messages`.
- No per-user conversation scope because mobile authentication is missing.
- Top-bar unread badge is hardcoded in Android.

### Cart

Sections:

- `cart-hero` (`hero`)
- `cart-summary` (`cart_summary`)
- `delivery-options` (`business_info`)

Admin coverage:

- Cart summary is currently built from featured products as sample cart data.
- Delivery options are editable only through `App pages` JSON.
- Orders can be managed in the admin once an order exists.

Gaps:

- No persistent mobile cart.
- No add/update/remove cart item API.
- Android checkout does not call `POST /api/orders`.
- Cart quantities are not real.
- Delivery options and checkout notes do not have structured admin resources.

## Recommended Implementation Order

1. Add end-to-end image support:
   - Replace `image_url` text inputs with Filament upload support or add upload
     fields alongside URL fields.
   - Store files on the public disk.
   - Update Android to render `imageUrl` for products, services, programs, and
     gallery items.

2. Wire Android submit actions:
   - Service booking form to `POST /api/service-bookings`.
   - Message composer to `POST /api/messages`.
   - Checkout to `POST /api/orders`.
   - Add a program booking endpoint/resource and wire program booking.

3. Make common page sections admin-friendly:
   - Hero editor.
   - Shortcut/category card editor.
   - Business info card editor.
   - Delivery option editor.
   - Static labels for filters/forms.

4. Add customer/account foundations:
   - Mobile auth.
   - Customer profiles and addresses.
   - Bike profiles.
   - Per-customer orders, repairs, cart, and messages.

5. Add operational settings:
   - Store hours/status.
   - Service time slots.
   - Delivery methods.
   - Dynamic top-bar unread/cart counts.

## Current Status Summary

The admin panel can update a large part of what the app displays through domain
resources and raw `App pages` JSON. The biggest missing admin capability is
image upload, and the biggest missing app capability is that commerce, booking,
messages, and program reservations are not yet connected to the backend from
Android.
