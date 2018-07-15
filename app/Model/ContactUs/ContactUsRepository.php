<?php

namespace App\Model\ContactUs;

use App\Model\Common;
use App\Model\ContactUs;
use App\Model\Program;
use Auth;

/**
 * Class ContactUsRepository
 *
 * @package App\Model\ContactUs
 */
class ContactUsRepository implements IContactUsRepository
{
    /**
     * {@inheritdoc}
     */
    public function save(array $data, $type = 'general')
    {
        $product[0]['program_id'] = '';
        if ($type == 'coursepage') {
            $product = Program::getFeedArray($data['slug']);
            $data['phone'] = '';
        }
        $mobile = (!empty($data['mobile'])) ? $data['mobile'] : 'NA';
        $userid = (Auth::check()) ? Auth::user()->uid : '';
        ContactUs::insert([
            'contact_id' => ContactUs::getUniqueId(),
            'name' => $data['name'],
            'message' => $data['message'],
            'email' => $data['email'],
            'mobile' => array_get($data, 'mobile', 'NA'),
            'phone' => array_get($data, 'phone', 'NA'),
            'contact_date' => time(),
            'product_id' => $product[0]['program_id'],
            'user_id' => $userid,
            'type' => $type
        ]);

        // Email start
        $site_name = config('app.site_name');
        $to = config('app.contact_us_email');
        $program = Program::getCFTitleID($product[0]['program_id']);
        $subject = 'Contact Enquiry for ' . $program . ' - ' . $site_name . '';

        if ($type == 'coursepage') {
            $body = '<html><body>Hello Admin,<br><br>A customer has
            tried to contact you from - ' . $site_name . '<br><br>Customer Name:-
            ' . $data['name'] . '<br>Customer Email:- ' . $data['email'] . '<br>Customer Mobile:-
             ' . $mobile . '<br>
            Customer Message:- ' . $data['message'] . '<br>Program Name:-
            ' . $program . '<br><br>Thank you,<br>Team ' . $site_name . '<br><br></html>
            </body>';
        } else {
            $body = '<html><body>Hello Admin,<br><br>A customer has
            tried to contact you from - ' . $site_name . '<br><br>Customer Name:-
            ' . $data['name'] . '<br>Customer Email:- ' . $data['email'] . '<br>Customer Mobile:-
             ' . $mobile . '<br>
            Customer Message:- ' . $data['message'] . '<br><br>Thank you,<br>Team ' . $site_name . '<br><br></html>
            </body>';
        }


        Common::sendMailHtml($body, $subject, $to);
    }
}
