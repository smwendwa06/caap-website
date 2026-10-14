<?php
// Email alerts for the contact form.
// Change these three settings if the alerts should go somewhere else.

require_once __DIR__ . '/helpers.php';

const CAAP_ALERT_TO   = 'caap@caapke.org';    // who gets the alert (a real mailbox or forwarder in cPanel)
const CAAP_ALERT_FROM = 'noreply@caapke.org'; // sender shown on the alert; keep it on your own domain
const CAAP_SITE_URL   = 'https://caapke.org'; // used for the "open in dashboard" link

/**
 * Emails a short alert about a new contact-form message.
 * Never throws: if the email can't be sent, the message is still safely saved in the database.
 */
function notify_new_message(mysqli $conn, int $messageId, string $name, string $email, string $message): bool
{
    try {
        // Flood guard: at most 10 alert emails per 10 minutes, so a spam burst can't bury the inbox.
        // (Every message is still saved and shows up in the dashboard.)
        $recent = mysqli_query($conn, "SELECT COUNT(*) FROM contact_messages WHERE submitted_at > (NOW() - INTERVAL 10 MINUTE)");
        if ($recent && (int) mysqli_fetch_row($recent)[0] > 10) {
            return false;
        }

        $cleanName = trim(preg_replace('/\s+/', ' ', $name)); // no line breaks in anything that reaches a header
        $subject   = 'New website message from ' . mb_substr($cleanName, 0, 60);

        $body  = "Someone sent a message through the CAAP website.\n\n";
        $body .= "Name:  {$cleanName}\n";
        $body .= "Email: {$email}\n\n";
        $body .= "Message:\n";
        $body .= mb_substr($message, 0, 5000) . "\n\n";
        $body .= "Read it in the dashboard:\n" . CAAP_SITE_URL . '/admin/messages/view.php?id=' . $messageId . "\n";

        $headers = [
            'From'                      => 'CAAP Website <' . CAAP_ALERT_FROM . '>',
            'MIME-Version'              => '1.0',
            'Content-Type'              => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => 'base64',
        ];
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $headers['Reply-To'] = $email; // hitting Reply in your inbox answers the visitor
        }

        $sent = @mail(
            CAAP_ALERT_TO,
            '=?UTF-8?B?' . base64_encode($subject) . '?=',
            chunk_split(base64_encode($body)),
            $headers,
            '-f' . CAAP_ALERT_FROM
        );
        if (!$sent) {
            error_log('CAAP: alert email for message #' . $messageId . ' could not be sent.');
        }
        return $sent;
    } catch (Throwable $e) {
        error_log('CAAP: alert email failed: ' . $e->getMessage());
        return false;
    }
}
