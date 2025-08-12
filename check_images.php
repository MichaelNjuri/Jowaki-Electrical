<?php
// Image checker script
echo "<h2>🖼️ Image Status Checker</h2>";

$required_images = [
    'assets/images/IMG_1.jpg' => 'Electric Fence Installation',
    'assets/images/IMg-2.jpg' => 'Razor Wire on Wall', 
    'assets/images/IMG_3.jpg' => 'CCTV Camera',
    'assets/images/IMG_4.jpg' => 'Security System',
    'assets/images/IMG_5.jpg' => 'Electrical Work',
    'assets/images/IMG_6.jpg' => 'Installation Work',
    'assets/images/IMG_7.jpg' => 'Security Installation',
    'assets/images/IMG-8.jpg' => 'Electrical Installation',
    'assets/images/IMG-9.jpg' => 'Security Equipment',
    'assets/images/IMG_10.jpg' => 'Electrical Equipment',
    'assets/images/IMG_11.jpg' => 'Security System',
    'assets/images/Logo.jpg' => 'Company Logo',
    'assets/images/mpesa-logo.png' => 'M-Pesa Logo',
    'assets/images/paypal-logo.png' => 'PayPal Logo',
    'assets/images/visa-logo.jpeg' => 'Visa Logo',
    'assets/images/cctvbanner-1.jpg' => 'CCTV Banner'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Image Path</th><th>Description</th><th>Status</th><th>Size</th></tr>";

foreach ($required_images as $path => $description) {
    if (file_exists($path)) {
        $size = filesize($path);
        $size_kb = round($size / 1024, 1);
        echo "<tr style='background-color: #d4edda;'>";
        echo "<td>$path</td>";
        echo "<td>$description</td>";
        echo "<td>✅ Found</td>";
        echo "<td>{$size_kb} KB</td>";
        echo "</tr>";
    } else {
        echo "<tr style='background-color: #f8d7da;'>";
        echo "<td>$path</td>";
        echo "<td>$description</td>";
        echo "<td>❌ Missing</td>";
        echo "<td>-</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<br><h3>📋 Upload Instructions:</h3>";
echo "<ol>";
echo "<li>Upload the entire <code>assets/images/</code> folder to your server</li>";
echo "<li>Make sure all image files are in the correct location</li>";
echo "<li>Check file permissions (should be 644 for images)</li>";
echo "</ol>";

echo "<br><h3>🔧 Quick Fix:</h3>";
echo "<p>If images are missing, upload these files from your local folder:</p>";
echo "<code>C:\\Users\\USER\\OneDrive\\Desktop\\public_html\\assets\\images\\</code>";

echo "<br><br><a href='index_simple.php'>← Back to Website</a>";
?>

