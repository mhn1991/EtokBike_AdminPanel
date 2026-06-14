@extends('storefront.layouts.app')

@section('content')
    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div>
                <p class="text-sm font-semibold text-red-700">ارتباط با EtokBike</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight tracking-normal text-neutral-950 sm:text-5xl">پیام به پشتیبانی</h1>
                <p class="mt-5 leading-8 text-neutral-600">پیام شما در صندوق ورودی پنل مدیریت ثبت می‌شود و تیم مربوطه آن را بررسی می‌کند.</p>
                <div class="mt-6 grid gap-3">
                    @forelse ($departments as $department)
                        <div class="rounded-lg border border-neutral-200 bg-[#f6f3ef] p-4">
                            <p class="font-semibold text-neutral-950">{{ $department->title }}</p>
                            @if ($department->subtitle)
                                <p class="mt-1 text-sm leading-6 text-neutral-600">{{ $department->subtitle }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="rounded-lg border border-neutral-200 bg-[#f6f3ef] p-4 text-sm leading-6 text-neutral-600">واحد پیام فعالی ثبت نشده است.</p>
                    @endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('storefront.messages.store') }}" class="grid gap-5 rounded-lg border border-neutral-200 bg-[#f6f3ef] p-5">
                @csrf
                <label class="grid gap-2 text-sm font-medium text-neutral-800">
                    واحد دریافت‌کننده
                    <select name="message_department_id" required class="min-h-11 rounded-md border border-neutral-300 bg-white px-3 text-neutral-950">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((int) old('message_department_id') === $department->id)>{{ $department->title }}</option>
                        @endforeach
                    </select>
                    @error('message_department_id') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                </label>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        نام و نام خانوادگی
                        <input name="customer_name" value="{{ old('customer_name') }}" required class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                        @error('customer_name') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        شماره تماس
                        <input name="customer_phone" value="{{ old('customer_phone') }}" required class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                        @error('customer_phone') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label class="grid gap-2 text-sm font-medium text-neutral-800">
                    ایمیل
                    <input name="customer_email" type="email" value="{{ old('customer_email') }}" class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                    @error('customer_email') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                </label>

                <label class="grid gap-2 text-sm font-medium text-neutral-800">
                    متن پیام
                    <textarea name="text" rows="7" required class="rounded-md border border-neutral-300 px-3 py-2 text-neutral-950">{{ old('text') }}</textarea>
                    @error('text') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                </label>

                <button type="submit" class="min-h-12 rounded-md bg-neutral-950 px-6 text-sm font-semibold text-white hover:bg-red-700">ارسال پیام</button>
            </form>
        </div>
    </section>
@endsection
