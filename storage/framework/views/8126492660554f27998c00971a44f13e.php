

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #567</title>
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
            height: 80px;
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

        .client-box p {
            margin: 1px 0;
            font-size: 10px;
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
        <!-- Header -->
        <div class="header">
            <div class="company-logo">
                TECH<br>SOLUTIONS<br>INC.
            </div>
            <div class="invoice-title">INVOICE</div>
        </div>

        <!-- Company and Client Details -->
        <div class="company-details">
            <div class="company-info">
                <h3>TECHSOLUTIONS INC.</h3>
                <p>ADDRESS: 123 Business Avenue</p>
                <p>CITY: New York, NY 10001</p>
                <p>PHONE: (555) 123-4567</p>
                <p>EMAIL: billing@techsolutions.com</p>
            </div>
            <div class="client-box">
                <strong>CLIENT: ABC CORPORATION</strong><br>
                <p><strong>Contact:</strong> John Smith</p>
                <p><strong>Address:</strong> 456 Client Street</p>
                <p>Los Angeles, CA 90210</p>
                <p><strong>Email:</strong> john.smith@abccorp.com</p>
            </div>
        </div>

        <!-- Invoice Meta Information -->
        <div class="invoice-meta">
            <table class="meta-table">
                <tr>
                    <td>DATE:</td>
                    <td>15/01/2024</td>
                </tr>
                <tr>
                    <td>INVOICE N°:</td>
                    <td>567</td>
                </tr>
                <tr>
                    <td>Reference:</td>
                    <td>WEB-DEV</td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
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
                <tr>
                    <td>Website Development Package</td>
                    <td>1.00</td>
                    <td>2500.00</td>
                    <td>2500.00</td>
                </tr>
                <tr>
                    <td>UI/UX Design Services</td>
                    <td>20.00</td>
                    <td>75.00</td>
                    <td>1500.00</td>
                </tr>
                <tr>
                    <td>Database Setup & Configuration</td>
                    <td>1.00</td>
                    <td>800.00</td>
                    <td>800.00</td>
                </tr>
                <tr>
                    <td>SEO Optimization</td>
                    <td>10.00</td>
                    <td>100.00</td>
                    <td>1000.00</td>
                </tr>
                <tr>
                    <td>Testing & Quality Assurance</td>
                    <td>15.00</td>
                    <td>60.00</td>
                    <td>900.00</td>
                </tr>
                <tr style="height: 40px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <div class="payment-terms">
                <p><strong>Payment Terms:</strong> Net 30 days</p>
                <p><strong>Payment due by:</strong> February 15, 2024</p>
                <p><strong>Late fees:</strong> 1.5% per month</p>
                <br>
                <p><strong>Bank Details:</strong></p>
                <p>Account: TechSolutions Inc.</p>
                <p>Number: 1234567890</p>
                <p>Routing: 987654321</p>
                <br>
                <p><strong>Signature:</strong></p>
                <div style="height: 30px; border-bottom: 1px solid #000; width: 200px; margin-top: 10px;"></div>
            </div>
            
            <table class="totals-table">
                <tr>
                    <td>Montant HT</td>
                    <td>6700.00</td>
                </tr>
                <tr>
                    <td>TVA</td>
                    <td>569.50</td>
                </tr>
                <tr>
                    <td>Montant TTC</td>
                    <td>7269.50</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-info">
            ICE: 002851390001S | RC: 50487 | TP: 51405652 | NIF: 0606843<br>
            THANK YOU FOR YOUR BUSINESS - QUESTIONS? CONTACT: billing@techsolutions.com
        </div>
    </div>
</body>
</html><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/pdf/invoice.blade.php ENDPATH**/ ?>