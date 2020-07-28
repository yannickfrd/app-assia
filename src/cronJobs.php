<?php

use App\Service\DumpDatabase;

// $dumpDatabase = new DumpDatabase(
//     $_ENV['DB_DATABASE_NAME'],
//     $_ENV['DB_USERNAME'],
//     $_ENV['DB_PASSWORD'],
//     $_ENV['DB_HOST'],
//     $_ENV['PATH_MYSQLDUMP']
// );

// $backupDatas = $dumpDatabase->dump();

// $to = 'romain.madelaine@gmail.com';
// $subject = 'essai Cron '.(new \DateTime())->format('H:i');
// $message = $subject;

// $headers = [
//     'MIME-Version' => '1.0',
//     'Content-type' => 'text/html;charset=UTF-8',
//     'From' => 'Esperer95.app <noreply@esperer95-app.fr>',
//     'Reply-To' => 'Esperer95.app <romain.madelaine@esperer-95.org>',
//     'X-Mailer' => 'PHP/'.phpversion(),
// ];

// mail($to, $subject, $message, $headers);
