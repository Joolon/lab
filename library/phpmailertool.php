<?php

/**
 * Created by JoLon.
 * User：Jolon
 * Time: 15/9/3 下午9:33
 */
class PhpMailerTool
{
    public static function to_mail($header, $body)
    {
        require_once(Kohana::find_file('vendor', 'phpmailer/class.phpmailer'));
        try {
            $mail = new PHPMailer(true);
            $mail->IsSMTP();
            $mail->CharSet = 'UTF-8';
            //设置邮件的字符编码，这很重要，不然中文乱码
            $mail->SMTPAuth = true;
            //开启认证
            $mail->Port = 25;
            $mail->Host = "smtp.sina.com";
            $mail->Username = "jslichun@sina.com";
            $mail->Password = "lichun3315021";

            $mail->IsSendmail();//如果没有sendmail组件就注释掉，否则出现“Could not execute: /var/qmail/bin/sendmail ”的错误提示
            $mail->AddCC("chenw393425760eiyuan@haitun.hk", "陈未远");//抄送地址
            $mail->AddCC("@qq.com", "张志鹰");//抄送地址
            $mail->AddCC("32070053@qq.com", "熊波");//抄送地址
            $mail->AddCC("519494584@qq.com", "李春");//抄送地址

            $mail->From = "zhuyh1419@sina.com";
            $mail->FromName = "zhuyh1419";

            $mail->From = "jslichun@sina.com";
            $mail->FromName = "lichun";

            $to = "2535998102@qq.com";
            $mail->AddAddress($to);
            $mail->Subject = $header;
            $mail->Body = $body;
            $mail->WordWrap = 80;
            // 设置每行字符串的长度
            $mail->AltBody = "To view the message, please use an HTML compatible email viewer!";//当邮件不支持html时备用显示，可以省略
            $mail->AddAttachment("f:/test.png");//可以添加附件
            $mail->IsHTML(true);
            $mail->Send();
            echo '邮件已发送' . date('Y-m-d H:i:s', time());
        } catch (phpmailerException $e) {
            echo "邮件发送失败：" . $e->errorMessage();
        }
    }
}