<?php
// Test script to check if PHPMailer is working properly

// Display all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the autoloader
require 'vendor/autoload.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h2>PHPMailer Test</h2>";

// Test if PHPMailer classes exist
echo "<p>Testing PHPMailer class availability:</p>";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "<p style='color:green'>PHPMailer class exists</p>";
} else {
    echo "<p style='color:red'>PHPMailer class does not exist</p>";
}

// Try to send an email
echo "<p>Attempting to send a test email:</p>";

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'grixia400@gmail.com'; // Replace with your email
    $mail->Password   = 'fwpx upvb sjbv weve'; // Replace with your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('grixia400@gmail.com', 'Test Sender');
    $mail->addAddress('grixia400@gmail.com'); // Send to yourself for testing

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test';
    $mail->Body    = 'This is a test email to verify that PHPMailer is working correctly.';
    $mail->AltBody = 'This is a test email to verify that PHPMailer is working correctly.';

    $mail->send();
    echo "<p style='color:green'>Test message sent</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Message could not be sent. Error: {$mail->ErrorInfo}</p>";
    
    // Additional debugging
    echo "<h3>Debugging Information:</h3>";
    echo "<pre>";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Extensions loaded: " . implode(', ', get_loaded_extensions()) . "\n";
    
    // Check for required extensions
    echo "\nRequired extensions check:\n";
    echo "OpenSSL: " . (extension_loaded('openssl') ? 'Loaded' : 'Not loaded') . "\n";
    echo "PDO: " . (extension_loaded('pdo') ? 'Loaded' : 'Not loaded') . "\n";
    echo "SMTP: " . (function_exists('fsockopen') ? 'Function available' : 'Function not available') . "\n";
    echo "</pre>";
}
?> 