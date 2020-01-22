<?php

namespace App\Notification;

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use App\Entity\User;
use Twig\Environment;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailNotificationTest
{
    protected $mail;

    public function __construct()
    {
        // Instantiation and passing `true` enables exceptions
        $this->mail = new PHPMailer(true);

        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mail->Debugoutput = "html";
        $this->mail->isSMTP(); // Send using SMTP
        $this->mail->SMTPAuth = true; // Enable SMTP authentication
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        if (strchr($_SERVER["HTTP_HOST"], "127.0.0.1")) {
            $this->mail->Host = "smtp.gmail.com"; // Set the SMTP server to send through
            $this->mail->Username = "romain.madelaine@gmail.com"; // SMTP username
            $this->mail->Password = "!joiro+689*"; // SMTP password
            $this->mail->Port = 587; // TCP port to connect to        
        } else {
            $this->mail->Host = "smtp.ionos.fr";
            $this->mail->Username = "esperer95-app@romain-mad.fr";
            $this->mail->Password = "Esp3r3r-95*";
            $this->mail->Port = 465;
        }

        $this->mail->CharSet = "UTF-8";
        $this->mail->isHTML(true); // Set email format to HTML
    }

    public function send($to, $subject, $htmlBody, $txtBody = null)
    {
        try {
            //Recipients
            $this->mail->setFrom("esperer95-app@romain-mad.fr", "Esperer95-app");
            $this->mail->addAddress($to["email"], $to["name"]);     // Add a recipient
            // $this->mail->addAddress("ellen@example.com");               // Name is optional
            // $this->mail->addReplyTo("info@example.com", "Information");
            // $this->mail->addCC("cc@example.com");
            // $this->mail->addBCC("bcc@example.com");

            // Attachments
            // $this->mail->addAttachment("/var/tmp/file.tar.gz");         // Add attachments
            // $this->mail->addAttachment("/tmp/image.jpg", "new.jpg");    // Optional name

            // Content
            $this->mail->Subject = $subject;
            $this->mail->Body    = $htmlBody;
            $this->mail->AltBody = $txtBody ?? null;

            $this->mail->send();

            return [
                "type" => "success",
                "message" => "Le message a été envoyé !"
            ];
            echo "Message has been sent";
        } catch (Exception $e) {
            return [
                "type" => "danger",
                "message" => "Le message n'a pas pu être envoyé. Erreur : {$this->mail->ErrorInfo}"
            ];
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}
