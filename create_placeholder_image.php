<?php
// Create a simple placeholder image
$width = 300;
$height = 200;

// Create image
$image = imagecreatetruecolor($width, $height);

// Define colors
$bg_color = imagecolorallocate($image, 245, 245, 245); // Light gray background
$text_color = imagecolorallocate($image, 128, 128, 128); // Gray text
$border_color = imagecolorallocate($image, 200, 200, 200); // Border color

// Fill background
imagefill($image, 0, 0, $bg_color);

// Draw border
imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

// Add text
$text = "No Image";
$font_size = 5;
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font_size, $x, $y, $text, $text_color);

// Output image
header('Content-Type: image/jpeg');
header('Content-Disposition: inline; filename="placeholder.jpg"');
imagejpeg($image, 'assets/images/placeholder.jpg', 90);

// Free memory
imagedestroy($image);

echo "Placeholder image created successfully at assets/images/placeholder.jpg";
?>
