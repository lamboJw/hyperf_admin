<?php
namespace App\Helper;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer {

    /*
     * 发送邮件
     * */
    public function send($to, $subject, $content, $attachment = '') {
        $mail               = new PHPMailer; //PHPMailer对象
        $mail->CharSet      = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP(); // 设定使用SMTP服务
        $mail->SMTPDebug    = 0; // 关闭SMTP调试功能
        $mail->SMTPAuth     = true; // 启用 SMTP 验证功能
        $mail->SMTPSecure   = 'ssl'; // 使用安全协议
        $mail->Host         = 'smtp.exmail.qq.com'; // SMTP 服务器
        $mail->Port         = '465'; // SMTP服务器的端口号
        $mail->Username     = 'xiamingyi@deepseagame.com'; // SMTP服务器用户名
        $mail->Password     = 'kinj30gr4jrv!kb^'; // SMTP服务器密码
        $mail->Subject      = $subject;
        $mail->Body         = $content;
        $mail->IsHTML(true);
        $mail->SetFrom('xiamingyi@deepseagame.com', '合同对账系统'); // 邮箱，昵称
        if (!empty($attachment)){
            $exa            = explode('/', $attachment);
            $mail->AddAttachment(BASE_PATH .'/storage'. $attachment, $exa[count($exa)-1]);
        }
        // 收件人
        if (is_array($to)){
            foreach($to as $v){
                $mail->AddAddress($v);
            }
        }else{
            $mail->AddAddress($to);
        }

        if ($mail->Send()){
            return true;
        }else{
            return false;
        }
    }


}