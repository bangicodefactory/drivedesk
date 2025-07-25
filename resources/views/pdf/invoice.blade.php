<!DOCTYPE html>
<html lang="en">
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
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
            background: white;
            padding: 15px;
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 15px;
            height: fit-content;
        }

        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            flex-direction: column;
        }

        .company-logo {
        width: 120px;
        height: 60px;
        border: 2px solid #000;
        border-radius: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 10px;
        text-align: center;
        margin-bottom: 10px;
        margin-left: auto;   /* Add this */
        margin-right: auto;  /* Add this */
    }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
            color: #000;
        }

        .company-details {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    gap: 20px;
    container: flex; /* Ensure flexbox is applied */
}

        .company-info {
            width: 48%;
            border: 2px solid #000;
            padding: 8px;
            background: #ffffffff;
        }

        .company-info h3 {
            color: red;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .company-info p {
            margin: 1px 0;
            font-size: 10px;
        }

        .client-box {
            width: 48%;
            border: 2px solid #000;
            padding: 8px;
            height: 80px;
        }

        .client-box strong {
            font-size: 11px;
        }

        .invoice-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .meta-table {
            border: 2px solid #000;
            border-collapse: collapse;
        }

        .meta-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
        }

        .meta-table td:first-child {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .items-table {
            width: 100%;
            border: 2px solid #000;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-size: 11px;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .items-table td:first-child {
            text-align: left;
            width: 50%;
        }

        .bottom-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .payment-terms {
            width: 60%;
            font-size: 10px;
            line-height: 1.4;
        }

        .totals-table {
            border: 2px solid #000;
            border-collapse: collapse;
        }

        .totals-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
        }

        .totals-table td:first-child {
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: left;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .footer-info {
            margin-top: 15px;
            font-size: 9px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        @media print {
            body {
                padding: 0;
                font-size: 11px;
            }
            
            .invoice-container {
                border: 2px solid #000;
                padding: 10px;
                margin: 0;
                max-width: none;
            }

            .header {
                margin-bottom: 10px;
            }

            .company-details {
                margin-bottom: 10px;
            }

            .invoice-meta {
                margin-bottom: 10px;
            }

            .items-table {
                margin-bottom: 10px;
            }

            .bottom-section {
                margin-top: 10px;
            }

            .footer-info {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-logo">
                TECH<br>SOLUTIONS<br>INC.
            </div>
            <div class="invoice-title">FACTURE</div>
        </div>

        <div class="company-details">
            <div class="company-info">
                <h3>TECHSOLUTIONS INC.</h3>
                <p>ADDRESS: 123 Business Avenue</p>
                <p>CITY: New York, NY 10001</p>
                <p>PHONE: (555) 123-4567</p>
                <p>EMAIL: billing@techsolutions.com</p>
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
                    <td>Reference:</td>
                    <td>WEB-DEV</td>
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
            ICE: 002851390001S | RC: 50487 | TP: 51405652 | NIF: 0606843<br>
            THANK YOU FOR YOUR BUSINESS - QUESTIONS? CONTACT: billing@techsolutions.com
        </div>
    </div>
</body>

</html>
