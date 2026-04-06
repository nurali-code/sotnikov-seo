<?php
// Allow access to config
define('ACCESS_ALLOWED', true);

// Include configuration
require_once __DIR__ . '/config.php';

date_default_timezone_set('Europe/Moscow');
$response = ['status' => '', 'message' => ''];

// Антиспам проверка
if (!empty($_POST[ANTI_SPAM_FIELD])) {
    $response['status'] = 'error';
    $response['message'] = 'Обнаружен спам';
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$_POST['date'] = date('m/d/Y H:i:s');
$_POST['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

// Проверка обязательных полей
$requiredFields = ['name', 'phone'];
$missingFields = [];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
        $missingFields[] = $field;
    }
}
if (!empty($missingFields)) {
    $response['status'] = 'error';
    $response['message'] = 'Не заполнены обязательные поля: ' . implode(', ', $missingFields);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// ====== EMAIL SEND ======
$emailSuccess = false;
try {
    $to = implode(",", MAIL_ADDRESSES);
    
    $subject = MAIL_SUBJECT;
    if (!empty($_POST["topic"])) {
        $topicLabels = [
            'seo' => 'SEO',
            'site' => 'Сайт',
            'locks' => 'Замки'
        ];
        $subject .= " — " . ($topicLabels[$_POST["topic"]] ?? htmlspecialchars($_POST["topic"]));
    }
    
    $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    
    // Get domain for email
    $domain = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'sotnikov.com.ru';
    $domain = preg_replace(['/^www\./', '/:\d+$/'], '', $domain);
    if (preg_match('/([a-z0-9-]+\.[a-z]{2,}(?:\.[a-z]{2})?)$/i', $domain, $m)) {
        $domain = $m[1];
    }
    
    // Create HTML body
    $htmlBody = generate_html_body($_POST);
    
    // Create headers
    $boundary = md5(time());
    $headers = [
        "MIME-Version: 1.0",
        "Content-Type: multipart/mixed; boundary=\"{$boundary}\"",
        "From: =?UTF-8?B?" . base64_encode(MAIL_FROM_NAME) . "?= <" . MAIL_FROM_EMAIL . ">",
        "Reply-To: " . MAIL_FROM_EMAIL,
        "X-Mailer: PHP/" . phpversion()
    ];
    $headers = implode("\r\n", $headers);
    
    // Build message
    $message = "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= $htmlBody . "\r\n";
    
    // Add attachments if any
    foreach (incoming_files() as $file) {
        if (file_exists($file['tmp_name'])) {
            $content = chunk_split(base64_encode(file_get_contents($file['tmp_name'])));
            $filename = $file['name'];
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: application/octet-stream; name=\"{$filename}\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n";
            $message .= $content . "\r\n";
        }
    }
    
    $message .= "--{$boundary}--";
    
    // Send email
    if (mail($to, $subject, $message, $headers, "-f" . MAIL_FROM_EMAIL)) {
        $emailSuccess = true;
        $response['status'] = 'success';
        $response['message'] = 'Сообщение успешно отправлено';
    } else {
        $error = error_get_last();
        $errorMsg = $error ? $error['message'] : 'Unknown error';
        throw new Exception("Ошибка при отправке почты: " . $errorMsg);
    }
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = "Сообщение не было отправлено. Причина: " . $e->getMessage();
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response, JSON_UNESCAPED_UNICODE);

// ================== Функции ====================

function humanize_key($key)
{
    $key = preg_replace('/[_\-]+/', ' ', $key);
    $key = preg_replace('/([a-z])([A-Z])/', '$1 $2', $key);
    return ucfirst(strtolower($key));
}

function generate_html_body(array $post)
{
    $body = "";
    foreach ($post as $key => $value) {
        if ($value != "") {
            $key = FIELD_LABELS[$key] ?? humanize_key($key);
            $body .= "
            <tr style='background-color: #f8f8f8;'>
                <td style='padding: 10px; border: #e9e9e9 1px solid;'><b>$key</b></td>
                <td style='padding: 10px; border: #e9e9e9 1px solid;'>" .
                (is_array($value) ? htmlspecialchars(implode(', ', $value)) : nl2br(htmlspecialchars($value))) .
                "</td>
            </tr>";
        }
    }
    return "<table style='width: 100%; border-collapse: collapse;'>$body</table>";
}

function incoming_files()
{
    $files = $_FILES;
    $files2 = [];
    foreach ($files as $input => $infoArr) {
        $filesByInput = [];
        foreach ($infoArr as $key => $valueArr) {
            if (is_array($valueArr)) {
                foreach ($valueArr as $i => $value) {
                    $filesByInput[$i][$key] = $value;
                }
            } else {
                $filesByInput[] = $infoArr;
                break;
            }
        }
        $files2 = array_merge($files2, $filesByInput);
    }
    $files3 = [];
    foreach ($files2 as $file) {
        if (!isset($file['error']) || !$file['error'])
            $files3[] = $file;
    }
    return $files3;
}
