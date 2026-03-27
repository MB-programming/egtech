<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once '../db/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Sanitize inputs
$name    = trim(strip_tags($_POST['name'] ?? ''));
$email   = trim(strip_tags($_POST['email'] ?? ''));
$mobile  = trim(strip_tags($_POST['mobile'] ?? ''));
$service = trim(strip_tags($_POST['service'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));
$ip      = $_SERVER['REMOTE_ADDR'] ?? '';

// Validate
if (empty($name) || strlen($name) < 2) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid name.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}
if (empty($mobile) || !preg_match('/^[\+\d\s\-\(\)]{7,20}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid mobile number.']);
    exit;
}
if (empty($service)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a service name.']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        "INSERT INTO contacts (name, email, mobile, service, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$name, $email, $mobile, $service, $message, $ip]);
    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to save message. Please try again.']);
}
