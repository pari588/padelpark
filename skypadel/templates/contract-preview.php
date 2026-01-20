<?php
/**
 * Contract PDF Template Preview
 * Preview the Indian Legal Contract Template with sample data
 */

require_once __DIR__ . '/contract-pdf-template.php';

// Sample contract data for preview
$sampleContract = [
    'contractNo' => 'SPI/CON/2025/00142',
    'contractDate' => date('Y-m-d'),
    'contractAmount' => 3250000.00,
    'advanceAmount' => 1625000.00,
    'advancePercentage' => 50,
    'clientName' => 'Prestige Sports & Recreation Pvt. Ltd.',
    'clientEmail' => 'projects@prestigesports.in',
    'clientPhone' => '+91 98765 43210',
    'siteAddress' => '142, Sports Complex Road, Whitefield',
    'siteCity' => 'Bengaluru',
    'siteState' => 'Karnataka',
    'courtConfiguration' => '2 Professional Panoramic Padel Courts (20m x 10m each)',
    'signedBy' => '',
    'signedAt' => '',
    'signatureMethod' => '',
    'scopeOfWork' => 'Design, supply, and installation of 2 (Two) professional panoramic padel tennis courts with the following specifications:

• Court Dimensions: 20m x 10m (International Standards)
• Structure: Hot-dip galvanized steel frame with powder coating
• Glass Panels: 12mm tempered safety glass (rear and side walls)
• Mesh Fencing: Galvanized welded mesh (4mm wire, 50x50mm aperture)
• Playing Surface: Premium artificial turf with silica sand infill
• LED Lighting: Professional grade LED floodlights (500 lux average)
• Accessories: Professional nets, posts, and court accessories
• Civil Works: Foundation preparation and finishing as per specifications',
    'termsAndConditions' => 'The First Party shall complete the installation within 45-60 working days from the date of advance payment receipt.
All materials used shall be of premium quality and as per international padel federation standards.
The Second Party shall ensure unobstructed site access and necessary permissions before installation begins.
Any changes to the approved design/specifications shall be subject to additional charges and timeline revision.
The warranty period shall be 5 years for structural components and 2 years for other items from the date of completion.
Force Majeure: Neither party shall be liable for delays due to circumstances beyond their reasonable control.
The First Party reserves the right to subcontract specific portions of work while maintaining overall responsibility.
All intellectual property rights related to designs remain with the First Party.',
    'paymentTerms' => 'All payments shall be made via NEFT/RTGS/Cheque in favor of "Sky Padel India Private Limited". GST @ 18% is applicable and included in the contract value. TDS if applicable shall be deducted at source as per Income Tax provisions. Payment delays beyond 7 days from due date shall attract interest @ 1.5% per month.'
];

$sampleMilestones = [
    [
        'milestoneName' => 'Advance Payment',
        'milestoneDescription' => 'On signing of contract agreement',
        'paymentPercentage' => 50,
        'paymentAmount' => 1625000.00,
        'dueAfterDays' => 0
    ],
    [
        'milestoneName' => 'Material Dispatch',
        'milestoneDescription' => 'Upon dispatch of materials from factory',
        'paymentPercentage' => 25,
        'paymentAmount' => 812500.00,
        'dueAfterDays' => 21
    ],
    [
        'milestoneName' => 'Installation Complete',
        'milestoneDescription' => 'Upon completion of installation at site',
        'paymentPercentage' => 20,
        'paymentAmount' => 650000.00,
        'dueAfterDays' => 45
    ],
    [
        'milestoneName' => 'Final Handover',
        'milestoneDescription' => 'Upon final inspection and handover',
        'paymentPercentage' => 5,
        'paymentAmount' => 162500.00,
        'dueAfterDays' => 60
    ]
];

$companyDetails = [
    'name' => 'SKY PADEL INDIA PRIVATE LIMITED',
    'address' => '501, Business Hub, Andheri East, Mumbai, Maharashtra - 400069',
    'cin' => 'U74999MH2024PTC421234',
    'gstin' => '27AADCS1234A1ZF',
    'pan' => 'AADCS1234A',
    'email' => 'contracts@skypadelindia.com',
    'phone' => '+91 22 4567 8900'
];

// Render the template
renderContractPDF($sampleContract, $sampleMilestones, $companyDetails);
?>
