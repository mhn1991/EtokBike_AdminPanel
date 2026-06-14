@php
    use App\Models\Receipt;
    use App\Support\Storefront\PriceFormatter;

    $documentLabel = Receipt::TYPE_OPTIONS[$receipt->type] ?? 'Receipt';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentLabel }} {{ $receipt->receipt_number }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f7f9;
            --surface: #ffffff;
            --text: #101114;
            --muted: #5d6675;
            --border: #d9dee7;
            --accent: #c18408;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            max-width: 960px;
            margin: 24px auto 0;
            padding: 0 20px;
        }

        .button {
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            font: inherit;
            padding: 10px 14px;
        }

        .button.primary {
            border-color: var(--accent);
            background: var(--accent);
            color: #ffffff;
        }

        .document {
            max-width: 960px;
            margin: 20px auto 40px;
            padding: 40px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: start;
            border-bottom: 1px solid var(--border);
            padding-bottom: 24px;
        }

        .brand {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
        }

        .meta {
            text-align: right;
        }

        .label {
            color: var(--muted);
            font-size: 13px;
            margin: 0;
        }

        .value {
            margin: 4px 0 0;
            font-weight: 600;
        }

        .section-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
            margin-top: 28px;
        }

        h2 {
            font-size: 15px;
            margin: 0 0 10px;
        }

        p {
            margin: 0;
        }

        .muted {
            color: var(--muted);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 28px;
        }

        th,
        td {
            border-bottom: 1px solid var(--border);
            padding: 12px 0;
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }

        .numeric {
            text-align: right;
            white-space: nowrap;
        }

        .totals {
            display: grid;
            justify-content: end;
            margin-top: 24px;
        }

        .totals table {
            min-width: 320px;
            margin-top: 0;
        }

        .totals td {
            padding: 8px 0;
        }

        .total-row td {
            border-bottom: 0;
            font-size: 18px;
            font-weight: 700;
            padding-top: 14px;
        }

        .notes {
            margin-top: 28px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }

        @media (max-width: 720px) {
            .document {
                border-radius: 0;
                border-left: 0;
                border-right: 0;
                margin: 16px 0 32px;
                padding: 24px 20px;
            }

            .header,
            .section-grid {
                grid-template-columns: 1fr;
            }

            .meta {
                text-align: left;
            }

            .toolbar {
                margin-top: 16px;
            }

            .totals {
                display: block;
            }

            .totals table {
                min-width: 0;
            }
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .document {
                max-width: none;
                margin: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar" aria-label="Receipt actions">
        <button class="button" type="button" onclick="window.close()">Close</button>
        <button class="button primary" type="button" onclick="window.print()">Print / Save PDF</button>
    </div>

    <main class="document">
        <header class="header">
            <div>
                <h1 class="brand">EtokBike</h1>
                @if ($storeProfile)
                    <p class="muted">{{ $storeProfile->branch_title }}</p>
                    <p class="muted">{{ $storeProfile->address }}</p>
                    <p class="muted">{{ $storeProfile->hours }}</p>
                @endif
            </div>
            <div class="meta">
                <p class="label">{{ $documentLabel }}</p>
                <p class="value">{{ $receipt->receipt_number }}</p>
                <p class="label" style="margin-top: 12px;">Issued</p>
                <p class="value">{{ $receipt->issued_at?->format('Y-m-d H:i') ?? '-' }}</p>
                <p class="label" style="margin-top: 12px;">Status</p>
                <p class="value">{{ Receipt::STATUS_OPTIONS[$receipt->status] ?? $receipt->status }}</p>
            </div>
        </header>

        <section class="section-grid">
            <div>
                <h2>Bill to</h2>
                <p>{{ $receipt->customer_name }}</p>
                @if ($receipt->customer_phone)
                    <p class="muted">{{ $receipt->customer_phone }}</p>
                @endif
                @if ($receipt->customer_email)
                    <p class="muted">{{ $receipt->customer_email }}</p>
                @endif
                @if ($receipt->billing_address)
                    <p class="muted">{{ $receipt->billing_address }}</p>
                @endif
            </div>
            <div>
                <h2>Reference</h2>
                <p>Order: {{ $receipt->order?->order_number ?? '-' }}</p>
                @if ($receipt->returnRequest)
                    <p>Return: {{ $receipt->returnRequest->return_number }}</p>
                @endif
                <p class="muted">Payment: {{ $receipt->payment_status ?: '-' }}</p>
                @if ($receipt->payment_method)
                    <p class="muted">Method: {{ $receipt->payment_method }}</p>
                @endif
            </div>
        </section>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th class="numeric">Qty</th>
                    <th class="numeric">Unit</th>
                    <th class="numeric">Line total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receipt->items as $item)
                    <tr>
                        <td>{{ $item->title }}</td>
                        <td class="muted">{{ $item->sku ?: '-' }}</td>
                        <td class="numeric">{{ number_format($item->quantity) }}</td>
                        <td class="numeric">{{ PriceFormatter::format($item->unit_price, $receipt->currency) }}</td>
                        <td class="numeric">{{ PriceFormatter::format($item->line_total, $receipt->currency) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <section class="totals" aria-label="Receipt totals">
            <table>
                <tbody>
                    <tr>
                        <td>Subtotal</td>
                        <td class="numeric">{{ PriceFormatter::format($receipt->subtotal, $receipt->currency) }}</td>
                    </tr>
                    <tr>
                        <td>Discount</td>
                        <td class="numeric">{{ PriceFormatter::format($receipt->discount_total, $receipt->currency) }}</td>
                    </tr>
                    <tr>
                        <td>Delivery</td>
                        <td class="numeric">{{ PriceFormatter::format($receipt->delivery_total, $receipt->currency) }}</td>
                    </tr>
                    <tr>
                        <td>Tax</td>
                        <td class="numeric">{{ PriceFormatter::format($receipt->tax_total, $receipt->currency) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="numeric">{{ PriceFormatter::format($receipt->total, $receipt->currency) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        @if ($receipt->notes)
            <section class="notes">
                <h2>Notes</h2>
                <p class="muted">{{ $receipt->notes }}</p>
            </section>
        @endif
    </main>
</body>
</html>
