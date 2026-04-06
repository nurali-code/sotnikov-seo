<?php
// Configuration file - hidden from search engines
// This file contains sensitive credentials and should not be indexed

// Email configuration
define("MAIL_FROM_NAME", "Сотников");
define("MAIL_FROM_EMAIL", "zakaz@sotnikov.com.ru");
define("MAIL_ADDRESSES", ["info@sotnikov.com.ru"]);
define("MAIL_SUBJECT", "Заявка с сайта sotnikov.com.ru");

// Anti-spam configuration
define("ANTI_SPAM_FIELD", "cnames");

// Field labels for email and notifications
define("FIELD_LABELS", [
    'name' => 'Имя',
    'phone' => 'Телефон',
    'email' => 'E-Mail',
    'topic' => 'Тема обращения',
    'hotel' => 'Сайт или название отеля',
    'comment' => 'Комментарий',
    'consent' => 'Согласие на обработку данных',
    'date' => 'Дата и время заявки (по МСК)',
    'user_ip' => 'IP адрес пользователя',
    'page_url' => 'Страница отправки'
]);

// Prevent direct access
if (!defined('ACCESS_ALLOWED')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}
?>
