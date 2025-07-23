<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gathered Order Summary</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 40px;
            line-height: 1.5;
        }
        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }
        .logo {
            width: 250px;
            margin-bottom: 15px;
        }
        .header {
            border-bottom: 2px solid #2a4365;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h2 {
            color: #2a4365;
            margin: 8px 0;
            font-size: 18px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 4px 0;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th {
            background-color: #2a4365;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: 500;
        }
        td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .grand-total {
            font-weight: bold;
            background-color: #e2e8f0;
            color: #1a202c;
        }
        .signature {
            margin-top: 40px;
        }
        .signature p {
            margin: 5px 0;
        }
        .text-bold { font-weight: bold; }
        .text-blue { color: #2a4365; }
        .item-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border: 1px solid #eee;
            padding: 2px;
            background: white;
        }
    </style>
</head>
<body>

    <div class="center header">
        <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Koncepto Logo">
        <h2>KONCEPTO SCHOOL SUPPLIES AND EQUIPMENT TRADING</h2>
    </div>

<div class="info-section" style="margin: 0; padding: 0;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            @if (!empty($order->user->school->image) && file_exists(public_path('storage/' . $order->user->school->image)))
                <td style="width: 110px; vertical-align: top;">
                    <img src="{{ public_path('storage/' . $order->user->school->image) }}" alt="School Logo"
                         style="width: 100px; height: 100px; object-fit: contain; border: 1px solid #ddd;">
                </td>
            @endif
            <td style="padding-left: 20px; vertical-align: top;">
                <p class="text-bold left" style="margin: 2px 0;">
                    Name:
                    <span class="text-blue">
                        {{ $order->user->first_name ?? '' }} {{ $order->user->last_name ?? '' }}
                    </span>
                </p>
                <p class="text-bold left" style="margin: 2px 0;">
                    Position:
                    <span class="text-blue">
                        {{ $order->user->role === 'school_admin' ? 'School Administrative Officer' : ucfirst($order->user->role ?? 'N/A') }}
                    </span>
                </p>
                <p class="text-bold left" style="margin: 2px 0;">
                    Business Name:
                    <span class="text-blue">{{ $order->user->school->school_name ?? 'N/A' }}</span>
                </p>
                <p class="text-bold left" style="margin: 2px 0;">
                    Business Address:
                    <span class="text-blue">{{ $order->user->school->address ?? 'N/A' }}</span>
                </p>
            </td>
        </tr>
    </table>
</div>



    <table>
        <thead>
            <tr>
                <th width="5%">Item No.</th>
                <th width="8%">Unit</th>
                <th width="20%">Product Name</th>
                <th width="8%">Quantity</th>
                <th width="15%">Offered Brand</th>
                <th width="10%">Image</th>
                <th width="12%">Unit Cost</th>
                <th width="12%">Total Cost</th>
                <th width="10%">Prepared?</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach ($order->items as $index => $item)
                @php
                    $price = $item['price'] ?? 0;
                    $quantity = $item['quantity'] ?? 0;
                    $total = $price * $quantity;
                    $grandTotal += $total;
                    $photoPath = public_path('storage/' . ($item['photo'] ?? ''));
                    $gathered = $item['gathered'] ?? false;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['unit'] ?? '-' }}</td>
                    <td style="text-align: left;">{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['brand'] ?? '-' }}</td>
                    <td>
                        @if (!empty($item['photo']) && file_exists($photoPath))
                            <img src="{{ $photoPath }}" class="item-image" alt="Product Image">
                        @else
                            -
                        @endif
                    </td>
                    <td>&#8369; {{ number_format($price, 2) }}</td>
                    <td>&#8369; {{ number_format($total, 2) }}</td>
                    <td>
                        <input type="checkbox" {{ $gathered ? 'checked' : '' }} disabled>
                    </td>
                </tr>
            @endforeach

            <tr class="grand-total">
                <td colspan="7" class="right text-bold">Grand Total</td>
                <td class="text-bold">&#8369;{{ number_format($grandTotal, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="signature left">
        <p class="text-bold">Certified Correct:</p>
        <br><br>
        <p>_____________________________</p>
        <p class="text-bold">REENA OPHELIA C. ANGELES</p>
        <p>Proprietor</p>
    </div>

</body>
</html>
