<?php
use App\Model\User;
use App\Model\Email;
use App\Model\Common;
		$email=Input::get('email');
		$names=User::getUserNames($email);
		foreach($names as $deatils){
			$firstname = $deatils->firstname;
			$lastname = $deatils->lastname;
		}
		$admin_support_email = Config('app.admin_support_email');
            $email_address = config('mail.from')['address'];
            $support_email = $admin_support_email?$admin_support_email:$email_address;

            $site_name = Config('app.site_name');
            $base_url = config('app.url');
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            $headers .= 'From:'.$site_name."\r\n";
            $to = $email;
            $temp_subject = '';
            $temp_body = '';

            $base_url = config('app.url');
            $reset_url = '<a href="'.$base_url.'/password/reset/'.$token.'">Click here</a>';
            $link =  $base_url.'/password/reset/'.$token;
            $name = 'forgot-password';
            $email_details = Email::getEmail($name);
            foreach($email_details as $template_details){
            	$temp_subject = $template_details->subject;
            	$temp_body = $template_details->body;
            }
            	
            $subject = $temp_subject;
            $subject_find = array('<SITE NAME>');
            $subject_replace = array($site_name);
            $subject = str_replace($subject_find, $subject_replace, $subject);

            $body = $temp_body;
            $find = array('<FIRSTNAME>', '<LASTNAME>', '<SITE NAME>' , '<RESET URL>', '<LINK>', '<SUPPORT EMAIL>','<SITE URL>');
            $replace = array($firstname, $lastname, $site_name, $reset_url,$link, $support_email,$base_url);
            $body = str_replace($find, $replace, $body);
      
            //Common::SendMailHTML($body, $subject, $to);
?>
{!!$body!!}