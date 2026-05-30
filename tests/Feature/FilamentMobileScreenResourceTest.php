<?php

namespace Tests\Feature;

use App\Models\MobileScreen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentMobileScreenResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_mobile_screens_resource_renders_in_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $screen = MobileScreen::query()->create([
            'screen_id' => 'home',
            'title' => 'صفحه خانه پنل',
            'version' => 1,
            'hide_title' => true,
            'is_active' => true,
        ]);

        $screen->sections()->create([
            'section_id' => 'home-hero',
            'type' => 'hero',
            'data' => ['title' => 'قهرمان صفحه خانه'],
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/admin/mobile-screens')
            ->assertOk()
            ->assertSee('صفحه خانه پنل');

        $this->actingAs($user)
            ->get("/admin/mobile-screens/{$screen->id}")
            ->assertOk()
            ->assertSee('صفحه خانه پنل');
    }
}
