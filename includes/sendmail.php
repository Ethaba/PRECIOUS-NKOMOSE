<?php
require_once __DIR__ . '/functions.php';

function send_mail_log(string $to, string $subject, string $body): bool
{
	// Use the system email logger to keep track of messages
	return send_system_email($to, $subject, $body);
}

