<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerMessage;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\ProgramBooking;
use App\Models\ServiceBooking;
use App\Support\Customers\CustomerProfileUpdater;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = CustomerProfile::query()
            ->where('user_id', $user->id)
            ->with(['bikeProfiles' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')])
            ->first();

        return response()->json([
            'data' => [
                'profile' => $profile ? $this->profilePayload($profile) : [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => null,
                    'delivery_address' => null,
                ],
                'orders' => Order::query()
                    ->where('user_id', $user->id)
                    ->with(['items', 'shipments', 'receipts', 'returnRequests'])
                    ->latest('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(fn (Order $order): array => $this->orderPayload($order))
                    ->values(),
                'service_bookings' => ServiceBooking::query()
                    ->where('user_id', $user->id)
                    ->latest('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(fn (ServiceBooking $booking): array => $this->serviceBookingPayload($booking))
                    ->values(),
                'program_bookings' => ProgramBooking::query()
                    ->where('user_id', $user->id)
                    ->with('program')
                    ->latest('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(fn (ProgramBooking $booking): array => $this->programBookingPayload($booking))
                    ->values(),
                'messages' => CustomerMessage::query()
                    ->where('user_id', $user->id)
                    ->with('department')
                    ->latest()
                    ->limit(20)
                    ->get()
                    ->map(fn (CustomerMessage $message): array => $this->messagePayload($message))
                    ->values(),
            ],
        ]);
    }

    public function update(Request $request, CustomerProfileUpdater $profiles): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $user->forceFill([
            'name' => $validated['name'],
        ])->save();

        $profile = $profiles->update($user, $validated);

        return response()->json([
            'data' => [
                'profile' => $profile ? $this->profilePayload($profile) : null,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload(CustomerProfile $profile): array
    {
        return [
            'name' => $profile->name,
            'phone' => $profile->phone,
            'email' => $profile->email,
            'delivery_address' => $profile->delivery_address,
            'bikes' => $profile->bikeProfiles
                ->map(fn ($bike): array => $bike->toMobilePayload())
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'fulfillment_method' => $order->fulfillment_method,
            'subtotal' => $order->subtotal,
            'discount_total' => $order->discount_total,
            'delivery_total' => $order->delivery_total,
            'total' => $order->total,
            'items' => $order->items->map(fn ($item): array => [
                'title' => $item->title,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
            ])->values(),
            'shipments' => $order->shipments->map(fn ($shipment): array => [
                'status' => $shipment->status,
                'tracking_number' => $shipment->tracking_number,
                'carrier_name' => $shipment->carrier_name,
                'delivery_address' => $shipment->delivery_address,
            ])->values(),
            'receipts' => $order->receipts->map(fn ($receipt): array => [
                'receipt_number' => $receipt->receipt_number,
                'type' => $receipt->type,
                'status' => $receipt->status,
                'total' => $receipt->total,
            ])->values(),
            'returns' => $order->returnRequests->map(fn ($return): array => [
                'return_number' => $return->return_number,
                'status' => $return->status,
                'refund_status' => $return->refund_status,
                'refund_total' => $return->refund_total,
            ])->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceBookingPayload(ServiceBooking $booking): array
    {
        return [
            'id' => $booking->id,
            'service_type' => $booking->service_type,
            'bike_label' => $booking->bike_label,
            'preferred_time' => $booking->preferred_time,
            'status' => $booking->status,
            'problem_description' => $booking->problem_description,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function programBookingPayload(ProgramBooking $booking): array
    {
        return [
            'id' => $booking->id,
            'program' => $booking->program?->slug,
            'program_title' => $booking->program?->title,
            'attendees' => $booking->attendees,
            'status' => $booking->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function messagePayload(CustomerMessage $message): array
    {
        return [
            'id' => $message->id,
            'department' => $message->department?->slug,
            'department_title' => $message->department?->title,
            'sender' => $message->sender,
            'label' => $message->label,
            'text' => $message->text,
            'time' => $message->time_label,
            'is_unread' => $message->is_unread,
        ];
    }
}
