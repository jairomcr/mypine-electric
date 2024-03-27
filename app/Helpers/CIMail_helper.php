<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('sendEmail')) {
    function sendEmail($mailConfig){
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        $mail ->SMTPDebug = 0;
        $mail ->isSMTP();
        $mail ->Host = env('EMAIL_HOST');
        $mail ->SMTPAuth=true;
        $mail ->Username=env('EMAIL_USERNAME');
        $mail ->Password=env('EMAIL_PASSWORD');
        $mail ->SMTPSecure=env('EMAIL_ENCRYPTION');
        $mail ->Port=env('EMAIL_PORT');
        $mail ->setFrom($mailConfig['mail_from_email'],$mailConfig['mail_from_name']);
        $mail ->addAddress($mailConfig['mail_recipient_email'],$mailConfig['mail_recipient_name']);
        $mail ->isHTML(true);
        $mail ->Subject=$mailConfig['mail_subject'];
        $mail ->Body = $mailConfig['mail_body'];
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
        
    }
}
/*function sendEmail($tipoInscripcion, $emailuser, $inscripcion){
    require($_SERVER["DOCUMENT_ROOT"]."/vendor/phpmailer/phpmailer/src/OAuth.php");

    require $_SERVER["DOCUMENT_ROOT"]."/vendor/phpmailer/phpmailer/src/Exception.php";
        require($_SERVER["DOCUMENT_ROOT"]."/vendor/phpmailer/phpmailer/src/PHPMailer.php");
        require($_SERVER["DOCUMENT_ROOT"]."/vendor/phpmailer/phpmailer/src/SMTP.php");

        $mail = new PHPMailer();
        try{
            $mail->IsSMTP();                                      // set mailer to
            $mail->Host = "mail.servidorcorreo.com";  // specify main and backup server
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->SMTPAutoTLS = false;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Username = "usuario@servidorcorreo.com";  // SMTP username
            $mail->Password = "Contraseña";
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->From = "Dirección de correo que envia";
            $mail->FromName = "Un nombre";        // remitente
            $mail->AddAddress($emailuser);        // destinatario
            $mail->IsHTML(true);     // set email
            $mail->Body = "EL cuerpo del mensage en html";
            if(!$mail->Send()) 
            {
                exit;
            }
        }catch(phpmailerException $e){
            echo $e->getMessage();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }*/