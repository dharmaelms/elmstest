<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Model\AccessRequest;
use App\Model\Announcement;
use App\Model\ChannelFaq;
use App\Model\Package\Entity\Package;
use App\Model\Program;
use App\Model\StaticPage;
use App\Model\Testimonial\Entity\Testimonial;
use App\Model\UserGroup;
use App\Model\Banners;
use App\Model\Catalog\Order\Entity\Order;
use App\Model\Catalog\Pricing\Entity\Price;
use App\Model\Category;
use App\Model\ChannelFaqAnswers;
use App\Model\ContactUs;
use App\Model\CronLog;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\Dam;
use App\Model\DimensionAnnouncements;
use App\Model\DirectQuizPerformanceByIndividualQuestion;
use App\Model\Event;
use App\Model\Faq;
use App\Model\FlashCard;
use App\Model\ImportLog\Entity\EnrolLog;
use App\Model\ImportLog\Entity\PackageEnrolLog;
use App\Model\ImportLog\Entity\PackageLog;
use App\Model\ImportLog\Entity\ProgramLog;
use App\Model\ImportLog\Entity\UserLog;
use App\Model\ImportLog\Entity\UsergroupLog;
use App\Model\Module\Entity\Module;
use App\Model\Notification;
use App\Model\Packet;
use App\Model\PacketFaq;
use App\Model\PacketFaqAnswers;
use App\Model\PartnerLogo;
use App\Model\PromoCode;
use App\Model\Question;
use App\Model\QuestionBank;
use App\Model\QuestionbankImportHistory;
use App\Model\Quiz;
use App\Model\QuizReport;
use App\Model\QuizAttempt;
use App\Model\Role;
use App\Model\RolesAndPermissions\Entity\Context;
use App\Model\RolesAndPermissions\Entity\Permission;
use App\Model\Transaction;
use App\Model\UserCertificates\UserCertificates;
use App\Model\UserimportHistory;
use App\Model\WebexHost;
use App\Model\Sequence;
use App\Model\User;
use Illuminate\Support\Facades\DB;

class CreateSequenceTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('sequence');
        $bulk_in_ary = [];
        $bulk_in_ary = [
            [
                '_id' => 'request_id',
                'seq' => is_null(AccessRequest::max('request_id')) ? 0 : AccessRequest::max('request_id')
            ],
            [
                '_id' => 'announcement_id',
                'seq' => is_null(Announcement::max('announcement_id')) ?
                        0 : Announcement::max('announcement_id')
            ],
            [
                '_id' => 'channel_faq_id',
                'seq' => is_null(ChannelFaq::max('id')) ? 0 : ChannelFaq::max('id')
            ],
            [
                '_id' => 'package_id',
                'seq' => is_null(Package::max('package_id')) ? 0 : Package::max('package_id')
            ],
            [
                '_id' => 'program_id',
                'seq' => is_null(Program::max('program_id')) ? 0 : Program::max('program_id')
            ],
            [
                '_id' => 'staticpagge_id',
                'seq' => is_null(StaticPage::max('staticpagge_id')) ? 0 : StaticPage::max('staticpagge_id')
            ],
            [
                '_id' => 'testimonial_id',
                'seq' => is_null(Testimonial::max('id')) ? 0 : Testimonial::max('id')
            ],
            [
                '_id' => 'ugid',
                'seq' => is_null(UserGroup::max('ugid')) ? 0 : UserGroup::max('ugid')
            ],
            [
                '_id' => 'banners_id',
                'seq' => is_null(Banners::max('id')) ? 0 : Banners::max('id')
            ],
            [
                '_id' => 'order_id',
                'seq' => is_null(Order::max('order_id')) ? 0 : Order::max('order_id')
            ],
            [
                '_id' => 'price_id',
                'seq' => is_null(Price::max('price_id')) ? 0 : Price::max('price_id')
            ],
            [
                '_id' => 'category_id',
                'seq' => is_null(Category::max('category_id')) ? 0 : Category::max('category_id')
            ],
            [
                '_id' => 'channel_faq_answers_id',
                'seq' => is_null(ChannelFaqAnswers::max('id')) ? 0 : ChannelFaqAnswers::max('id')
            ],
            [
                '_id' => 'contact_id',
                'seq' => is_null(ContactUs::max('contact_id')) ? 0 : ContactUs::max('contact_id')
            ],
            [
                '_id' => 'cron_log_id',
                'seq' => is_null(CronLog::max('id')) ? 0 :CronLog::max('id')
            ],
            [
                '_id' => 'custom_fields_id',
                'seq' => is_null(CustomFields::max('id')) ? 0 : CustomFields::max('id')
            ],
            [
                '_id' => 'dams_id',
                'seq' => is_null(Dam::max('id')) ? 0 : Dam::max('id')
            ],
            [
                '_id' => 'dim_announcement_id',
                'seq' => is_null(DimensionAnnouncements::max('id')) ? 0 : DimensionAnnouncements::max('id')
            ],
            [
                '_id' => 'dir_q_perf_que_id',
                'seq' => is_null(DirectQuizPerformanceByIndividualQuestion::max('id')) ?
                    0 : DirectQuizPerformanceByIndividualQuestion::max('id')
            ],
            [
                '_id' => 'event_id',
                'seq' => is_null(Event::max('event_id')) ?
                    0 : Event::max('event_id')
            ],
            [
                '_id' => 'faq_id',
                'seq' => is_null(Faq::max('faq_id')) ? 0 : Faq::max('faq_id')
            ],
            [
                '_id' => 'card_id',
                'seq' => is_null(FlashCard::max('card_id')) ? 0 : FlashCard::max('card_id')
            ],
            [
                '_id' => 'enrol_log_id',
                'seq' => is_null(EnrolLog::max('rid')) ? 0 : EnrolLog::max('rid')
            ],
            [
                '_id' => 'package_enrol_log_id',
                'seq' => is_null(PackageEnrolLog::max('rid')) ?
                    0 : PackageEnrolLog::max('rid')
            ],
            [
                '_id' => 'package_log_id',
                'seq' => is_null(PackageLog::max('rid')) ? 0 : PackageLog::max('rid')
            ],
            [
                '_id' => 'program_log',
                'seq' => is_null(ProgramLog::max('rid')) ? 0 : ProgramLog::max('rid')
            ],
            [
                '_id' => 'user_log',
                'seq' => is_null(UserLog::max('rid')) ? 0 : UserLog::max('rid')
            ],
            [
                '_id' => 'usergroup_log_id',
                'seq' => is_null(UsergroupLog::max('rid')) ? 0 : UsergroupLog::max('rid')
            ],
            [
                '_id' => 'module_id',
                'seq' => is_null(Module::max('id')) ? 0 : Module::max('id')
            ],
            [
                '_id' => 'notification_id',
                'seq' => is_null(Notification::max('notification_id')) ?
                    0 : Notification::max('notification_id')
            ],
            [
                '_id' => 'packet_id',
                'seq' => is_null(Packet::max('packet_id')) ? 0 : Packet::max('packet_id')
            ],
            [
                '_id' => 'packet_faq_id',
                'seq' => is_null(PacketFaq::max('id')) ? 0 : PacketFaq::max('id')
            ],
            [
                '_id' => 'packet_faq_answers_id',
                'seq' => is_null(PacketFaqAnswers::max('id')) ? 0 : PacketFaqAnswers::max('id')
            ],
            [
                '_id' => 'partner_logo_id',
                'seq' => is_null(PartnerLogo::max('partner_id')) ? 0 : PartnerLogo::max('partner_id')
            ],
            [
                '_id' => 'promo_code_id',
                'seq' => is_null(PromoCode::max('id')) ? 0 : PromoCode::max('id')
            ],
            [
                '_id' => 'question_id', 'seq' => is_null(Question::max('question_id')) ?
                    0 : Question::max('question_id')
            ],
            [
                '_id' => 'question_bank_id', 'seq' => is_null(QuestionBank::max('question_bank_id')) ?
                    0 : QuestionBank::max('question_bank_id')
            ],
            [
                '_id' => 'ques_bank_imp_his_id', 'seq' => is_null(QuestionbankImportHistory::max('id')) ?
                    0 : QuestionbankImportHistory::max('id')
            ],
            [
                '_id' => 'quiz_id', 'seq' => is_null(Quiz::max('quiz_id')) ?
                    0 : Quiz::max('quiz_id')
            ],
            [
                '_id' => 'quiz_report_id', 'seq' => is_null(QuizReport::max('quiz_report_id')) ?
                    0 : QuizReport::max('quiz_report_id')
            ],
            [
                '_id' => 'attempt_id', 'seq' => is_null(QuizAttempt::max('attempt_id')) ?
                    0 : QuizAttempt::max('attempt_id')
            ],
            [
                '_id' => 'rid', 'seq' => is_null(Role::max('rid')) ?
                    0 : Role::max('rid')
            ],
            [
                '_id' => 'context_id', 'seq' => is_null(Context::max('id')) ?
                    0 : Context::max('id')
            ],
            [
                '_id' => 'permission_id', 'seq' => is_null(Permission::max('id')) ?
                    0 : Permission::max('id')
            ],
            [
                '_id' => 'trans_id', 'seq' => is_null(Transaction::max('trans_id')) ?
                    0 : Transaction::max('trans_id')
            ],
            [
                '_id' => 'certificate_id', 'seq' => is_null(UserCertificates::max('certificate_id')) ?
                    0 : UserCertificates::max('certificate_id')
            ],
            [
                '_id' => 'user_history_id', 'seq' => is_null(UserimportHistory::max('hid')) ?
                    0 : UserimportHistory::max('hid')
            ],
            [
                '_id' => 'uid', 'seq' => is_null(User::max('uid')) ?
                    0 : User::max('uid')
            ],
            [
                '_id' => 'webex_host_id', 'seq' => is_null(WebexHost::max('webex_host_id')) ?
                    0 : WebexHost::max('webex_host_id')
            ]
        ];
       
        DB::collection('sequence')->raw(function ($collection) use ($bulk_in_ary) {
            return $collection->insertMany($bulk_in_ary, ['continueOnError' => true]);
        });
        
        echo 'Inserted sequence in sequence colletion / table successfully'.PHP_EOL;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::collection('sequence')->raw(function ($collection) {
            return $collection->drop();
        });
        echo "sequence is droped".PHP_EOL;
    }
}
