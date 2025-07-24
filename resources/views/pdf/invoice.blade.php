<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #{{ $tva->facture_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
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
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 15px; 
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
            font-size: 24px; 
            font-weight: bold; 
            text-decoration: underline; 
            text-align: center; 
            flex-grow: 1; 
            color:mediumblue;
        }
        .top-right-logo img { 
            width: 100px; 
            height: auto; 
        }

        .company-details { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 15px; 
            gap: 20px; 
        }
        .company-info, .client-box {
            width: 48%;
            border: 2px solid #000;
            padding: 8px;
            background: #ffffffff;
        }
        .company-info h3 { 
            color: #c00; 
            font-size: 12px; 
            font-weight: bold; 
            margin-bottom: 3px; 
        }
        .company-info p, .client-box p { 
            margin: 3px 0; 
            font-size: 10px; 
        }
        .client-box strong { 
            font-size: 11px; 
        }

        .invoice-meta { 
            margin-bottom: 15px; 
        }
        .meta-table, .items-table, .totals-table {
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px;
        }
        .meta-table td, .items-table td, .items-table th, .totals-table td {
            border: 1px solid #000; 
            padding: 4px 8px;
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
            margin-top: 20px; 
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
            margin-bottom: 5px; 
            font-weight: bold;
        }
        .signature-line {
            height: 30px;
            border-bottom: 1px solid #000;
            width: 200px;
            margin-left: auto;
        }

        .footer-info {
            margin-top: 40px; 
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
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-logo">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo de l'entreprise">
                @else
                    <div style="width:120px; height:60px; color:white; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                        LOGO
                    </div>
                @endif
            </div>
            <div class="invoice-title">FACTURE</div>
        </div>

        <div class="company-details">
            <div class="company-info">
                <h3>{{ $settings['company_name'] ?? 'Nom de l\'entreprise' }}</h3>
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
                    <td>{{ $tva->designation }}</td>
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
            <div class="signature-box">
                <div class="label">Signature :</div>
                <div class="signature-line"></div>
            </div>
        </div>

        <div class="footer-info">
            ICE : {{ $settings['ice'] ?? '---' }} | RC : {{ $settings['rc'] ?? '---' }} |
            PATTENTE : {{ $settings['patente']  ?? '---' }} | IF : {{ $settings['if'] ?? '---' }}<br>
            MERCI POUR VOTRE CONFIANCE - CONTACT : {{ $settings['company_email'] ?? '---' }}
        </div>
    </div>
</body>
</html>