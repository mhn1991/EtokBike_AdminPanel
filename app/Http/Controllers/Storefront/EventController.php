<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramBooking;
use App\Models\ProgramCategory;
use App\Support\Customers\CustomerProfileUpdater;
use App\Support\Storefront\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index(): View
    {
        $categories = ProgramCategory::query()
            ->where('is_active', true)
            ->with(['programs' => fn ($query) => $query
                ->where('is_active', true)
                ->with('galleryItems')
                ->orderBy('program_state')
                ->orderBy('sort_order')
                ->orderBy('date_value')])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return view('storefront.events.index', [
            'categories' => $categories,
            'meta' => [
                'title' => Seo::defaultTitle('برنامه‌ها و رویدادها | EtokBike'),
                'description' => Seo::defaultDescription('برنامه‌های آینده، رویدادهای دوچرخه‌سواری و گالری برنامه‌های برگزار شده EtokBike.'),
                'canonical' => route('storefront.events'),
            ],
            'structuredData' => [
                Seo::breadcrumbs([
                    ['name' => Seo::siteName(), 'url' => route('storefront.home')],
                    ['name' => 'برنامه‌ها', 'url' => route('storefront.events')],
                ]),
            ],
        ]);
    }

    public function show(Program $program): View
    {
        abort_unless($program->is_active && $program->category?->is_active, 404);

        return view('storefront.events.show', [
            'program' => $program->load('category', 'galleryItems'),
            'meta' => [
                'title' => $program->title.' | '.Seo::siteName(),
                'description' => Seo::description($program->advertisement, $program->subtitle),
                'canonical' => route('storefront.events.show', $program),
                'image' => Seo::image(\App\Support\Mobile\ImageUrl::resolve($program->image_url)),
            ],
            'structuredData' => [
                Seo::breadcrumbs([
                    ['name' => Seo::siteName(), 'url' => route('storefront.home')],
                    ['name' => 'برنامه‌ها', 'url' => route('storefront.events')],
                    ['name' => $program->title, 'url' => route('storefront.events.show', $program)],
                ]),
            ],
        ]);
    }

    public function book(Request $request, Program $program, CustomerProfileUpdater $profiles): RedirectResponse
    {
        abort_unless($program->is_active, 404);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'attendees' => ['nullable', 'integer', 'min:1', 'max:20'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($program->program_state !== 'future') {
            throw ValidationException::withMessages([
                'program' => 'فقط برنامه‌های آینده امکان رزرو دارند.',
            ]);
        }

        $profiles->update(null, $validated);

        DB::transaction(function () use ($program, $validated): void {
            $lockedProgram = Program::query()->lockForUpdate()->findOrFail($program->id);
            $attendees = (int) ($validated['attendees'] ?? 1);

            if ($lockedProgram->capacity !== null && ($lockedProgram->reserved_count + $attendees) > $lockedProgram->capacity) {
                throw ValidationException::withMessages([
                    'attendees' => 'ظرفیت کافی برای این تعداد نفر باقی نمانده است.',
                ]);
            }

            ProgramBooking::query()->create([
                'program_id' => $lockedProgram->id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'attendees' => $attendees,
                'customer_notes' => $validated['customer_notes'] ?? null,
                'status' => 'pending',
            ]);
        });

        return redirect()
            ->route('storefront.events.show', $program)
            ->with('status', 'درخواست رزرو برنامه ثبت شد.');
    }
}
