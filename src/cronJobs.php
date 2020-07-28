<?php

$to = 'romain.madelaine@gmail.com';
$subject = 'essai Cron '.(new \DateTime())->format('H:i');
$message = $subject;

$headers = [
    'MIME-Version' => '1.0',
    'Content-type' => 'text/html;charset=UTF-8',
    'From' => 'Esperer95.app <noreply@esperer95-app.fr>',
    'Reply-To' => 'Esperer95.app <romain.madelaine@esperer-95.org>',
    'X-Mailer' => 'PHP/'.phpversion(),
];

mail($to, $subject, $message, $headers);
