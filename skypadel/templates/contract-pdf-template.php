<?php
/**
 * Sky Padel India - Legal Contract PDF Template
 * Indian Legal Document Style with Stamp Paper Aesthetic
 *
 * Usage: Include this file and call renderContractPDF($contractData)
 */

function renderContractPDF($contract, $milestones = [], $company = []) {
    // Default company details
    $company = array_merge([
        'name' => 'SKY PADEL INDIA PRIVATE LIMITED',
        'address' => 'Corporate Office Address, City, State - PIN',
        'cin' => 'U74999MH2024PTC000000',
        'gstin' => '27XXXXX1234X1ZX',
        'pan' => 'XXXXX0000X',
        'email' => 'contracts@skypadelindia.com',
        'phone' => '+91 00000 00000'
    ], $company);

    // Format currency
    $formatMoney = function($amount) {
        return '₹ ' . number_format((float)$amount, 2);
    };

    // Convert number to words (Indian system)
    $numberToWords = function($number) {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $number = (int)$number;
        if ($number < 20) return $ones[$number];
        if ($number < 100) return $tens[(int)($number/10)] . ($number % 10 ? ' ' . $ones[$number % 10] : '');
        if ($number < 1000) return $ones[(int)($number/100)] . ' Hundred' . ($number % 100 ? ' and ' . $numberToWords($number % 100) : '');
        if ($number < 100000) return $numberToWords((int)($number/1000)) . ' Thousand' . ($number % 1000 ? ' ' . $numberToWords($number % 1000) : '');
        if ($number < 10000000) return $numberToWords((int)($number/100000)) . ' Lakh' . ($number % 100000 ? ' ' . $numberToWords($number % 100000) : '');
        return $numberToWords((int)($number/10000000)) . ' Crore' . ($number % 10000000 ? ' ' . $numberToWords($number % 10000000) : '');
    };

    $amountInWords = $numberToWords((int)$contract['contractAmount']) . ' Rupees Only';
    $contractDate = date('jS F, Y', strtotime($contract['contractDate']));
    $contractYear = date('Y', strtotime($contract['contractDate']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Agreement - <?php echo htmlspecialchars($contract['contractNo']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Indian Stamp Paper Color Palette */
            --color-stamp-green: #1a5c3a;
            --color-stamp-green-dark: #0f3d26;
            --color-stamp-green-light: #2d7a52;
            --color-parchment: #faf6ed;
            --color-parchment-dark: #f0e9d8;
            --color-gold: #b8860b;
            --color-gold-light: #d4a84b;
            --color-seal-red: #8b2323;
            --color-ink: #1a1a1a;
            --color-ink-light: #4a4a4a;
            --color-border: #c9b896;

            /* Typography */
            --font-display: 'Cormorant Garamond', Georgia, serif;
            --font-body: 'Source Serif 4', Georgia, serif;
            --font-mono: 'JetBrains Mono', monospace;

            /* Spacing */
            --page-margin: 0.75in;
            --section-gap: 1.5rem;
        }

        @page {
            size: A4;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            font-size: 11pt;
            line-height: 1.6;
            color: var(--color-ink);
            background: var(--color-parchment);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .contract-document {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: var(--color-parchment);
            position: relative;
        }

        /* Ornate Border Frame */
        .border-frame {
            position: absolute;
            inset: 12mm;
            border: 2px solid var(--color-stamp-green);
            pointer-events: none;
        }

        .border-frame::before {
            content: '';
            position: absolute;
            inset: 3px;
            border: 1px solid var(--color-stamp-green-light);
        }

        .border-frame::after {
            content: '';
            position: absolute;
            inset: 7px;
            border: 1px dashed var(--color-border);
        }

        /* Corner Ornaments */
        .corner-ornament {
            position: absolute;
            width: 24px;
            height: 24px;
            border: 2px solid var(--color-stamp-green);
        }

        .corner-ornament.top-left {
            top: -2px;
            left: -2px;
            border-right: none;
            border-bottom: none;
        }

        .corner-ornament.top-right {
            top: -2px;
            right: -2px;
            border-left: none;
            border-bottom: none;
        }

        .corner-ornament.bottom-left {
            bottom: -2px;
            left: -2px;
            border-right: none;
            border-top: none;
        }

        .corner-ornament.bottom-right {
            bottom: -2px;
            right: -2px;
            border-left: none;
            border-top: none;
        }

        .page-content {
            padding: 18mm 20mm;
            position: relative;
            z-index: 1;
        }

        /* Document Header */
        .document-header {
            text-align: center;
            margin-bottom: var(--section-gap);
        }

        .contract-title {
            font-family: var(--font-display);
            font-size: 22pt;
            font-weight: 700;
            color: var(--color-stamp-green-dark);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 8px;
            text-shadow: 1px 1px 0 rgba(255,255,255,0.8);
        }

        .contract-number {
            font-family: var(--font-mono);
            font-size: 10pt;
            color: var(--color-stamp-green);
            letter-spacing: 0.1em;
            padding: 6px 16px;
            background: var(--color-parchment-dark);
            border: 1px solid var(--color-border);
            display: inline-block;
            margin-bottom: 8px;
        }

        .contract-date {
            font-family: var(--font-display);
            font-size: 11pt;
            color: var(--color-ink-light);
        }

        /* Parties Section */
        .parties-section {
            margin-bottom: var(--section-gap);
            padding: var(--section-gap);
            background: linear-gradient(180deg, rgba(26, 92, 58, 0.03) 0%, transparent 100%);
            border: 1px solid var(--color-border);
            border-radius: 4px;
        }

        .party-block {
            margin-bottom: 1.25rem;
        }

        .party-block:last-child {
            margin-bottom: 0;
        }

        .party-label {
            font-family: var(--font-display);
            font-size: 9pt;
            font-weight: 600;
            color: var(--color-stamp-green);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .party-label::before {
            content: '';
            width: 8px;
            height: 8px;
            background: var(--color-gold);
            transform: rotate(45deg);
        }

        .party-name {
            font-family: var(--font-display);
            font-size: 13pt;
            font-weight: 600;
            color: var(--color-ink);
            margin-bottom: 4px;
        }

        .party-details {
            font-size: 10pt;
            color: var(--color-ink-light);
            line-height: 1.5;
        }

        .party-details span {
            display: block;
        }

        .party-connector {
            text-align: center;
            font-family: var(--font-display);
            font-style: italic;
            color: var(--color-ink-light);
            margin: 1rem 0;
            font-size: 10pt;
        }

        /* Recitals */
        .recitals {
            margin-bottom: var(--section-gap);
        }

        .section-heading {
            font-family: var(--font-display);
            font-size: 12pt;
            font-weight: 600;
            color: var(--color-stamp-green-dark);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 1rem;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-heading::before {
            content: '§';
            font-size: 14pt;
            color: var(--color-gold);
        }

        .whereas-clause {
            margin-bottom: 0.75rem;
            text-align: justify;
            text-indent: 2em;
        }

        .whereas-clause strong {
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--color-stamp-green);
        }

        /* Contract Value Box */
        .value-box {
            background: linear-gradient(135deg, var(--color-stamp-green) 0%, var(--color-stamp-green-dark) 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            margin: var(--section-gap) 0;
            border-radius: 4px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: center;
            box-shadow: 0 4px 12px rgba(26, 92, 58, 0.3);
        }

        .value-label {
            font-family: var(--font-display);
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            opacity: 0.9;
            margin-bottom: 4px;
            color: white;
        }

        .value-amount {
            font-family: var(--font-mono);
            font-size: 18pt;
            font-weight: 500;
            color: white;
        }

        .value-words {
            font-family: var(--font-display);
            font-size: 10pt;
            font-style: italic;
            opacity: 0.9;
            color: white;
            margin-top: 4px;
        }

        .value-badge {
            background: var(--color-gold);
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-align: center;
        }

        .value-badge-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .value-badge-amount {
            font-family: var(--font-mono);
            font-size: 12pt;
            font-weight: 600;
        }

        /* Terms & Clauses */
        .terms-section {
            margin-bottom: var(--section-gap);
        }

        .clause {
            margin-bottom: 1rem;
            text-align: justify;
        }

        .clause-number {
            font-family: var(--font-mono);
            font-size: 9pt;
            color: var(--color-stamp-green);
            font-weight: 500;
            margin-right: 8px;
        }

        .clause-title {
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--color-ink);
        }

        .sub-clause {
            margin-left: 2em;
            margin-top: 0.5rem;
        }

        .sub-clause-marker {
            font-family: var(--font-mono);
            font-size: 9pt;
            color: var(--color-ink-light);
            margin-right: 6px;
        }

        /* Payment Schedule Table */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 10pt;
        }

        .schedule-table th {
            background: var(--color-stamp-green);
            color: white;
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 10px 12px;
            text-align: left;
            border: 1px solid var(--color-stamp-green-dark);
        }

        .schedule-table th:last-child {
            text-align: right;
        }

        .schedule-table td {
            padding: 10px 12px;
            border: 1px solid var(--color-border);
            background: white;
        }

        .schedule-table td:last-child {
            text-align: right;
            font-family: var(--font-mono);
            font-weight: 500;
        }

        .schedule-table tr:nth-child(even) td {
            background: var(--color-parchment);
        }

        .schedule-table tfoot td {
            background: var(--color-parchment-dark) !important;
            font-weight: 600;
            border-top: 2px solid var(--color-stamp-green);
        }

        /* Signature Section */
        .signature-section {
            margin-top: 2rem;
            page-break-inside: avoid;
        }

        .signature-intro {
            text-align: center;
            font-family: var(--font-display);
            font-style: italic;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--color-parchment-dark);
            border: 1px solid var(--color-border);
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .signature-block {
            padding: 1.25rem;
            border: 1px solid var(--color-border);
            background: white;
        }

        .signature-block-title {
            font-family: var(--font-display);
            font-size: 10pt;
            font-weight: 600;
            color: var(--color-stamp-green);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1rem;
            padding-bottom: 6px;
            border-bottom: 1px dashed var(--color-border);
        }

        .signature-line {
            border-bottom: 1px solid var(--color-ink);
            height: 50px;
            margin-bottom: 8px;
            position: relative;
        }

        .signature-line::after {
            content: 'Signature';
            position: absolute;
            bottom: -18px;
            left: 0;
            font-size: 8pt;
            color: var(--color-ink-light);
            font-style: italic;
        }

        .signature-details {
            margin-top: 1.5rem;
        }

        .signature-field {
            display: flex;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .signature-field-label {
            width: 80px;
            color: var(--color-ink-light);
        }

        .signature-field-value {
            flex: 1;
            border-bottom: 1px dotted var(--color-border);
            padding-bottom: 2px;
        }

        /* Witness Section */
        .witness-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--color-border);
        }

        .witness-title {
            font-family: var(--font-display);
            font-size: 11pt;
            font-weight: 600;
            color: var(--color-stamp-green-dark);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1rem;
            text-align: center;
        }

        .witness-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .witness-block {
            padding: 1rem;
            background: var(--color-parchment);
            border: 1px dashed var(--color-border);
        }

        .witness-number {
            font-family: var(--font-mono);
            font-size: 9pt;
            color: var(--color-stamp-green);
            margin-bottom: 8px;
        }

        /* Official Seal Area */
        .seal-area {
            text-align: center;
            margin: 2rem 0;
            padding: 1.5rem;
            border: 2px dashed var(--color-border);
            background: rgba(184, 134, 11, 0.05);
        }

        .seal-placeholder {
            width: 100px;
            height: 100px;
            border: 2px solid var(--color-gold);
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-size: 8pt;
            color: var(--color-gold);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .seal-text {
            font-size: 9pt;
            color: var(--color-ink-light);
            font-style: italic;
        }

        /* Footer */
        .document-footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 2px solid var(--color-stamp-green);
            text-align: center;
            font-size: 9pt;
            color: var(--color-ink-light);
        }

        .jurisdiction {
            font-family: var(--font-display);
            font-style: italic;
            margin-bottom: 8px;
        }

        .page-number {
            font-family: var(--font-mono);
            font-size: 8pt;
            color: var(--color-stamp-green);
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
            }

            .contract-document {
                margin: 0;
                box-shadow: none;
            }
        }

        /* Page Break Helpers */
        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="contract-document">
        <!-- Border Frame -->
        <div class="border-frame">
            <div class="corner-ornament top-left"></div>
            <div class="corner-ornament top-right"></div>
            <div class="corner-ornament bottom-left"></div>
            <div class="corner-ornament bottom-right"></div>
        </div>

        <div class="page-content">
            <!-- Document Header -->
            <div class="document-header">
                <h1 class="contract-title">Contract Agreement</h1>
                <div class="contract-number"><?php echo htmlspecialchars($contract['contractNo']); ?></div>
                <div class="contract-date">Executed on this <?php echo $contractDate; ?></div>
            </div>

            <!-- Parties Section -->
            <div class="parties-section">
                <div class="party-block">
                    <div class="party-label">First Party (Service Provider)</div>
                    <div class="party-name"><?php echo htmlspecialchars($company['name']); ?></div>
                    <div class="party-details">
                        <span>A company incorporated under the Companies Act, 2013</span>
                        <span>CIN: <?php echo htmlspecialchars($company['cin']); ?></span>
                        <span>GSTIN: <?php echo htmlspecialchars($company['gstin']); ?></span>
                        <span>Registered Office: <?php echo htmlspecialchars($company['address']); ?></span>
                    </div>
                </div>

                <div class="party-connector">— AND —</div>

                <div class="party-block">
                    <div class="party-label">Second Party (Client)</div>
                    <div class="party-name"><?php echo htmlspecialchars($contract['clientName']); ?></div>
                    <div class="party-details">
                        <span>Address: <?php echo htmlspecialchars($contract['siteAddress'] ?? ''); ?></span>
                        <span><?php echo htmlspecialchars($contract['siteCity'] ?? ''); ?>, <?php echo htmlspecialchars($contract['siteState'] ?? ''); ?></span>
                        <span>Email: <?php echo htmlspecialchars($contract['clientEmail']); ?> | Phone: <?php echo htmlspecialchars($contract['clientPhone']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Recitals -->
            <div class="recitals">
                <h2 class="section-heading">Recitals</h2>
                <p class="whereas-clause">
                    <strong>WHEREAS</strong> the First Party is engaged in the business of designing, manufacturing, supplying, and installing professional padel tennis courts and related sports infrastructure;
                </p>
                <p class="whereas-clause">
                    <strong>AND WHEREAS</strong> the Second Party has approached the First Party for the design, supply, and installation of padel court(s) at the site specified herein, as per the specifications agreed upon;
                </p>
                <p class="whereas-clause">
                    <strong>AND WHEREAS</strong> both parties have agreed to enter into this Contract Agreement on the terms and conditions set forth hereinafter;
                </p>
                <p class="whereas-clause">
                    <strong>NOW, THEREFORE</strong>, in consideration of the mutual covenants and agreements herein contained, the parties agree as follows:
                </p>
            </div>

            <!-- Contract Value -->
            <div class="value-box">
                <div>
                    <div class="value-label">Total Contract Value</div>
                    <div class="value-amount"><?php echo $formatMoney($contract['contractAmount']); ?></div>
                    <div class="value-words">(<?php echo $amountInWords; ?>)</div>
                </div>
                <div class="value-badge">
                    <div class="value-badge-label">Advance Payment</div>
                    <div class="value-badge-amount"><?php echo $formatMoney($contract['advanceAmount'] ?? 0); ?></div>
                </div>
            </div>

            <!-- Scope of Work -->
            <?php if (!empty($contract['scopeOfWork'])): ?>
            <div class="terms-section no-break">
                <h2 class="section-heading">Scope of Work</h2>
                <div class="clause">
                    <span class="clause-number">1.</span>
                    <span class="clause-title">Project Description:</span>
                    <?php echo nl2br(htmlspecialchars($contract['scopeOfWork'])); ?>
                </div>
                <div class="clause">
                    <span class="clause-number">2.</span>
                    <span class="clause-title">Configuration:</span>
                    <?php echo htmlspecialchars($contract['courtConfiguration'] ?? 'As per quotation specifications'); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Payment Schedule -->
            <?php if (!empty($milestones)): ?>
            <div class="terms-section no-break">
                <h2 class="section-heading">Payment Schedule</h2>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th>Milestone</th>
                            <th style="width: 12%;">Percentage</th>
                            <th style="width: 12%;">Due</th>
                            <th style="width: 18%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($milestones as $i => $m): ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $i + 1; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($m['milestoneName']); ?></strong>
                                <?php if (!empty($m['milestoneDescription'])): ?>
                                <br><small style="color: var(--color-ink-light);"><?php echo htmlspecialchars($m['milestoneDescription']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;"><?php echo $m['paymentPercentage']; ?>%</td>
                            <td style="text-align: center;"><?php echo $m['dueAfterDays'] > 0 ? $m['dueAfterDays'] . ' days' : 'On Order'; ?></td>
                            <td><?php echo $formatMoney($m['paymentAmount']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Total Contract Value</strong></td>
                            <td><?php echo $formatMoney($contract['contractAmount']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>

            <!-- Terms & Conditions -->
            <?php if (!empty($contract['termsAndConditions'])): ?>
            <div class="terms-section">
                <h2 class="section-heading">Terms &amp; Conditions</h2>
                <p><?php echo nl2br(htmlspecialchars($contract['termsAndConditions'])); ?></p>
            </div>
            <?php endif; ?>

            <!-- Payment Terms -->
            <?php if (!empty($contract['paymentTerms'])): ?>
            <div class="terms-section no-break">
                <h2 class="section-heading">Payment Terms</h2>
                <div class="clause">
                    <?php echo nl2br(htmlspecialchars($contract['paymentTerms'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-intro">
                    <strong>IN WITNESS WHEREOF</strong>, the parties hereto have executed this Agreement as of the date first above written,
                    having read, understood, and agreed to all terms and conditions contained herein.
                </div>

                <div class="signature-grid">
                    <!-- First Party Signature -->
                    <div class="signature-block">
                        <div class="signature-block-title">For &amp; On Behalf of First Party</div>
                        <div class="signature-line"></div>
                        <div class="signature-details">
                            <div class="signature-field">
                                <span class="signature-field-label">Name:</span>
                                <span class="signature-field-value"></span>
                            </div>
                            <div class="signature-field">
                                <span class="signature-field-label">Designation:</span>
                                <span class="signature-field-value"></span>
                            </div>
                            <div class="signature-field">
                                <span class="signature-field-label">Date:</span>
                                <span class="signature-field-value"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Second Party Signature -->
                    <div class="signature-block">
                        <div class="signature-block-title">For &amp; On Behalf of Second Party</div>
                        <div class="signature-line"></div>
                        <div class="signature-details">
                            <div class="signature-field">
                                <span class="signature-field-label">Name:</span>
                                <span class="signature-field-value"><?php echo htmlspecialchars($contract['signedBy'] ?? ''); ?></span>
                            </div>
                            <div class="signature-field">
                                <span class="signature-field-label">Date:</span>
                                <span class="signature-field-value"><?php echo !empty($contract['signedAt']) ? date('d/m/Y', strtotime($contract['signedAt'])) : ''; ?></span>
                            </div>
                            <?php if (!empty($contract['signatureMethod'])): ?>
                            <div class="signature-field">
                                <span class="signature-field-label">Verified:</span>
                                <span class="signature-field-value"><?php echo htmlspecialchars($contract['signatureMethod']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Seal Area -->
                <div class="seal-area no-break">
                    <div class="seal-placeholder">Company<br>Seal</div>
                    <div class="seal-text">Affix Company Seal / Stamp Here</div>
                </div>

                <!-- Witness Section -->
                <div class="witness-section no-break">
                    <h3 class="witness-title">Witnesses</h3>
                    <div class="witness-grid">
                        <div class="witness-block">
                            <div class="witness-number">Witness 1</div>
                            <div class="signature-field">
                                <span class="signature-field-label">Name:</span>
                                <span class="signature-field-value"></span>
                            </div>
                            <div class="signature-field">
                                <span class="signature-field-label">Address:</span>
                                <span class="signature-field-value"></span>
                            </div>
                            <div class="signature-field">
                                <span class="signature-field-label">Signature:</span>
                                <span class="signature-field-value"></span>
                            </div>
                        </div>
                        <div class="witness-block">
                            <div class="witness-number">Witness 2</div>
                            <div class="signature-field">
                                <span class="signature-field-label">Name:</span>
                                <span class="signature-field-value"></span>
                            </div>
                            <div class="signature-field">
                                <span class="signature-field-label">Address:</span>
                                <span class="signature-field-value"></span>
                            </div>
                            <div class="signature-field">
                                <span class="signature-field-label">Signature:</span>
                                <span class="signature-field-value"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="document-footer">
                <div class="jurisdiction">
                    This Agreement shall be governed by and construed in accordance with the laws of India.
                    Any disputes arising out of this Agreement shall be subject to the exclusive jurisdiction of the courts at Maharashtra.
                </div>
                <div class="page-number">Page 1 of 1 | <?php echo htmlspecialchars($contract['contractNo']); ?></div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
}

// Helper function to render and output PDF (requires a PDF library like mPDF or TCPDF)
function generateContractPDF($contract, $milestones = [], $company = [], $outputPath = null) {
    // Start output buffering
    ob_start();
    renderContractPDF($contract, $milestones, $company);
    $html = ob_get_clean();

    // If mPDF is available, generate PDF
    if (class_exists('Mpdf\Mpdf')) {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'default_font' => 'dejavusans'
        ]);

        $mpdf->WriteHTML($html);

        if ($outputPath) {
            $mpdf->Output($outputPath, 'F');
            return $outputPath;
        } else {
            $mpdf->Output('Contract-' . $contract['contractNo'] . '.pdf', 'D');
            return true;
        }
    }

    // Fallback: return HTML for browser printing
    return $html;
}
?>
