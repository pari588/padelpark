<?php
// Simple test script to check if products endpoint works
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../../../core/core.inc.php");

// Test direct database query
$DB->vals = array();
$DB->types = "";
$DB->sql = "SELECT p.productID, p.productSKU, p.productName, p.hsnCode, p.basePrice, p.gstRate, p.uom
            FROM " . $DB->pre . "product p
            WHERE p.status=1 AND p.isActive=1
            ORDER BY p.productName
            LIMIT 10";
$products = $DB->dbRows();

echo "<h2>Direct Database Query Test</h2>";
echo "<p>Found " . count($products) . " products</p>";
echo "<pre>";
print_r($products);
echo "</pre>";

// Test with search
$search = "rac";
$DB->vals = array("%$search%", "%$search%");
$DB->types = "ss";
$DB->sql = "SELECT p.productID, p.productSKU, p.productName, p.hsnCode, p.basePrice, p.gstRate, p.uom
            FROM " . $DB->pre . "product p
            WHERE p.status=1 AND p.isActive=1
            AND (p.productSKU LIKE ? OR p.productName LIKE ?)
            ORDER BY p.productName
            LIMIT 10";
$searchProducts = $DB->dbRows();

echo "<h2>Search Test (searching for 'rac')</h2>";
echo "<p>Found " . count($searchProducts) . " products</p>";
echo "<pre>";
print_r($searchProducts);
echo "</pre>";
?>
