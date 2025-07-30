<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="page-size" content="A4">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Professional Invoice</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Arial", sans-serif;
            line-height: 1.4;
            color: #333;
            background-color: white;
            font-size: 13pt;
        }

        /* Print Styles for A4 */
        @media print {
            @page {
                size: A4;
                margin: 5mm 8mm 15mm 8mm;
                /* Top Right Bottom Left */
            }

            body {
                background-color: white;
                font-size: 13pt;
                line-height: 1.3;
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-container {
                height: auto !important;
                min-height: 260mm;
                padding-bottom: 25mm !important;
                margin: 0;
                padding: 8mm;
                box-shadow: none;
                background: white;
                page-break-inside: avoid;
                position: relative;
                overflow: hidden;
            }

            /* Prevent page breaks */
            .invoice-header,
            .billing-section,
            .invoice-items,
            .invoice-totals,
            .invoice-footer {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                page-break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 5mm 8mm 15mm 8mm;
            }

            /* Style pour la ligne du montant en lettres */
            .amount-in-words {
                font-size: 12px;
                padding: 8px 0;
                border-top: 1px dashed #eee;
            }

            .amount-in-words .total-label {
                font-style: italic;
                font-weight: normal;
            }

            .amount-in-words .total-value {
                font-weight: bold;
                font-style: italic;
            }

            @media print {
                .amount-in-words {
                    font-size: 11px;
                    border-top: 1px dashed #ccc;
                }
            }

            /* Hide elements that shouldn't print */
            .no-print {
                display: none !important;
            }

            /* Force single page layout */
            html,
            body {
                height: 100%;
                margin: 0 !important;
                padding: 0 !important;
            }

        }

        .ttc-in-words {
            font-size: 12px;
            margin-top: 5px;
            padding: 5px 0;
            text-align: right;
            font-style: italic;
            border-top: 1px dashed #eee;
            width: 100%;
        }

        @media print {
            .ttc-in-words {
                font-size: 11px;
                border-top: 1px dashed #ccc;
            }
        }

        /* Container */
        .invoice-container {
            /* Keep these properties: */
            min-height: 250mm;
            /* Reduced from 260mm */
            padding-bottom: 20mm !important;
            /* Reduced from 25mm */
            margin: 0 auto;
            /* Changed from 5px */
            background: white;
            padding: 10mm;
            /* Changed from 15px to mm units */
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            page-break-inside: avoid;

            /* Add these new properties: */
            position: static;
            /* Instead of relative */
            display: flow-root;
            /* Better containment */
            box-sizing: border-box;
        }

        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2563eb;
        }

        .company-info {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .company-logo {
            max-height: 150px;
            width: auto;
        }

        .company-details h1 {
            color: #2563eb;
            font-size: 25px !important;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .company-details p {
            color: #666;
            font-size: 18px;
            line-height: 1.3;
        }

        .invoice-title {
            text-align: right;
            font-size: 32px !important;
        }

        .invoice-title h2 {
            font-size: 28px;
            color: #2563eb;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .invoice-meta p {
            margin-bottom: 3px;
            font-size: 15px;
        }

        /* Billing Section */
        .billing-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .bill-to h3,
        .bill-from h3 {
            color: #2563eb;
            font-size: 20px;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .client-info p,
        .vendor-info p {
            font-size: 15px;
            line-height: 1.3;
            color: #555;
        }

        /* Invoice Items Table */
        .invoice-items {
            margin-bottom: 15px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table thead {
            background-color: #2563eb;
            color: white;
        }

        .items-table th,
        .items-table td {
            padding: 7px 5px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 15px !important;
        }

        .items-table th {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 10px;
        }

        .items-table .description {
            width: 50%;
        }

        .items-table .quantity,
        .items-table .rate,
        .items-table .amount {
            width: 16.67%;
            text-align: right;
        }

        .items-table .quantity {
            text-align: center;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .description strong {
            color: #2563eb;
            font-size: 15px;
        }

        /* Totals Section */
        .invoice-totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            clear: both;
            width: 100%;
        }

        .totals-table {
            width: 300px;
            margin-right: 0;
            float: right;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 15px;
        }

        .total-row.grand-total {
            background-color: #2563eb;
            color: white;
            padding: 10px 15px;
            margin-top: 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 13px;
            border: none;
        }

        .total-label {
            font-weight: 600;
        }

        .total-value {
            font-weight: bold;
        }

        /* Payment Information */
        .payment-info {
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }

        .payment-info h3 {
            color: #2563eb;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .payment-info p {
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Notes and Terms */
        .invoice-notes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .notes h3,
        .terms h3 {
            color: #2563eb;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .notes p {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
        }

        .terms ul {
            list-style: none;
            padding-left: 0;
        }

        .terms li {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .terms li:before {
            content: "•";
            color: #2563eb;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        /* Footer */
        .invoice-footer {
            text-align: center;
            text-align: center;
            font-size: 20px !important;
            padding: 5mm 0;
            border-top: 2px solid #e5e7eb;
            color: #666;
            position: fixed;
            bottom: 0;
            left: 8mm;
            right: 8mm;
            background: white;
            z-index: 100;
        }

        .footer-content p {
            margin-bottom: 3px;
            font-size: 15px !important;
        }

        /* Additional Print Optimizations */
        @media print {
            .invoice-container {
                padding: 5mm 8mm !important;
                margin: 0 !important;
                width: 100% !important;
                height: auto !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                box-shadow: none !important;
                page-break-after: avoid !important;
                min-height: 270mm;
                padding-bottom: 30mm !important;
                max-height: 287mm;
            }

            .invoice-header {
                margin-bottom: 3mm !important;
                padding-bottom: 2mm !important;
            }

            .billing-section {
                margin-bottom: 5mm !important;
            }

            .invoice-items {
                margin-bottom: 5mm !important;
            }

            .invoice-totals {
                margin-bottom: 5mm !important;
                clear: both;
            }

            .invoice-footer {
                position: fixed !important;
                bottom: 0;
                left: 8mm !important;
                right: 8mm !important;
                padding: 5mm;
                text-align: center;
                font-size: 17px !important;
                border-top: 1px solid #e5e7eb;
                background: white;
                z-index: 100;
                margin: 0 !important;
            }

            /* Reduce font sizes for better fit */
            .company-details h1 {
                font-size: 25px !important;
            }

            .invoice-title h2 {
                font-size: 24px !important;
            }

            .items-table th,
            .items-table td {
                padding: 4px 3px !important;
                font-size: 9px !important;
            }

            /* Ensure colors print correctly */
            .invoice-header,
            .items-table thead,
            .total-row.grand-total {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            /* Force single page */
            * {
                page-break-inside: avoid !important;
            }

            .invoice-container * {
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <header class="invoice-header">
            <div class="company-info">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="Company Logo" class="company-logo">
                @else
                    <div
                        style="width:120px; height:60px; color:white; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                        LOGO
                    </div>
                @endif

                <div class="company-details">
                    <h1>{{ $settings['company_name'] ?? 'Nom de l\'entreprise' }}</h1>
                    <p>{{ $settings['company_address'] ?? '' }}<br>
                        Code Postal: 93030<br>
                        Phone: {{ $settings['company_phone'] ?? ' ' }}<br>
                        Email: {{ $settings['company_email'] ?? ' ' }}</p>
                </div>
            </div>
            <div class="invoice-title">
                <h2>FACTURE</h2>
                <div class="invoice-meta">
                    <p><strong>FACTURE N° :</strong> {{ $tva->facture_number }}</p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($tva->created_at)->format('d/m/Y') }}</p>
                    <p><strong>Mode de paiement:</strong> ESPECE</p>
                </div>
            </div>
        </header>

        <!-- Bill To Section -->
        <section class="billing-section">
            <div class="bill-to">
                <h3>Client:</h3>
                <div class="client-info">
                    <p><strong>{{ $tva->client_name }}</strong><br>
                        {{ $tva->client_address }}<br>

                </div>
            </div>
            {{-- <div class="bill-from">
                <h3>From:</h3>
                <div class="vendor-info">
                    <p><strong>Your Company Name</strong><br>
                    Jane Smith<br>
                    123 Business Street<br>
                    City, State 12345<br>
                    Tax ID: 12-3456789</p>
                </div>
            </div> --}}
        </section>

        <!-- Invoice Items Table -->
        <section class="invoice-items">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="description">Description</th>
                        <th class="quantity">Qty</th>
                        <th class="rate">P.U.H.T</th>
                        <th class="amount">TOTAL H.T</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tva->items as $item)
                        <tr>
                            <td class="description">
                                <strong>{{ $item->description }}</strong><br>
                                {{-- <small>{{ $item->details }}</small> --}}
                            </td>
                            <td class="quantity">{{ $item->quantity }}</td>

                            <td class="rate">{{ number_format($item->unit_price, 2) }} MAD</td>
                            <td class="amount">{{ number_format($item->total_ht, 2) }} MAD</td>
                        </tr>
                    @endforeach


                </tbody>
            </table>
        </section>

        <!-- Totals Section -->
        <section class="invoice-totals">
            <div class="totals-table">
                <div class="total-row">
                    <span class="total-label">Montant H.T:</span>
                    <span class="total-value">{{ number_format($tva->total_ht, 2) }} MAD</span>
                </div>
                <div class="total-row">
                    <span class="total-label">TVA (20%):</span>
                    <span class="total-value">{{ number_format($tva->tva, 2) }} MAD</span>
                </div>
                {{-- <div class="total-row discount">
                    <span class="total-label">Montant T.T.C:</span>
                    <span class="total-value">-$267.50</span>
                </div> --}}
                <div class="total-row grand-total">
                    <span class="total-label">Montant T.T.C:</span>
                    <span class="total-value">{{ number_format($tva->montant_ttc, 2) }} MAD</span>
                </div>
                <div class="total-row amount-in-words">
                    <span class="total-label">Arrêtée à la somme de:</span>
                    <span class="total-value">{{ $ttcInWords }}</span>
                </div>
            </div>
        </section>

        <div style="clear: both;"></div>

        @php
            $setting = \App\Models\Setting::where('name', 'admin_signature')->first();
        @endphp
        <!-- Payment Information -->
        {{-- <section class="payment-info">
            <div class="payment-details">
                <h3>Signature</h3>
                <p><strong>Payment Terms:</strong> Net 30 days</p>
                <p><strong>Payment Method:</strong> Bank Transfer, Check, or Credit Card</p>
                <p><strong>Bank Details:</strong><br>
                    Account Name: Your Company Name<br>
                    Account Number: 1234567890<br>
                    Routing Number: 987654321<br>
                    Bank: First National Bank</p>
            </div>
        </section> --}}

        <!-- Notes and Terms -->
        {{-- <section class="invoice-notes">
            <div class="notes">
                <h3>Notes</h3>
                <p>Thank you for your business! Please remit payment within 30 days of the invoice date. Late payments may be subject to a 1.5% monthly service charge.</p>
            </div>
            <div class="terms">
                <h3>Terms & Conditions</h3>
                <ul>
                    <li>Payment is due within 30 days of invoice date</li>
                    <li>Late payments subject to 1.5% monthly service charge</li>
                    <li>All work performed is subject to our standard terms of service</li>
                    <li>Disputes must be reported within 10 days of invoice date</li>
                </ul>
            </div>
        </section> --}}


        <!-- Footer -->
        <footer class="invoice-footer">
            <div class="footer-content">
                <p>Nous vous Remercions de Votre Confiance!</p>
                <p>ICE : {{ $settings['ice'] ?? '---' }} | RC : {{ $settings['rc'] ?? '---' }} |
                    PATTENTE : {{ $settings['patente'] ?? '---' }} | IF : {{ $settings['if'] ?? '---' }}<br>
                    CONTACT : {{ $settings['company_email'] ?? '---' }}</p>
            </div>
        </footer>
    </div>
</body>


</html>
