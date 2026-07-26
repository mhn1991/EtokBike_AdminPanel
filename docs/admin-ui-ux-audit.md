# Admin Panel UI/UX Audit

Date: 2026-07-20

Status: **Findings recorded, no code changes made yet.** Pick this back up by
reading this file, confirming scope with the user (see "Open decisions"
below), then executing the plan.

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
