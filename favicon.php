<?php
// Favicon handler to prevent 404 errors
header('Content-Type: image/x-icon');
header('Cache-Control: public, max-age=86400'); // Cache for 24 hours

// Create a simple 16x16 transparent icon
$icon_data = pack('H*', 
    '00000100010010100000010020006804000016000000' .
    '00000000000000000000000000000000000000000000' .
    '00000000000000000000000000000000000000000000' .
    '00000000000000000000000000000000000000000000'
);

echo $icon_data;
?>
