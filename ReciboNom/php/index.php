<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once "../../assets/mail/src/PHPMailer.php";
require_once "../../assets/mail/src/SMTP.php";
require_once "../../assets/mail/src/POP3.php";
$img_data_url =  $_POST['url'];
$correo =  $_POST['correo'];
$img_data = substr($img_data_url, strpos($img_data_url, ",") + 1);
$img_data = base64_decode($img_data);
$temp_img_path = "temp/image.png";
file_put_contents($temp_img_path, $img_data);
$mail = new PHPMailer();
$mail->isSMTP(); 
$mail->Host = 'smtp.gmail.com';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;
$mail->SMTPDebug  = 2;
$mail->SMTPAuth   = true;
$mail->Username   = 'kcmprosedemail@gmail.com';
$mail->isHTML(true);
$mail->Password   = 'skvh yraq vlip uxax';
$mail->SetFrom('noreply@Prosede.com', "Mes Prosede");
$mail->AddReplyTo('noreply@Prosede.com', 'no-reply');
$mail->Subject    = 'Recibo de nomina';
$mail->addAttachment($temp_img_path, 'imagen.png');
$mail->Body = "<p>Adjunto encontrarás tu recibo de nomina.</p>";
$mail->AddAddress($correo, 'Destinatario');
// $mail->IsSMTP();
if($mail->send()){
    http_response_code(200);
}else {
    http_response_code(500);
}


?>