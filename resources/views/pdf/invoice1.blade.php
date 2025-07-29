<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  line-height: 1.6;
  color: #333;
  background-color: white;
}

/* Print Styles for A4 */
@media print {
  body {
    background-color: white;
    font-size: 11pt;
    line-height: 1.3;
  }

  .invoice-container {
    width: 210mm;
    margin: 0;
    padding: 10mm;
    box-shadow: none;
    background: white;
    min-height: 280mm;
    position: relative;
  }

  /* Ensure proper page breaks */
  .invoice-header,
  .billing-section,
  .invoice-items,
  .invoice-totals {
    page-break-inside: avoid;
  }

  /* Hide elements that shouldn't print */
  .no-print {
    display: none !important;
  }
}

/* Container */
.invoice-container {
  max-width: 210mm;
  margin: 10px auto;
  background: white;
  padding: 20px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  min-height: calc(100vh - 40px);
  position: relative;
}

/* Header Section */
.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 3px solid #2563eb;
}

.company-info {
  display: flex;
  align-items: flex-start;
  gap: 20px;
}

.company-logo {
  max-height: 80px;
  width: auto;
}

.company-details h1 {
  color: #2563eb;
  font-size: 24px;
  margin-bottom: 10px;
  font-weight: bold;
}

.company-details p {
  color: #666;
  font-size: 14px;
  line-height: 1.5;
}

.invoice-title {
  text-align: right;
}

.invoice-title h2 {
  font-size: 36px;
  color: #2563eb;
  margin-bottom: 15px;
  font-weight: bold;
}

.invoice-meta p {
  margin-bottom: 5px;
  font-size: 14px;
}

/* Billing Section */
.billing-section {
  display: grid;
  grid-template-columns: 1fr;
  gap: 30px;
  margin-bottom: 25px;
}

.bill-to h3,
.bill-from h3 {
  color: #2563eb;
  font-size: 16px;
  margin-bottom: 15px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.client-info p,
.vendor-info p {
  font-size: 14px;
  line-height: 1.6;
  color: #555;
}

/* Invoice Items Table */
.invoice-items {
  margin-bottom: 20px;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 15px;
}

.items-table thead {
  background-color: #2563eb;
  color: white;
}

.items-table th,
.items-table td {
  padding: 10px 8px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
  font-size: 12px;
}

.items-table th {
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
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

.items-table tbody tr:hover {
  background-color: #f1f5f9;
}

.description strong {
  color: #2563eb;
  font-size: 14px;
}

.description small {
  color: #666;
  font-size: 12px;
  display: block;
  margin-top: 5px;
}

/* Totals Section */
.invoice-totals {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 50px;
  clear: both;
  width: 100%;
  padding-right: 0;
}

.totals-table {
  width: 350px;
  margin-right: 0;
  float: right;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #e5e7eb;
}

.total-row.discount .total-value {
  color: #dc2626;
}

.total-row.grand-total {
  background-color: #2563eb;
  color: white;
  padding: 15px 20px;
  margin-top: 10px;
  border-radius: 5px;
  font-weight: bold;
  font-size: 18px;
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
  padding-top: 15px;
  border-top: 2px solid #e5e7eb;
  color: #666;
  margin-bottom: 0;
  position: absolute;
  bottom: 20px;
  left: 20px;
  right: 20px;
}

.footer-content p {
  margin-bottom: 5px;
  font-size: 14px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .invoice-container {
    margin: 10px;
    padding: 20px;
  }

  .invoice-header {
    flex-direction: column;
    gap: 20px;
  }

  .company-info {
    flex-direction: column;
    gap: 15px;
  }

  .invoice-title {
    text-align: left;
  }

  .billing-section {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .invoice-notes {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .items-table {
    font-size: 12px;
  }

  .items-table th,
  .items-table td {
    padding: 10px 5px;
  }

  .totals-table {
    width: 100%;
  }
}

/* Additional Print Optimizations */
@media print {
  .invoice-container {
    padding: 10mm !important;
    margin: 0 !important;
  }

  .invoice-header {
    margin-bottom: 15px;
    padding-bottom: 10px;
  }

  .billing-section {
    margin-bottom: 15px;
  }

  .invoice-items {
    margin-bottom: 10px;
  }

  .invoice-totals {
    margin-bottom: 20px;
    clear: both;
  }

  .payment-info {
    margin-bottom: 10px;
    background-color: #f9f9f9 !important;
    padding: 10px !important;
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
  }

  .invoice-notes {
    margin-bottom: 10px;
  }

  .invoice-footer {
    position: absolute;
    bottom: 10mm;
    left: 10mm;
    right: 10mm;
    padding-top: 10px;
    margin-bottom: 0;
    padding-bottom: 0;
  }

  /* Reduce font sizes for better fit */
  .company-details h1 {
    font-size: 20px !important;
  }

  .invoice-title h2 {
    font-size: 30px !important;
  }

  .items-table th,
  .items-table td {
    padding: 8px 6px !important;
    font-size: 11px !important;
  }

  /* Ensure colors print correctly */
  .invoice-header,
  .items-table thead,
  .total-row.grand-total,
  .payment-info {
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
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
                    <p><strong>Référence:</strong> ESPECE</p>
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

<script>
  function numberToFrench(num) {
    if (typeof num !== 'number' || num < 0 || num > 999999999999) {
        throw new Error('Le nombre doit être entre 0 et 999 999 999 999');
    }
    
    if (num === 0) return 'zéro';
    
    // Tableaux de base pour les nombres français
    const units = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'
    ];
    
    const tens = [
        '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'
    ];
    
    // Fonction pour convertir les nombres de 0 à 99
    function convertUnder100(n) {
        if (n === 0) return '';
        if (n < 20) return units[n];
        
        const ten = Math.floor(n / 10);
        const unit = n % 10;
        
        if (ten === 7) {
            // 70-79: soixante-dix, soixante-et-onze, etc.
            if (unit === 1) return 'soixante-et-onze';
            return 'soixante-' + (unit === 0 ? 'dix' : units[10 + unit]);
        } else if (ten === 8) {
            // 80-89: quatre-vingts, quatre-vingt-un, etc.
            if (unit === 0) return 'quatre-vingts';
            return 'quatre-vingt-' + units[unit];
        } else if (ten === 9) {
            // 90-99: quatre-vingt-dix, quatre-vingt-onze, etc.
            if (unit === 0) return 'quatre-vingt-dix';
            return 'quatre-vingt-' + units[10 + unit];
        } else {
            // 20-69
            if (unit === 0) return tens[ten];
            if (unit === 1 && (ten === 2 || ten === 3 || ten === 4 || ten === 5 || ten === 6)) {
                return tens[ten] + '-et-un';
            }
            return tens[ten] + '-' + units[unit];
        }
    }
    
    // Fonction pour convertir les centaines
    function convertUnder1000(n) {
        if (n === 0) return '';
        
        const hundreds = Math.floor(n / 100);
        const remainder = n % 100;
        
        let result = '';
        
        if (hundreds > 0) {
            if (hundreds === 1) {
                result = 'cent';
            } else {
                result = units[hundreds] + '-cent';
            }
            // Ajouter "s" à cent si c'est un multiple de 100 > 100
            if (remainder === 0 && hundreds > 1) {
                result += 's';
            }
        }
        
        if (remainder > 0) {
            const remainderText = convertUnder100(remainder);
            if (result) {
                result += '-' + remainderText;
            } else {
                result = remainderText;
            }
        }
        
        return result;
    }
    
    // Fonction principale de conversion
    function convert(n) {
        if (n === 0) return '';
        
        const billions = Math.floor(n / 1000000000);
        const millions = Math.floor((n % 1000000000) / 1000000);
        const thousands = Math.floor((n % 1000000) / 1000);
        const remainder = n % 1000;
        
        let result = '';
        
        // Milliards
        if (billions > 0) {
            const billionText = convertUnder1000(billions);
            result += billionText + (billions === 1 ? '-milliard' : '-milliards');
        }
        
        // Millions
        if (millions > 0) {
            if (result) result += '-';
            const millionText = convertUnder1000(millions);
            result += millionText + (millions === 1 ? '-million' : '-millions');
        }
        
        // Milliers
        if (thousands > 0) {
            if (result) result += '-';
            if (thousands === 1) {
                result += 'mille';
            } else {
                result += convertUnder1000(thousands) + '-mille';
            }
        }
        
        // Centaines, dizaines, unités
        if (remainder > 0) {
            if (result) result += '-';
            result += convertUnder1000(remainder);
        }
        
        return result;
    }
    
    return convert(Math.floor(num));
}

// Fonction de test avec exemples
function testFrenchNumbers() {
    const testCases = [
        0, 1, 11, 21, 31, 41, 51, 61, 71, 80, 81, 91, 99, 100, 101, 200, 201, 
        1000, 1001, 1100, 2000, 10000, 100000, 1000000, 1000001, 2000000, 
        1000000000, 1234567890
    ];
    
    console.log('Tests de conversion de nombres en français :');
    console.log('=' .repeat(50));
    
    testCases.forEach(num => {
        try {
            const french = numberToFrench(num);
            console.log(`${num.toLocaleString('fr-FR')} → ${french}`);
        } catch (error) {
            console.log(`${num} → Erreur: ${error.message}`);
        }
    });
}

// Exemples d'utilisation
console.log('Exemples d\'utilisation :');
console.log('numberToFrench(42) =', numberToFrench(42));
console.log('numberToFrench(80) =', numberToFrench(80));
console.log('numberToFrench(81) =', numberToFrench(81));
console.log('numberToFrench(1999) =', numberToFrench(1999));
console.log('numberToFrench(2000) =', numberToFrench(2000));

// Lancer les tests
testFrenchNumbers();
</script>

</html>
