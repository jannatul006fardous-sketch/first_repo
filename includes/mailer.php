<?php
declare(strict_types=1);

function send_mail(string $to, string $subject, string $bodyHtml, ?string $bodyText = null): bool
{
    $driver = env('MAIL_DRIVER', 'log');
    $from = env('SMTP_FROM', 'noreply@refa.org');
    $fromName = env('SMTP_FROM_NAME', 'REFA');
    $text = $bodyText ?? trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $bodyHtml)));

    if ($driver === 'log') {
        return mail_to_log($to, $subject, $bodyHtml);
    }

    $autoload = ROOT_PATH . '/vendor/autoload.php';
    if (is_file($autoload) && $driver === 'smtp') {
        require_once $autoload;
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return mail_via_phpmailer($to, $subject, $bodyHtml, $text, $from, $fromName);
        }
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . sprintf('%s <%s>', mb_encode_mimeheader($fromName, 'UTF-8'), $from),
    ];
    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $bodyHtml, implode("\r\n", $headers));
}

function mail_to_log(string $to, string $subject, string $body): bool
{
    $dir = STORAGE_PATH . '/mail';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $entry = "---- " . date('c') . " ----\nTo: {$to}\nSubject: {$subject}\n\n{$body}\n\n";
    return (bool) file_put_contents($dir . '/outbox.log', $entry, FILE_APPEND | LOCK_EX);
}

function mail_via_phpmailer(
    string $to,
    string $subject,
    string $html,
    string $text,
    string $from,
    string $fromName
): bool {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = env('SMTP_HOST', '');
        $mail->SMTPAuth = true;
        $mail->Username = env('SMTP_USER', '');
        $mail->Password = env('SMTP_PASS', '');
        $enc = env('SMTP_ENCRYPTION', 'tls');
        $mail->SMTPSecure = $enc === 'ssl'
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) env('SMTP_PORT', '587');
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $text;
        $mail->send();
        return true;
    } catch (Throwable $e) {
        if (APP_DEBUG) {
            mail_to_log($to, '[FAIL] ' . $subject, $e->getMessage() . "\n" . $html);
        }
        return false;
    }
}

function mail_template(string $heading, string $bodyHtml): string
{
    $name = e(t('site.name_bn'));
    return '<!DOCTYPE html><html lang="bn"><body style="font-family:Hind Siliguri,sans-serif;line-height:1.8;color:#1a1614;background:#faf6f0;padding:24px;">
    <div style="max-width:560px;margin:auto;background:#fff;border-radius:16px;padding:28px;border:1px solid #eadfd3;">
      <h1 style="color:#0f4f38;font-size:22px;margin:0 0 12px;">' . e($heading) . '</h1>
      <div>' . $bodyHtml . '</div>
      <p style="margin-top:28px;font-size:13px;color:#5c534c;">' . $name . '<br>ফিনল্যান্ড, ২০২৬</p>
    </div></body></html>';
}

function notify_admin(string $subject, string $html): void
{
    $to = setting('admin_email', env('ADMIN_NOTIFY_EMAIL', 'admin@refa.org'));
    if ($to) {
        send_mail($to, $subject, mail_template($subject, $html));
    }
}
