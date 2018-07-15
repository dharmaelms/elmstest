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
use App\Model\QuizAttempt;
use App\Model\QuizReport;
use App\Model\Role;
use App\Model\RolesAndPermissions\Entity\Context;
use App\Model\RolesAndPermissions\Entity\Permission;
use App\Model\Transaction;
use App\Model\UserCertificates\UserCertificates;
use App\Model\UserimportHistory;
use App\Model\WebexHost;
use App\Model\User;

class InsertSeqInAllCollections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        echo 'Do not wanted to insert sequence in all collections since its split into one separete table successfully.';
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        AccessRequest::where("_id", "request_id")->delete();
        Announcement::where("_id", "announcement_id")->delete();
        ChannelFaq::where("_id", "id")->delete();
        Package::where("_id", "package_id")->delete();
        Program::where("_id", "program_id")->delete();
        StaticPage::where("_id", "staticpagge_id")->delete();
        Testimonial::where("_id", "id")->delete();
        UserGroup::where("_id", "ugid")->delete();
        Banners::where("_id", "id")->delete();
        Order::where("_id", "order_id")->delete();
        Price::where("_id", "price_id")->delete();
        Category::where("_id", "category_id")->delete();
        ChannelFaqAnswers::where("_id", "id")->delete();
        ContactUs::where("_id", "contact_id")->delete();
        CronLog::where("_id", "id")->delete();
        CustomFields::where("_id", "id")->delete();
        Dam::where("_id", "id")->delete();
        DimensionAnnouncements::where("_id", "id")->delete();
        DirectQuizPerformanceByIndividualQuestion::where("_id", "id")->delete();
        Event::where("_id", "event_id")->delete();
        Faq::where("_id", "faq_id")->delete();
        FlashCard::where("_id", "card_id")->delete();
        EnrolLog::where("_id", "rid")->delete();
        PackageEnrolLog::where("_id", "rid")->delete();
        PackageLog::where("_id", "rid")->delete();
        ProgramLog::where("_id", "rid")->delete();
        UserLog::where("_id", "rid")->delete();
        UsergroupLog::where("_id", "rid")->delete();
        Module::where("_id", "id")->delete();
        Notification::where("_id", "notification_id")->delete();
        Packet::where("_id", "packet_id")->delete();
        PacketFaq::where("_id", "id")->delete();
        PacketFaqAnswers::where("_id", "id")->delete();
        PartnerLogo::where("_id", "partner_id")->delete();
        PromoCode::where("_id", "id")->delete();
        Question::where("_id", "question_id")->delete();
        QuestionBank::where("_id", "question_bank_id")->delete();
        QuestionbankImportHistory::where("_id", "id")->delete();
        Quiz::where("_id", "quiz_id")->delete();
        QuizReport::where("_id", "quiz_report_id")->delete();
        QuizAttempt::where("_id", 'attempt_id')->delete();
        Role::where("_id", "rid")->delete();
        Context::where("_id", "id")->delete();
        Permission::where("_id", "id")->delete();
        Transaction::where("_id", "trans_id")->delete();
        UserCertificates::where("_id", "certificate_id")->delete();
        UserimportHistory::where("_id", "hid")->delete();
        WebexHost::where("_id", "webex_host_id")->delete();
        User::where("_id", "uid")->delete();

        echo "Removed all the sequece from respective collections";
    }
}
