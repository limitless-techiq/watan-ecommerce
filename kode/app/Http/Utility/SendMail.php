<?php
namespace App\Http\Utility;
use App\Models\MailConfiguration;
use Illuminate\Support\Facades\Mail;
use App\Models\GeneralSetting;
use App\Models\EmailTemplates;

class SendMail
{
    public static function MailNotification($userInfo, $emailTemplate, $code = [])
    {

        $general = GeneralSetting::first();

        $mailConfiguration = MailConfiguration::where('status', '1')->where('id',$general->email_gateway_id)->first();
    
        if(!$mailConfiguration){
            return ;
        }

        $emailTemplate = EmailTemplates::where('slug', $emailTemplate)->first();
        $messages = str_replace("{{username}}", @$userInfo->username??@$userInfo->first_name , $general->email_template);
        $messages = str_replace("{{message}}", @$emailTemplate->body, $messages);
        foreach ($code as $key => $value) {
            $messages = str_replace('{{' . $key . '}}', $value, $messages);
        }
        $response = '' ;
        if($mailConfiguration->name === "PHP MAIL"){
            $response = self::SendPHPmail($general->mail_from, $userInfo->email, $emailTemplate->subject, $messages);
        }elseif($mailConfiguration->name === "SMTP"){
            $response = self::SendSMTPMail($mailConfiguration->driver_information->from->address, $userInfo->email, $emailTemplate->subject, $messages);
        }elseif($mailConfiguration->name === "SendGrid Api"){
            $response = self::SendGrid($general->mail_from, $userInfo->email, @$userInfo->name, $emailTemplate->subject, $messages, @$mailConfiguration->driver_information->app_key);
        }
        return $response;
    }

    public static function SendSMTPMail($emailFrom, $emailTo, $subject, $messages)
    {
        $response ='';
        try{
            Mail::raw(strip_tags($messages), function ($message) use ($emailFrom, $emailTo, $subject) {
                $message->from($emailFrom);
                $message->to($emailTo);
                $message->subject($subject);
            });
        }
        catch(\Exception $exception){
            $response ='failed';
        }
        return $response;
 
    }

    public static function SendPHPmail($emailFrom, $emailTo, $subject, $messages)
    {
        $headers = "From: <$emailFrom> \r\n";
        $headers .= "Reply-To: $emailTo \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        @mail($emailTo, $subject, $messages, $headers);
        return '';
    }

    public static function SendGrid($emailFrom, $emailTo, $receiverName, $subject, $messages, $credentials)
    {
        $response ='';
        $general = GeneralSetting::first();
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($emailFrom, $general->site_name);
        $email->setSubject($subject);
        $email->addTo($emailTo, $receiverName);
        $email->addContent("text/html", $messages);
        $sendgrid = new \SendGrid($credentials);
        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            $response ='failed';
        }
        return $response;
    }
}