<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Facture #{{ $tva->facture_number }}</title>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family:  Helvetica, sans-serif;
    font-size: 12px;
    padding: 15px;
    color: #000;
    background: white;
    line-height: 1.4;
}

.invoice-container {
    max-width: 210mm;
    margin: 0 auto;
    border: 2px solid #000;
    padding: 15px;
    background: #fff;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);

    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 277mm; 
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px; 
    padding-bottom: 10px;
    border-bottom: 2px solid #ccc;
}

.company-logo img {
    width: 120px;
    height: auto;
    border: 1px solid #eee;
    padding: 3px;
}

.invoice-title {
    font-size: 28px;
    font-weight: bold;
    text-decoration: underline;
    text-align: center;
    flex-grow: 1;
    color: mediumblue;
    padding: 0 20px;
}

.top-right-logo img {
    width: 100px;
    height: auto;
}

.company-details {
    display: flex;
    justify-content: space-between;
    margin-bottom: 25px;
    gap: 30px;
}

.company-info,
.client-box {
    width: 48%;
    border: 2px solid #000;
    padding: 15px; 
    background: #fff;
}

.company-info h3 {
    color: #c00;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 6px;
}

.company-info p,
.client-box p {
    margin: 6px 0;
    font-size: 11px;
    font-weight: bold;
}

.client-box strong {
    font-size: 12px;
}

.invoice-meta {
    margin-bottom: 25px;
}

.meta-table,
.items-table,
.totals-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

.meta-table td,
.items-table td,
.items-table th,
.totals-table td {
    border: 1px solid #000;
    padding: 8px 12px;
}

.items-table th {
    background-color: #eaeaea;
    font-weight: bold;
}

.items-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.bottom-section {
    display: flex;
    justify-content: space-between;
    margin-top: auto; 
    align-items: flex-end;
    gap: 30px;
    padding-top: 30px;
    border-top: 2px solid #ccc; 
}

.totals-box {
    width: 45%;
}

.signature-box {
            width: 45%;
            text-align: right;
            margin-top: 30px;
            margin-left: 350px;
        }

        .signature-box .label {
            font-size: 11px;
            margin-top: 50px;

            margin-bottom: 5px;
            font-weight: bold;
        }

        .signature-line {
            min-height: auto;
            border-bottom: none;
            width: 200px;
            margin-left: auto;
            overflow: visible;
        }

        .signature-line img {
            max-height: auto;
        }


        .footer-info {
            margin-top: 100px;

            font-size: 9px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
            color: #555;
        }


.totals-table tr:last-child {
    font-weight: bold;
    background-color: #f0f0f0;
}

/* FOR PRINTING  */
@page {
    size: A4;
    margin: 15mm;
}

@media print {
    body, html {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 0;
        background: white;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        font-size: 12px;
        line-height: 1.4;
    }

    .invoice-container {
        height: 277mm; 
        box-sizing: border-box;
        margin: 0 auto;
        border: 2px solid #000;
        padding: 25px 30px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #fff;
        box-shadow: none;
    }

    .bottom-section {
        margin-top: auto; 
        padding-top: 30px;
        border-top: 2px solid #ccc;
    }

    .invoice-container,
    .items-table,
    .totals-table {
        page-break-inside: avoid;
    }
}
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-logo">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo de l'entreprise">
                @else
                    <div
                        style="width:120px; height:60px; color:white; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                        LOGO
                    </div>
                @endif
            </div>
            <div class="invoice-title">FACTURE</div>
        </div>

        <div class="company-details">
            <div class="company-info">
                <strong>
                    <h2 style="color:#c00 ;">{{ $settings['company_name'] ?? 'Nom de l\'entreprise' }}</h2>
                </strong>
                <p>{{ $settings['company_address'] ?? '' }}</p>
                <p>TÉLÉPHONE : {{ $settings['company_phone'] ?? '' }}</p>
                <p>EMAIL : {{ $settings['company_email'] ?? '' }}</p>
            </div>
            <div class="client-box">
                <strong>CLIENT : {{ $tva->client_name }}</strong>
                <p><strong>Adresse :</strong> {{ $tva->client_address }}</p>
            </div>
        </div>

        <div class="invoice-meta">
            <table class="meta-table">
                <tr>
                    <td>DATE :</td>
                    <td>{{ \Carbon\Carbon::parse($tva->created_at)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>FACTURE N° :</td>
                    <td>{{ $tva->facture_number }}</td>
                </tr>
                <tr>
                    <td>Référence :</td>
                    <td> ESPECE </td>
                    <!-- <td>{{ $tva->designation }}</td> -->
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>QTE</th>
                    <th>P.U.H.T</th>
                    <th>TOTAL H.T</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tva->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} MAD</td>
                        <td>{{ number_format($item->total_ht, 2) }} MAD</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="bottom-section">
            <div class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td>Montant H.T</td>
                        <td>{{ number_format($tva->total_ht, 2) }} MAD</td>
                    </tr>
                    <tr>
                        <td>TVA</td>
                        <td>{{ number_format($tva->tva, 2) }} MAD</td>
                    </tr>
                    <tr>
                        <td>Montant T.T.C</td>
                        <td>{{ number_format($tva->montant_ttc, 2) }} MAD</td>
                    </tr>
                </table>
            </div>
            @php
                $setting = \App\Models\Setting::where('name', 'admin_signature')->first();
            @endphp

            <div class="signature-box">
                <div class="label">Signature :</div>
                <div class="signature-line" style="padding-left: 200px;"
                    style="{{ $setting && $setting->value ? 'height: auto; border: none;' : '' }}">
                    @if ($setting && $setting->value && Storage::disk('public')->exists($setting->value))
                        <img src="{{ Storage::disk('public')->path($setting->value) }}" alt="Admin Signature"
                        style="max-height: 300px; max-width: 300px;" >
                    @else
                        <span class="text-muted">No signature .</span>
                    @endif
                </div>

            </div>

        </div>

        <div class="footer-info">
            ICE : {{ $settings['ice'] ?? '---' }} | RC : {{ $settings['rc'] ?? '---' }} |
            PATTENTE : {{ $settings['patente'] ?? '---' }} | IF : {{ $settings['if'] ?? '---' }}<br>
            MERCI POUR VOTRE CONFIANCE - CONTACT : {{ $settings['company_email'] ?? '---' }}
        </div>
    </div>
</body>

</html>
