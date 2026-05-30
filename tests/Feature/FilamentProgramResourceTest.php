<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentProgramResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_programs_resource_renders_in_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $category = ProgramCategory::query()->create([
            'slug' => 'rides',
            'label' => 'رکاب‌زنی',
            'title' => 'برنامه‌های رکاب‌زنی',
        ]);

        Program::query()->create([
            'program_category_id' => $category->id,
            'slug' => 'ride-test',
            'title' => 'تمرین تست',
            'subtitle' => 'برنامه تست',
            'date_value' => '2026-06-05',
            'date_label' => 'جمعه ۱۵ خرداد ۱۴۰۵، ساعت ۹',
            'program_state' => 'future',
        ]);

        $this->actingAs($user)
            ->get('/admin/programs')
            ->assertOk()
            ->assertSee('تمرین تست');
    }

    public function test_the_program_categories_resource_renders_in_the_admin_panel(): void
    {
        $user = User::factory()->create();

        ProgramCategory::query()->create([
            'slug' => 'camping',
            'label' => 'کمپینگ',
            'title' => 'برنامه‌های کمپینگ',
        ]);

        $this->actingAs($user)
            ->get('/admin/program-categories')
            ->assertOk()
            ->assertSee('کمپینگ');
    }
}
