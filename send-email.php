<?php
/**
 * ELITE DRIVE — Luxury Chauffeur Service
 * send-email.php — Form Email Handler
 *
 * Configuration: Update $config array below with your details.
 * Requires: PHP 7.4+, mail() function or PHPMailer (recommended)
 */

// ── Configuration ──────────────────────────────────────────────
$config = [
    'recipient_email' => 'info@elitedrive.com',  // ← CHANGE TO YOUR EMAIL
    'recipient_name'  => 'Elite Drive Enquiries',
    'sender_email'    => 'noreply@elitedrive.com', // ← CHANGE TO YOUR DOMAIN
    'sender_name'     => 'Elite Drive Website',
    'company_name'    => 'Elite Drive Chauffeur Service',
    'company_phone'   => '+44 20 1234 5678',
    'company_website' => 'https://www.elitedrive.com',
];

// ── CORS / Headers ─────────────────────────────────────────────
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// ── Sanitize Helper ────────────────────────────────────────────
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

// ── Determine Form Type ────────────────────────────────────────
$formType = isset($_POST['form_type']) ? clean($_POST['form_type']) : 'contact';

// ── Validate Common Fields ─────────────────────────────────────
$name  = clean($_POST['name']  ?? '');
$email = clean($_POST['email'] ?? '');
$phone = clean($_POST['phone'] ?? '');

if (empty($name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Name and email are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
    exit;
}

// ── Build Email Based on Form Type ─────────────────────────────
if ($formType === 'quote' || $formType === 'hero_quote') {

    $pickup      = clean($_POST['pickup']      ?? 'Not specified');
    $destination = clean($_POST['destination'] ?? 'Not specified');
    $date        = clean($_POST['date']        ?? 'Not specified');
    $time        = clean($_POST['time']        ?? 'Not specified');
    $vehicle     = clean($_POST['vehicle']     ?? 'Not specified');
    $notes       = clean($_POST['notes']       ?? 'None');
    $passengers  = clean($_POST['passengers']  ?? 'Not specified');

    $subject = "New Quote Request from {$name} — {$config['company_name']}";

    $body = buildQuoteEmail($name, $email, $phone, $pickup, $destination, $date, $time, $vehicle, $passengers, $notes, $config);
    $successMsg = "Thank you, {$name}! Your quote request has been received. We'll contact you within 2 hours.";

} else {

    $subject_input = clean($_POST['subject'] ?? 'General Enquiry');
    $message       = clean($_POST['message'] ?? '');

    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter your message.']);
        exit;
    }

    $subject = "Contact Form: {$subject_input} — {$config['company_name']}";
    $body = buildContactEmail($name, $email, $phone, $subject_input, $message, $config);
    $successMsg = "Thank you, {$name}! Your message has been received. We'll respond within 24 hours.";
}

// ── Send Email ─────────────────────────────────────────────────
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: {$config['sender_name']} <{$config['sender_email']}>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent = mail($config['recipient_email'], $subject, $body, $headers);

// Also send confirmation to user
if ($sent) {
    $confirmSubject = "Your Request Received — {$config['company_name']}";
    $confirmBody = buildConfirmationEmail($name, $config);
    $confirmHeaders  = "MIME-Version: 1.0\r\n";
    $confirmHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
    $confirmHeaders .= "From: {$config['company_name']} <{$config['sender_email']}>\r\n";
    mail($email, $confirmSubject, $confirmBody, $confirmHeaders);
}

if ($sent) {
    echo json_encode(['status' => 'success', 'message' => $successMsg]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Mail delivery failed. Please call us directly.']);
}

// ── Email Templates ────────────────────────────────────────────

function buildQuoteEmail($name, $email, $phone, $pickup, $destination, $date, $time, $vehicle, $passengers, $notes, $config) {
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f0e8;margin:0;padding:0;}
.wrap{max-width:640px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.1);}
.header{background:linear-gradient(135deg,#0A0A0A,#1A1A1A);padding:36px 40px;text-align:center;}
.header h1{font-family:Georgia,serif;color:#C9A84C;font-size:22px;letter-spacing:4px;text-transform:uppercase;margin:0 0 4px;}
.header p{color:rgba(255,255,255,0.5);font-size:11px;letter-spacing:2px;text-transform:uppercase;margin:0;}
.gold-bar{height:3px;background:linear-gradient(90deg,transparent,#C9A84C,#E8C97A,#C9A84C,transparent);}
.body{padding:40px;}
.alert-box{background:#f0e8d5;border-left:4px solid #C9A84C;padding:16px 20px;border-radius:4px;margin-bottom:28px;}
.alert-box h2{color:#0A0A0A;font-size:16px;margin:0 0 4px;}
.alert-box p{color:#8A8580;font-size:13px;margin:0;}
.section-title{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#C9A84C;font-weight:700;margin-bottom:12px;}
.detail-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(0,0,0,0.06);font-size:14px;}
.detail-label{color:#8A8580;font-weight:600;}
.detail-val{color:#111111;font-weight:500;text-align:right;}
.notes-box{background:#FAF7F2;padding:16px 20px;border-radius:4px;margin-top:20px;font-size:14px;color:#3A3530;line-height:1.6;}
.footer-bar{background:#0A0A0A;padding:24px 40px;text-align:center;}
.footer-bar p{color:rgba(255,255,255,0.35);font-size:11px;margin:0;letter-spacing:1px;}
.footer-bar a{color:#C9A84C;}
</style></head>
<body><div class="wrap">
<div class="header"><h1>Elite Drive</h1><p>Luxury Chauffeur Service</p></div>
<div class="gold-bar"></div>
<div class="body">
  <div class="alert-box">
    <h2>New Quote Request Received</h2>
    <p>A new quote request has been submitted through the website.</p>
  </div>
  <div class="section-title">Client Information</div>
  <div class="detail-row"><span class="detail-label">Full Name</span><span class="detail-val">{$name}</span></div>
  <div class="detail-row"><span class="detail-label">Email Address</span><span class="detail-val">{$email}</span></div>
  <div class="detail-row"><span class="detail-label">Phone Number</span><span class="detail-val">{$phone}</span></div>
  <br>
  <div class="section-title">Journey Details</div>
  <div class="detail-row"><span class="detail-label">Pickup Location</span><span class="detail-val">{$pickup}</span></div>
  <div class="detail-row"><span class="detail-label">Destination</span><span class="detail-val">{$destination}</span></div>
  <div class="detail-row"><span class="detail-label">Date</span><span class="detail-val">{$date}</span></div>
  <div class="detail-row"><span class="detail-label">Time</span><span class="detail-val">{$time}</span></div>
  <div class="detail-row"><span class="detail-label">Vehicle Preference</span><span class="detail-val">{$vehicle}</span></div>
  <div class="detail-row"><span class="detail-label">Passengers</span><span class="detail-val">{$passengers}</span></div>
  <div class="notes-box"><strong>Additional Notes:</strong><br>{$notes}</div>
</div>
<div class="gold-bar"></div>
<div class="footer-bar"><p>&copy; {$config['company_name']} &mdash; <a href="{$config['company_website']}">{$config['company_website']}</a></p></div>
</div></body></html>
HTML;
}

function buildContactEmail($name, $email, $phone, $subject, $message, $config) {
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f0e8;margin:0;padding:0;}
.wrap{max-width:640px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.1);}
.header{background:linear-gradient(135deg,#0A0A0A,#1A1A1A);padding:36px 40px;text-align:center;}
.header h1{font-family:Georgia,serif;color:#C9A84C;font-size:22px;letter-spacing:4px;text-transform:uppercase;margin:0 0 4px;}
.header p{color:rgba(255,255,255,0.5);font-size:11px;letter-spacing:2px;text-transform:uppercase;margin:0;}
.gold-bar{height:3px;background:linear-gradient(90deg,transparent,#C9A84C,#E8C97A,#C9A84C,transparent);}
.body{padding:40px;}
.section-title{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#C9A84C;font-weight:700;margin-bottom:12px;}
.detail-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(0,0,0,0.06);font-size:14px;}
.detail-label{color:#8A8580;font-weight:600;}
.detail-val{color:#111111;font-weight:500;}
.message-box{background:#FAF7F2;padding:20px;border-radius:4px;margin-top:20px;font-size:14px;color:#3A3530;line-height:1.7;}
.footer-bar{background:#0A0A0A;padding:24px 40px;text-align:center;}
.footer-bar p{color:rgba(255,255,255,0.35);font-size:11px;margin:0;}
.footer-bar a{color:#C9A84C;}
</style></head>
<body><div class="wrap">
<div class="header"><h1>Elite Drive</h1><p>Website Contact Form</p></div>
<div class="gold-bar"></div>
<div class="body">
  <div class="section-title">Contact Details</div>
  <div class="detail-row"><span class="detail-label">Full Name</span><span class="detail-val">{$name}</span></div>
  <div class="detail-row"><span class="detail-label">Email</span><span class="detail-val">{$email}</span></div>
  <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-val">{$phone}</span></div>
  <div class="detail-row"><span class="detail-label">Subject</span><span class="detail-val">{$subject}</span></div>
  <div class="message-box"><strong>Message:</strong><br><br>{$message}</div>
</div>
<div class="gold-bar"></div>
<div class="footer-bar"><p>&copy; {$config['company_name']} &mdash; <a href="{$config['company_website']}">{$config['company_website']}</a></p></div>
</div></body></html>
HTML;
}

function buildConfirmationEmail($name, $config) {
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f0e8;margin:0;padding:0;}
.wrap{max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;}
.header{background:linear-gradient(135deg,#0A0A0A,#1A1A1A);padding:40px;text-align:center;}
.header h1{font-family:Georgia,serif;color:#C9A84C;font-size:24px;letter-spacing:5px;text-transform:uppercase;margin:0 0 6px;}
.header p{color:rgba(255,255,255,0.4);font-size:11px;letter-spacing:2px;margin:0;}
.gold-bar{height:3px;background:linear-gradient(90deg,transparent,#C9A84C,#E8C97A,#C9A84C,transparent);}
.body{padding:44px 40px;text-align:center;}
.body h2{font-family:Georgia,serif;font-size:22px;color:#0A0A0A;margin-bottom:14px;}
.body p{color:#8A8580;font-size:14px;line-height:1.8;margin-bottom:20px;}
.cta{display:inline-block;background:linear-gradient(135deg,#C9A84C,#9A7A2E);color:#ffffff;padding:14px 36px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none;border-radius:4px;}
.divider{height:1px;background:rgba(0,0,0,0.08);margin:28px 0;}
.contact-info{font-size:13px;color:#8A8580;}
.contact-info strong{color:#C9A84C;}
.footer-bar{background:#0A0A0A;padding:24px;text-align:center;}
.footer-bar p{color:rgba(255,255,255,0.3);font-size:11px;margin:0;}
</style></head>
<body><div class="wrap">
<div class="header"><h1>Elite Drive</h1><p>Luxury Chauffeur Service</p></div>
<div class="gold-bar"></div>
<div class="body">
  <h2>Thank You, {$name}!</h2>
  <p>We've received your request and a member of our team will be in touch with you shortly. We pride ourselves on responding within <strong>2 hours</strong> during business hours.</p>
  <a href="{$config['company_website']}" class="cta">Visit Our Website</a>
  <div class="divider"></div>
  <p class="contact-info">Need immediate assistance?<br><strong>{$config['company_phone']}</strong></p>
</div>
<div class="gold-bar"></div>
<div class="footer-bar"><p>&copy; {$config['company_name']}</p></div>
</div></body></html>
HTML;
}
?>