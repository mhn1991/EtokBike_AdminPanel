# Admin Panel UI/UX Audit

Date: 2026-07-20

Status: **All three findings below were fixed on 2026-08-02.** Summary:

- **Finding 1 (translation sweep):** Turned out smaller than it looked —
  `app/Providers/Filament/AppServiceProvider.php` already force-enables
  `translateLabel()` on every Filament component globally, so `->label(...)`
  strings were already auto-translated via `lang/fa.json`; the only real gaps
  were (a) ~108 `Section::make('English')` calls across ~51 files that don't
  auto-translate their heading (Filament's `Section` uses a separate
  `HasHeading` concern with no translation hook) — fixed by a one-off script
  wrapping them all in `__()`, and (b) ~64 missing English→Persian entries in
  `lang/fa.json` (labels, section headings, and `->description()`/
  `->helperText()` strings), which were added. Verified with a full grep
  sweep (`->label()`, `Section::make()`, and every `__()` call) confirming
  zero untranslated strings remain in `app/Filament`.
- **Finding 2 (sidebar):** Fixed — removed
  `->collapsibleNavigationGroups(false)` from `AdminPanelProvider.php` (one
  line), restoring Filament's default collapsible groups.
- **Finding 3 (product table density):** Fixed in
  `app/Filament/Resources/Products/Tables/ProductsTable.php` — added a small
  `ImageColumn` thumbnail as the first column, and trimmed the title column's
  description to just the subtitle (dropped price/stock, which already have
  their own dedicated columns and were making rows tall for no new
  information). ~12 rows now visible per screen instead of ~4-5.

No regressions: `php artisan test` shows the same single pre-existing
failure (`MobileConfigApiTest`, unrelated) as on a clean checkout.

## Bundling / offers feature (built 2026-08-02)

Separate from this audit: a new product-bundles/offers feature was built —
`App\Models\ProductBundle` / `ProductBundleItem` / `ProductOffer`, resources
`ProductBundleResource` and `ProductOfferResource` (nav group بازاریابی), and
an "Offers" repeater section added to `ProductForm.php` so offers can be
created directly on both the add-product and edit-product pages. See git
history for the migrations/models/resources. Cart/checkout pricing
integration was explicitly left out of scope.

## Full panel audit (2026-08-02)

Following up on the original request ("make it easier for admins to work in
the panel"), all 37 Filament resources were reviewed at the code level
(every `Table` and `Resource` class), plus live checks of the dashboard,
global search, and dark mode. Findings below, roughly in priority order.

### Finding 4 (systemic, highest impact): almost no table is paginated

`grep -rl "paginated(false)" app/Filament` hits **37 of 39** table
definitions — effectively every list in the panel loads its *entire* table
in one request with no paging. This isn't hypothetical: `MobileAnalyticsEvent`
already has **1,030 rows** in the dev database and its table
(`app/Filament/Resources/MobileAnalyticsEvents/Tables/MobileAnalyticsEventsTable.php`)
still renders all of them unpaginated on every visit. `AdminActivityLog` and
`NotificationLog` are pure event logs that only grow over time with no
built-in pruning, so they'll hit the same wall. `Order`, `CustomerMessage`,
`StockMovement` will too, just on a slower clock, as the store gets more
real usage.

**Fix:** for the append-only/log-style resources especially
(`MobileAnalyticsEvents`, `AdminActivityLogs`, `NotificationLogs`,
`StockMovements`), drop `->paginated(false)` (Filament's default pagination
is fine) or set an explicit page size. Worth doing panel-wide over time, but
those four are the ones already carrying real or fast-growing row counts.

### Finding 5 (systemic): no bulk actions anywhere except Products

Only `ProductsTable` has a real `BulkActionGroup` (show/hide, feature,
unfeature in bulk). All other 38 resources have `->toolbarActions([])` or no
bulk actions at all. For an admin processing a queue, this means every
status change is one record at a time. Highest-value candidates, based on
what the row-level quick actions already suggest they need:

- **Orders** (`OrdersTable.php`) — already has excellent per-row actions
  (assign to me, mark paid, confirm, mark ready, complete) but no bulk
  equivalent for processing several similar orders at once.
- **CustomerMessages** — bulk "mark replied" for triaging a backlog.
- **ReturnRequests / ServiceBookings / ProgramBookings** — bulk
  confirm/approve.
- **NotificationLogs** — bulk delete for cleanup, given Finding 4.

### Finding 6: dashboard trend chart has a real axis bug, not just sparse data

The prior audit flagged the "عملیات روزانه" trend chart
(`app/Filament/Widgets/OperationsTrendChart.php`) as a flat line and
attributed it to sparse demo data. Re-checked live: it's *also* flat because
there's genuinely little historical data yet (expected for a new store), but
the y-axis independently has a real bug — it renders `-1.0, -0.6, -0.2, 0.2,
0.6, 1.0`, i.e. symmetric negative padding around a non-negative count
metric (orders/bookings/messages can't be negative). The chart's y-axis
min should be pinned to 0.

### Finding 7: purchase-order line items require re-typing the product name

`app/Filament/Resources/PurchaseOrders/RelationManagers/ItemsRelationManager.php`
— when adding a line item, `product_id` is a searchable product `Select`,
but `description` is a separate *required* free-text field right next to it
with no auto-fill. An admin building a 10-line purchase order has to
manually retype every product name after already picking it from the
dropdown. Auto-filling `description` from the selected product's title
(editable after, for cases where they want a different label) would remove
pure friction on a form procurement staff use often. (Everything else here
is well done — `line_total` and the parent PO's `subtotal`/`total`
auto-recalculate correctly via `PurchaseOrderItem`'s model events.)

### Finding 8: customer-message inbox sorts by recency only

`CustomerMessagesTable.php` defaults to `->defaultSort('created_at', 'desc')`
and has a "Needs response" filter, but doesn't sort unread/unresolved
messages to the top by default. A support inbox worked newest-first can bury
an unanswered message from days ago under newer already-handled ones.
Sorting by `is_unread` (desc) then `created_at` would surface what actually
needs attention first.

### Finding 9 (minor, cosmetic): one raw Persian label bypasses the translation convention

`app/Filament/Resources/PurchaseOrders/PurchaseOrderResource.php:97` has
`TextInput::make('shipping_total')->label('ارسال')` — every other field in
the panel passes an **English** string to `->label()` and lets the global
`translateLabel()` hook (`AppServiceProvider.php:53`) resolve it via
`lang/fa.json`. This one happens to render correctly by coincidence (`__()`
falls back to returning an unknown key verbatim), but it's inconsistent with
the convention and would silently break if `lang/fa.json` ever gained a key
literally named `ارسال`. Should be `->label('Shipping')` with a
`lang/fa.json` entry, matching every other field.

### Confirmed working well (checked live, no action needed)

- **Global search** — works correctly, returns grouped, relevant, Persian
  results (tested searching "دوچرخه", got matching products and categories).
  The earlier audit had this as "not tested."
- **Dark mode toggle** — works cleanly, badges/colors stay legible. Earlier
  audit had this as "not visually confirmed."
- **Orders table row actions** — genuinely well designed for the daily
  fulfilment workflow (assign, mark paid, confirm, mark ready, complete,
  generate receipt/invoice, call customer all in one dropdown).
- **PurchaseOrder totals** — subtotal/total correctly auto-recalculate from
  line items via model events, no manual math needed.

### Not yet checked

- Mobile/narrow-viewport responsiveness (still only checked at desktop
  width).
- Full accessibility pass (contrast, focus states, aria labels).
- The other ~30 resources not named above were reviewed for structural
  red flags (pagination, bulk actions, column count) via the metrics script
  below, but not individually walked through for form/workflow quality the
  way Orders/CustomerMessages/PurchaseOrders were.

Regenerate the structural metrics table with:

```bash
python3 -c "
import re, glob, os
resource_dirs = sorted(d for d in os.listdir('app/Filament/Resources') if os.path.isdir(f'app/Filament/Resources/{d}'))
for d in resource_dirs:
    files = glob.glob(f'app/Filament/Resources/{d}/Tables/*.php') or glob.glob(f'app/Filament/Resources/{d}/*Resource.php')
    for tf in files:
        content = open(tf, encoding='utf-8').read()
        if '(Table \$table): Table' not in content: continue
        print(d, 'paginated(false):', 'paginated(false)' in content, 'bulk:', 'toolbarActions([' in content and 'toolbarActions([])' not in content)
"
```

Original audit notes below, kept for context.

## How this was produced

Logged into the running panel (Filament v5, `app/Providers/Filament/AdminPanelProvider.php`)
at `/admin` as `admin@example.com` / `password` (seeded in
`database/seeders/DatabaseSeeder.php:44`, local sqlite DB only) and clicked
through the dashboard, product list, product create form, and an order view
page, then cross-checked what was seen against the resource source files.

## Finding 1 (highest impact): English/Persian mix throughout the panel

The panel is Persian/RTL (`brandName('مدیریت EtokBike')`, Persian nav groups,
Persian field labels in the parts that were translated), but a large fraction
of secondary UI text was left in English. This is visibly broken in the app —
e.g. the order view page (`/admin/orders/1`) shows section headings
**"Customer" / "Order" / "Admin" / "Totals"** in plain English while
everything around them is Persian.

This is systemic, not a one-off. A grep across `app/Filament` found:

- **~69 `Section::make('English Title')` calls** (form and infolist section
  headings) — see `Order`, `Customer`, `Admin`, `Totals`, `Bike`, `Category`,
  `Program`, `Audit`, etc. Full list was extracted to
  `/tmp/.../scratchpad/sections.txt` during the audit (not committed —
  regenerate with the command below).
- **~182 unique `->label('English')` strings** (~382 occurrences with
  repeats) on form fields, table columns, and action buttons. Common repeats:
  "Visible in app" (21x), "Order" (14x), "Product" (13x), "Category" (13x),
  "Visible" (11x), "Active" (9x), "SKU" (8x), "Actions" (8x), "User" (7x),
  "Customer" (7x).
- Widgets are affected too: `app/Filament/Widgets/RecentOrdersWidget.php`,
  `UnreadMessagesWidget.php`, `ServiceQueueWidget.php` all have English column
  labels ("Order", "Customer", "Department", "Message", "Time", "Actions",
  "Assign to me", "Confirm", "Start work", "Complete", ...).

Regenerate the exact current lists with:

```bash
grep -rhoP -- "->label\('\K[A-Za-z][a-zA-Z0-9 ,/&:()'.-]*(?='\))" app/Filament | sort -u
grep -rhoP -- "Section::make\('\K[A-Za-z][a-zA-Z0-9 ,/&:()'.-]*(?='\))" app/Filament | sort -u
```

Also worth checking once the main sweep is done (not yet checked in detail):
`Tabs\Tab::make(...)`, `Fieldset::make(...)`, `->heading(...)`,
`->description(...)`, `->placeholder(...)`, `->helperText(...)`,
notification `->title()`/`->body()` calls in actions, and enum/status label
methods (e.g. wherever order/payment status badges get their text).

### Recommended approach for the fix

Do **not** hand-edit 100 files one string at a time — most of the ~250 unique
strings repeat across files (domain vocabulary: Order, Product, Customer,
Category, Visible, Active, SKU, ...). Build one Persian translation
dictionary (English string → Persian string) covering every value in
`labels.txt` / `sections.txt`, spot-check ambiguous ones in context (e.g.
"Time" could be زمان/ساعت depending on field; "Style" and "Type" need to be
read in context, not guessed), then apply it with a small script (Python or
`sed`) that only rewrites inside `->label('...')`, `Section::make('...')`,
`->heading('...')`, `->description('...')`, `->placeholder('...')` calls — not
a blind global find/replace — to avoid touching PHP identifiers, comments, or
unrelated strings. Verify afterward with the same grep commands above (should
return nothing left in English) and a `php artisan test` / manual click
through of Orders, Products, Customers to confirm nothing broke.

## Finding 2: Sidebar is permanently fully expanded

`app/Providers/Filament/AdminPanelProvider.php:35` sets
`->collapsibleNavigationGroups(false)`. There are 17 nav groups (پیام‌ها, SEO,
انبار, خرید, مالی, سفارش‌ها, ارسال, بازاریابی, اعلان‌ها, گزارش‌ها, ممیزی,
کاتالوگ, خدمات, برنامه‌ها, محتوای اپ موبایل, مشتریان, تنظیمات) each with
several resources, and none of them can be collapsed. Scrolling through the
live sidebar confirmed it runs several screen-heights long with everything
always open, so reaching e.g. "تنظیمات" (Settings) at the bottom requires a
lot of scrolling every time, regardless of which section the admin actually
works in.

**Fix:** change `->collapsibleNavigationGroups(false)` to `true` (or just
delete the call — `true` is Filament's default). One-line change, very low
risk, should ship regardless of what happens with Finding 1. Optionally pair
with `->navigationGroups([...])->collapsed()` on the groups an admin is least
likely to need daily (SEO, ممیزی/Audit, محتوای اپ موبایل) so the sidebar opens
in a more scannable state by default.

## Finding 3: Product list table is low-density

`/admin/products` (`app/Filament/Resources/Products/Tables/ProductsTable.php`)
stacks title + description snippet + price info inside a single "عنوان"
column, making each row tall (~90-110px). On a normal viewport this means
only ~4-5 products are visible without scrolling, which hurts scanning a
catalog of any real size. There's also no product thumbnail in the list —
just a small color swatch for the primary variant — making visual
identification harder than it needs to be.

**Fix ideas (not yet designed in detail):**
- Move the description out of the default table view (keep it in the row's
  expandable/description area or drop it — it's already visible on the
  product's own page).
- Add a small `ImageColumn` thumbnail before the title.
- Reconsider which of the current columns (ترتیب نمایش، کارت، برچسب موجودی،
  مکان، موجودی فعلی، قیمت، نوع‌ها، وضعیت دسترسی، عنوان، دسته) are essential
  for the default view vs. toggleable columns (Filament tables support
  `->toggleable()`).

## Not yet investigated

- Mobile/narrow-viewport responsiveness (only checked at ~1600px wide).
- Dark mode toggle exists in the DOM (`ref_12`/`ref_13`/`ref_14`, "حالت روشن
  / حالت تیره / حالت سیستم") but wasn't visually confirmed to render/work
  correctly from the user menu — worth a quick click-through.
- Dashboard charts (`OperationsTrendChart`, `OrdersByStatusChart`,
  `MobileActivityTrendChart`) rendered as a flat line / single-color donut in
  the demo data — unclear if that's a data issue (empty/sparse seed data) or
  a charting config issue. Needs checking with more realistic seed data
  before concluding anything is wrong with the chart code.
- Global search (top-left search box in the sidebar) — not tested.
- Full accessibility pass (contrast, focus states, aria labels) — not done.

## Open decisions for next session

Asked the user to scope this; answer was "stop for now, write a report, we'll
resume from it" before the scope questions were fully settled. Re-ask when
resuming:

1. **Translation sweep scope:** full sweep of all ~100 files, or high-traffic
   resources first (Orders, Products, Customers, dashboard widgets, main
   nav) with the rest as a follow-up?
2. **Which of these to also do:** collapsible sidebar groups (low
   risk/effort, recommended regardless), denser product table, product
   thumbnails in the list.

## Environment notes for resuming

- Local dev server: `php artisan serve --port=8001`, DB is sqlite at
  `database/database.sqlite`, seeded admin login is
  `admin@example.com` / `password`.
- Assets are prebuilt (`public/build/manifest.json` exists) — `npm run dev`
  only needed if editing Blade/CSS/JS, not for PHP-only Filament label
  changes.
