<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\StoreProfile;
use Illuminate\Contracts\View\View;

class ReceiptPrintController extends Controller
{
    public function __invoke(Receipt $receipt): View
    {
        return view('admin.receipts.print', [
            'receipt' => $receipt->load(['items', 'order', 'returnRequest']),
            'storeProfile' => StoreProfile::query()
                ->where('is_active', true)
                ->latest()
                ->first(),
        ]);
    }
}
