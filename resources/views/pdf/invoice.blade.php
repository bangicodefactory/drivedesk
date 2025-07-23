<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #{{ $tva->facture_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 15px; color: #000; background: white; }
        .invoice-container { max-width: 210mm; margin: 0 auto; border: 2px solid #000; padding: 15px; }

        .header { text-align: center; margin-bottom: 15px; }
        .company-logo img { width: 120px; height: auto; margin-bottom: 10px; }
        .invoice-title { font-size: 24px; font-weight: bold; text-decoration: underline; }

        .company-details { display: flex; justify-content: space-between; margin-bottom: 15px; gap: 20px; }
        .company-info, .client-box {
            width: 48%;
            border: 2px solid #000;
            padding: 8px;
            height: auto;
        }
        .company-info h3 { color: red; font-size: 12px; font-weight: bold; margin-bottom: 3px; }
        .company-info p, .client-box p { margin: 1px 0; font-size: 10px; }
        .client-box strong { font-size: 11px; }

        .invoice-meta, .totals-table td { font-size: 11px; }
        .meta-table, .items-table, .totals-table {
            width: 100%; border-collapse: collapse; margin-bottom: 15px;
        }
        .meta-table td, .items-table td, .items-table th, .totals-table td {
            border: 1px solid #000; padding: 4px 8px;
        }
        .items-table th { background-color: #f0f0f0; }
        .bottom-section { display: flex; justify-content: space-between; }
        .payment-terms { width: 60%; font-size: 10px; line-height: 1.4; }

        .footer-info {
            margin-top: 15px; font-size: 9px; text-align: center; border-top: 1px solid #000; padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-logo">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo de l'entreprise">
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
                <tr>
                    <td>Signature :</td>
                    <td><div style="height: 30px; border-bottom: 1px solid #000; width: 200px; margin-top: 10px;"></div></td>
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
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->total_ht, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="bottom-section">
            <div class="payment-terms">
                <p><strong>Conditions de paiement :</strong> {{ $settings['payment_terms'] ?? 'Net 30 jours' }}</p>
                <p><strong>Date limite de paiement :</strong> {{ \Carbon\Carbon::parse($tva->created_at)->addDays(30)->format('d/m/Y') }}</p>
                <p><strong>Pénalité de retard :</strong> 1.5% par mois</p>
                <br>
                <p><strong>Coordonnées bancaires :</strong></p>
                <p>Compte : {{ $settings['bank_account_name'] ?? '' }}</p>
                <p>Numéro : {{ $settings['bank_account_number'] ?? '' }}</p>
                <p>Code : {{ $settings['bank_routing'] ?? '' }}</p>
            </div>

            <table class="totals-table">
                <tr>
                    <td>Montant H.T</td>
                    <td>{{ number_format($tva->total_ht, 2) }}</td>
                </tr>
                <tr>
                    <td>TVA</td>
                    <td>{{ number_format($tva->tva, 2) }}</td>
                </tr>
                <tr>
                    <td>Montant T.T.C</td>
                    <td>{{ number_format($tva->montant_ttc, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer-info">
            ICE : {{ $settings['ice'] ?? '---' }} | RC : {{ $settings['rc'] ?? '---' }} |
            TP : {{ $tva->tp_number  ?? '---' }} | IF : {{ $tva->nif_number ?? '---' }}<br>
            MERCI POUR VOTRE CONFIANCE - CONTACT : {{ $settings['company_email'] ?? '---' }}
        </div>
    </div>
</body>
</html>
