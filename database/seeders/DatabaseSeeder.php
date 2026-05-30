<?php

namespace Database\Seeders;

use App\Models\MessageDepartment;
use App\Models\MobileScreen;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ServiceBooking;
use App\Models\ServiceCategory;
use App\Models\ServiceOffering;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
            ],
        );

        $admin->assignRole($adminRole);

        $order = Order::query()->firstOrCreate(
            ['order_number' => 'ETB-DEMO-001'],
            [
                'customer_name' => 'Ali Rezaei',
                'customer_email' => 'ali@example.com',
                'customer_phone' => '+989120000000',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'fulfillment_method' => 'pickup',
                'customer_notes' => 'Demo order from the mobile checkout flow.',
                'placed_at' => now(),
            ],
        );

        if ($order->items()->doesntExist()) {
            $order->items()->createMany([
                [
                    'product_id' => 'bike-etx-200',
                    'title' => 'دوچرخه کوهستان ETX 200',
                    'quantity' => 1,
                    'unit_price' => 28500000,
                ],
                [
                    'product_id' => 'helmet-air',
                    'title' => 'کلاه ایمنی AirFit',
                    'quantity' => 1,
                    'unit_price' => 1400000,
                ],
            ]);
        }

        $this->seedPrograms();
        $this->seedProducts();
        $this->seedServices();
        $this->seedMessages();
        $this->seedMobileScreens();
    }

    private function seedPrograms(): void
    {
        $path = resource_path('mobile/screens/events.json');

        if (! file_exists($path)) {
            return;
        }

        $screen = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $subsections = $screen['sections'][0]['data']['subsections'] ?? [];

        foreach ($subsections as $categoryIndex => $subsection) {
            $category = ProgramCategory::query()->firstOrCreate(
                ['slug' => $subsection['id']],
                [
                    'label' => $subsection['label'],
                    'title' => $subsection['title'],
                    'sort_order' => $categoryIndex,
                    'is_active' => true,
                ],
            );

            foreach (($subsection['items'] ?? []) as $programIndex => $item) {
                $program = Program::query()->firstOrCreate(
                    ['slug' => $item['id']],
                    [
                        'program_category_id' => $category->id,
                        'title' => $item['title'],
                        'subtitle' => $item['subtitle'],
                        'date_value' => $item['dateValue'],
                        'date_label' => $item['dateLabel'],
                        'program_state' => $item['programState'],
                        'status_label' => $item['statusLabel'] ?? null,
                        'book_label' => $item['bookLabel'] ?? null,
                        'view_label' => $item['viewLabel'] ?? null,
                        'ad_title' => $item['adTitle'] ?? null,
                        'advertisement' => $item['advertisement'] ?? null,
                        'details' => $item['details'] ?? [],
                        'gallery_title' => $item['galleryTitle'] ?? null,
                        'thumbnail_text' => $item['thumbnailText'] ?? 'ETOK',
                        'thumbnail_color' => $item['thumbnailColor'] ?? '#101114',
                        'image_url' => $item['imageUrl'] ?? null,
                        'sort_order' => $programIndex,
                        'is_active' => true,
                    ],
                );

                if ($program->galleryItems()->exists()) {
                    continue;
                }

                foreach (($item['gallery'] ?? []) as $galleryIndex => $galleryItem) {
                    $program->galleryItems()->create([
                        'thumbnail_text' => $galleryItem['thumbnailText'] ?? 'PHOTO',
                        'thumbnail_color' => $galleryItem['thumbnailColor'] ?? '#101114',
                        'caption' => $galleryItem['caption'] ?? null,
                        'image_url' => $galleryItem['imageUrl'] ?? null,
                        'sort_order' => $galleryIndex,
                    ]);
                }
            }
        }
    }

    private function seedProducts(): void
    {
        $path = resource_path('mobile/screens/shop.json');

        if (! file_exists($path)) {
            return;
        }

        $screen = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $productSection = collect($screen['sections'] ?? [])
            ->firstWhere('type', 'product_list');

        if (! $productSection) {
            return;
        }

        foreach (($productSection['data']['categories'] ?? []) as $categoryIndex => $item) {
            ProductCategory::query()->firstOrCreate(
                ['slug' => $item['id']],
                [
                    'label' => $item['label'],
                    'sort_order' => $categoryIndex,
                    'is_active' => true,
                ],
            );
        }

        foreach (($productSection['data']['items'] ?? []) as $productIndex => $item) {
            $category = ProductCategory::query()->where('slug', $item['category'])->first();

            if (! $category) {
                continue;
            }

            Product::query()->firstOrCreate(
                ['slug' => $item['id']],
                [
                    'product_category_id' => $category->id,
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'description' => $item['description'] ?? null,
                    'availability' => $item['availability'] ?? 'in_stock',
                    'price_value' => $item['priceValue'] ?? 0,
                    'price_label' => $item['price'] ?? null,
                    'stock_label' => $item['stockLabel'] ?? null,
                    'thumbnail_text' => $item['thumbnailText'] ?? 'ETOK',
                    'thumbnail_color' => $item['thumbnailColor'] ?? '#101114',
                    'image_url' => $item['imageUrl'] ?? null,
                    'sort_order' => $productIndex,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedServices(): void
    {
        $path = resource_path('mobile/screens/services.json');

        if (! file_exists($path)) {
            return;
        }

        $screen = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $serviceSection = collect($screen['sections'] ?? [])->firstWhere('id', 'service-booking');

        foreach (($serviceSection['data']['subsections'] ?? []) as $categoryIndex => $subsection) {
            $category = ServiceCategory::query()->firstOrCreate(
                ['slug' => $subsection['id']],
                [
                    'label' => $subsection['label'],
                    'title' => $subsection['title'],
                    'sort_order' => $categoryIndex,
                    'is_active' => true,
                ],
            );

            foreach (($subsection['items'] ?? []) as $offeringIndex => $item) {
                ServiceOffering::query()->firstOrCreate(
                    ['slug' => str($subsection['id'].'-'.$item['thumbnailText'])->slug()->toString()],
                    [
                        'service_category_id' => $category->id,
                        'title' => $item['title'],
                        'subtitle' => $item['subtitle'],
                        'description' => $item['description'] ?? null,
                        'price_label' => $item['price'] ?? null,
                        'thumbnail_text' => $item['thumbnailText'] ?? 'ETOK',
                        'thumbnail_color' => $item['thumbnailColor'] ?? '#101114',
                        'image_url' => $item['imageUrl'] ?? null,
                        'sort_order' => $offeringIndex,
                        'is_active' => true,
                    ],
                );
            }
        }

        ServiceBooking::query()->firstOrCreate(
            ['customer_name' => 'Ali Rezaei', 'service_type' => 'سرویس کامل'],
            [
                'customer_phone' => '+989121234567',
                'bike_label' => 'ETX 200',
                'preferred_time' => 'فردا ۱۰:۳۰',
                'problem_description' => 'Demo service booking from the mobile services screen.',
                'status' => 'pending',
            ],
        );
    }

    private function seedMessages(): void
    {
        $path = resource_path('mobile/screens/messages.json');

        if (! file_exists($path)) {
            return;
        }

        $screen = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $messageSection = collect($screen['sections'] ?? [])->firstWhere('type', 'message_center');

        foreach (($messageSection['data']['departments'] ?? []) as $departmentIndex => $item) {
            $department = MessageDepartment::query()->firstOrCreate(
                ['slug' => $item['id']],
                [
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'] ?? null,
                    'thread_title' => $item['threadTitle'],
                    'composer_title' => $item['composerTitle'],
                    'placeholder' => $item['placeholder'] ?? null,
                    'send_label' => $item['sendLabel'] ?? 'ارسال پیام',
                    'sort_order' => $departmentIndex,
                    'is_active' => true,
                ],
            );

            if ($department->messages()->exists()) {
                continue;
            }

            foreach (($item['messages'] ?? []) as $message) {
                $department->messages()->create([
                    'sender' => $message['sender'] ?? 'department',
                    'label' => $message['label'],
                    'text' => $message['text'],
                    'time_label' => $message['time'] ?? null,
                    'is_unread' => ($item['unreadLabel'] ?? '') !== '' && ($message['sender'] ?? '') === 'department',
                ]);
            }
        }
    }

    private function seedMobileScreens(): void
    {
        foreach (glob(resource_path('mobile/screens/*.json')) ?: [] as $path) {
            $screen = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            $screenId = $screen['screenId'] ?? null;

            if (! is_string($screenId) || blank($screenId)) {
                continue;
            }

            $mobileScreen = MobileScreen::query()->firstOrCreate(
                ['screen_id' => $screenId],
                [
                    'title' => $screen['title'] ?? $screenId,
                    'version' => $screen['version'] ?? 1,
                    'hide_title' => (bool) ($screen['hideTitle'] ?? false),
                    'is_active' => true,
                ],
            );

            foreach (($screen['sections'] ?? []) as $sectionIndex => $section) {
                if (! is_array($section) || blank($section['id'] ?? null)) {
                    continue;
                }

                $mobileScreen->sections()->firstOrCreate(
                    ['section_id' => $section['id']],
                    [
                        'type' => $section['type'] ?? 'business_info',
                        'data' => $section['data'] ?? [],
                        'layout' => $section['layout'] ?? [],
                        'style' => $section['style'] ?? [],
                        'sort_order' => $sectionIndex,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
