<?php
/**
 * Download Bulk Upload CSV Template
 */

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="product_bulk_upload_template.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// CSV headers
$headers = [
    'product_title',
    'category_name',
    'brand_name',
    'product_price',
    'product_stock',
    'product_desc',
    'product_keywords'
];

// Sample data
$sample_data = [
    [
        'Panadol Extra Tablets 500mg Pack of 24',
        'Pain Relief',
        'Panadol',
        '18.50',
        '100',
        'Fast acting pain relief for headaches, fever, and body aches',
        'panadol,pain,headache,fever,tablets'
    ],
    [
        'Vitamin C 1000mg Tablets',
        'Vitamins',
        'Centrum',
        '45.00',
        '50',
        'Immune system support and antioxidant protection',
        'vitamin c,immune,health,supplements'
    ],
    [
        'Amoxicillin 500mg Capsules',
        'Antibiotics',
        'Generic',
        '35.00',
        '75',
        'Antibiotic for bacterial infections',
        'antibiotic,infection,amoxicillin'
    ]
];

// Open output stream
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, $headers);

// Write sample data
foreach ($sample_data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
