<?php
namespace App\Libraries\moodle;

use Exception;
use App\Model\SiteSetting;

/**
 * Moodle webservices API library.
 * This library fuctions are tested with the Moodle 3.0.
 * To call moodle webservices
 * @package Library/Moodle
 */
//Course Parameters
define('SUMMARY', '');
define('SUMMARYFORMAT', '1');
define('FORMAT', 'topics');
define('SHOWGRADES', '1');
define('NEWSITEMS', '5');
define('STARTDATE', time()); //Current Time
define('NUMSECTIONS', '10');
define('MAXBYTES', '26214400'); //25MB
define('SHOWREPORTS', '0');
define('HIDDENSECTIONS', '0');
define('GROUPMODE', '0');
define('GROUPMODEFORCE', '0');
define('DEFAULTGROUPID', '0');

//User Parameters
define('AUTH', 'manual');
define('IDNUMBER', '');
define('LANG', 'en');
define('TIMEZONE', '99'); //Site Default 99

class MoodleAPI
{

    private static $moodleapi;

    /**
      * Constructor function
      */
    private function __construct()
    {
    }

    public static function get_instance()
    {
        if (!self :: $moodleapi) {
            self :: $moodleapi = new MoodleAPI();
        }
        return self :: $moodleapi;
    }

    /**
     * Create function for creating the course in moodle
     * @param string arrParam[fullname]
     * @param string arrParam[shortname]
     * @param int arrParam[categoryid]
     * @param string arrParam[summary] (Optional)
     * @param int arrParam[startdate] (Optional)
     * @param boolean parser
     * @return array [0:[id:(int),shortname:(string)]]
     */
    public function moodle_course_create($arrParam)
    {
        $data = SiteSetting::module('Lmsprogram')->setting;
        $param = [];
        $param['courses'][0]['fullname'] =  $arrParam['fullname'];
        $param['courses'][0]['shortname'] =  $arrParam['shortname'];
        $param['courses'][0]['categoryid'] =  $data['categoryid'];
        $param['courses'][0]['summary'] =  (isset($arrParam['summary'])? $arrParam['summary']:SUMMARY);
        $param['courses'][0]['startdate'] =  (isset($arrParam['startdate'])? $arrParam['startdate']:STARTDATE);
        $param['courses'][0]['visible']=$arrParam['visible'];
        $param['courses'][0]['summaryformat'] = SUMMARYFORMAT;
        $param['courses'][0]['format'] = FORMAT;
        $param['courses'][0]['showgrades'] = SHOWGRADES;
        $param['courses'][0]['newsitems'] = NEWSITEMS;
        $param['courses'][0]['numsections'] = NUMSECTIONS;
        $param['courses'][0]['maxbytes'] = MAXBYTES;
        $param['courses'][0]['showreports'] = SHOWREPORTS;
        $param['courses'][0]['hiddensections'] = HIDDENSECTIONS;
        $param['courses'][0]['groupmode'] = GROUPMODE;
        $param['courses'][0]['groupmodeforce'] = GROUPMODEFORCE;

        $functionName = 'core_course_create_courses';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Update function for updating the course in moodle
     * @param int arrParam[id]
     * @param string arrParam[fullname]
     * @param string arrParam[shortname]
     * @param int arrParam[categoryid]
     * @param string arrParam[summary] (Optional)
     * @param int arrParam[startdate] (Optional)
     * @param boolean parser
     * @return array [0:[id:(int),shortname:(string)]]
     */
    public function moodle_course_update($arrParam)
    {

        $param = [];
        $param['courses'][0]['id'] =  $arrParam['id'];
        $param['courses'][0]['fullname'] =  $arrParam['fullname'];
        $param['courses'][0]['shortname'] =  $arrParam['shortname'];
        //$param['courses'][0]['categoryid'] =  $arrParam['categoryid'];
        //$param['courses'][0]['summary'] =  (isset($arrParam['summary'])? $arrParam['summary']:SUMMARY);
        $param['courses'][0]['startdate'] =  (isset($arrParam['startdate'])? $arrParam['startdate']:STARTDATE);
        $param['courses'][0]['summaryformat'] = SUMMARYFORMAT;
        $param['courses'][0]['format'] = FORMAT;
        $param['courses'][0]['showgrades'] = SHOWGRADES;
        $param['courses'][0]['newsitems'] = NEWSITEMS;
        $param['courses'][0]['numsections'] = NUMSECTIONS;
        $param['courses'][0]['maxbytes'] = MAXBYTES;
        $param['courses'][0]['showreports'] = SHOWREPORTS;
        $param['courses'][0]['hiddensections'] = HIDDENSECTIONS;
        $param['courses'][0]['groupmode'] = GROUPMODE;
        $param['courses'][0]['groupmodeforce'] = GROUPMODEFORCE;
        $param['courses'][0]['visible'] = $arrParam['visible'];

        $functionName = 'core_course_update_courses';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Delete function for deleting the course in moodle
     * @param int arrParam[id]
     * @return array [notification:[0:(string),1:(string),........]]
     */
    public function moodle_course_delete($arrParam)
    {

        $param = [];
        $param['courseids'][0] =  $arrParam['id'];
        $functionName = 'core_course_delete_courses';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Get function for listin the course details
     * @param int arrParam[id]
     * @return array|null
     */
    public function moodle_course_get($arrParam)
    {
        $param = [];
        $param['options']['ids'][0] = $arrParam['id'];
        $functionName = 'core_course_get_courses';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Create function used to create a user in the moodle.
     * @param string arrParam[username]
     * @param string arrParam[password]
     * @param string arrParam[firstname]
     * @param string arrParam[lastname]
     * @param string arrParam[email]
     * @param int arrParam[timezone] (Optional)
     * @param string arrParam[city] (Optional)
     * @param string arrParam[country] (Optional) //AU or IN
     * @param boolean parser
     * @return array[0[id:(int),username:(string)]]
    */
    public function moodle_user_create($arrParam, $parser = true)
    {

        $param = [];
        $param['users'][0]['username'] = $arrParam['username'];
        $param['users'][0]['password'] = $arrParam['password'];
        $param['users'][0]['firstname'] = $arrParam['firstname'];
        $param['users'][0]['lastname'] = $arrParam['lastname'];
        $param['users'][0]['email'] = $arrParam['email'];
        $param['users'][0]['auth'] = AUTH;
        $param['users'][0]['idnumber'] = IDNUMBER;
        $param['users'][0]['lang'] = LANG;
        $param['users'][0]['timezone'] = (isset($arrParam['timezone'])?$arrParam['timezone']:TIMEZONE);
        //$param['users'][0]['suspended'] = $arrParam['suspended'];
        //$param['users'][0]['country'] = $arrParam['country'];
        //$param['users'][0]['city'] = $arrParam['city'];

        $functionName = 'core_user_create_users';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Update function used to update the user in the moodle.
     * @param int arrParam[id]
     * @param string arrParam[username]
     * @param string arrParam[password]
     * @param string arrParam[firstname]
     * @param string arrParam[lastname]
     * @param string arrParam[email]
     * @param int arrParam[timezone] (Optional)
     * @param string arrParam[city] (Optional)
     * @param string arrParam[country] (Optional) //AU or IN
     * @param boolean parser
     * @return array[0[id:(int),username:(string)]]
    */
    public function moodle_user_update($arrParam)
    {

        $param = [];
        $param['users'][0]['id'] = $arrParam['id'];
        $param['users'][0]['username'] = $arrParam['username'];
        if (isset($arrParam['password'])) {
            $param['users'][0]['password'] = $arrParam['password'];
        }
        $param['users'][0]['firstname'] = $arrParam['firstname'];
        $param['users'][0]['lastname'] = $arrParam['lastname'];
        $param['users'][0]['email'] = $arrParam['email'];
        $param['users'][0]['auth'] = AUTH;
        $param['users'][0]['idnumber'] = IDNUMBER;
        $param['users'][0]['lang'] = LANG;
        $param['users'][0]['timezone'] = (isset($arrParam['timezone'])?$arrParam['timezone']:TIMEZONE);
        //$param['users'][0]['country'] = $arrParam['country'];
        //$param['users'][0]['city'] = $arrParam['city'];

        $functionName = 'core_user_update_users';
        $result=restcall($functionName, $param);
        return $result;
    }
    public function moodle_user_forgot_password($arrParam)
    {
        $param = [];
        $param['users'][0]['id'] = $arrParam['id'];
        $param['users'][0]['password'] = $arrParam['password'];
        $functionName = 'core_user_update_users';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Delete function used to delete the user in the moodle.
     * @param int arrParam[id]
     * @return array|null
    */
    public function moodle_user_delete($arrParam)
    {

        $param = [];
        $param['userids'][0] = $arrParam['id'];
        $functionName = 'core_user_delete_users';
        $result=restcall($functionName, $param);
        return $result;
    }

    /**
     * Get user by user fields.
     * @param string arrParam[field]
     * @param string arrParam[value]
     * @return array [0:[id:(int),username:(string),password:(string),.....]]
    */
    public function moodle_getuserbyfield($arrParam)
    {

        $param = [];
        $param['field'] = $arrParam['field'];
        $param['values'][0] = $arrParam['value'];
        $functionName = 'core_user_get_users_by_field';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Enrol function for enrolling the user into the moodle course.
     * @param int arrParam[roleid]
     * @param int arrParam[userid]
     * @param int arrParam[courseid]
     * @param int arrParam[timestart] (Optional)
     * @param int arrParam[timeend] (Optional)
     * @param int arrParam[suspend] (Optional)
     * @param boolean parser
     * @return array|null
     */
    public function moodle_enrol($arrParam, $parser = true)
    {
        $param = [];
        $param['enrolments'][0]['roleid'] = $arrParam['roleid'];
        $param['enrolments'][0]['userid'] = $arrParam['userid'];
        $param['enrolments'][0]['courseid'] = $arrParam['courseid'];
        if (isset($arrParam['timestart'])) {
            $param['enrolments'][0]['timestart'] = $arrParam['timestart'];
        }
        if (isset($arrParam['timeend'])) {
            $param['enrolments'][0]['timeend'] = $arrParam['timeend'];
        }
        if (isset($arrParam['suspend'])) {
            $param['enrolments'][0]['suspend'] = $arrParam['suspend'];
        }

        $functionName = 'enrol_manual_enrol_users';
        $result=restcall($functionName, $param);
        return $result;
    }
    public function moodle_unenrol($arrParam, $parser = true)
    {
        $param = [];
        $param['enrolments'][0]['roleid'] = $arrParam['roleid'];
        $param['enrolments'][0]['userid'] = $arrParam['userid'];
        $param['enrolments'][0]['courseid'] = $arrParam['courseid'];
        $functionName = 'enrol_manual_unenrol_users';
        $result=restcall($functionName, $param);
        return $result;
    }

    /**
     * Get enrolled users function used to fetch the list of enrolled users
     * in the moodle course.
     * @param int arrParam[courseid]
     * @param string arrParam[withcapability] (Optional)
     * @param int arrParam[groupid] (Optional)
     * @param int arrParam[onlyactive] (Optional)
     * @return array|null [0:[courseid:(int),userid:(int),firstname:(string),lastname:(string),fullname:(string),
     *                        username:(string),profileimagurl:(string),profileimgurlsmall:(string)],
     *                     1:[courseid:(int)........]]
     */
    public function moodle_enrol_get($arrParam)
    {
        //$arrParam['withcapability'] = NULL;
        //$arrParam['onlyactive'] = 0;
        $param['courseid']= $arrParam['courseid'];
        $functionName = 'core_enrol_get_enrolled_users';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Get user enrolled courses list in moodle.
     * @param int arrParam[userid]
     * @param int arrParam[onlyactive] (Optional)
     * @return array|null [0:[courseid:(int),shortname:(dtring),fullname:(string),enrolledusercount:(int),idnumber:(string),
     *                        visible:(int),summary:(string),summaryformat:(string),format:(string)
     *                        showgrades:(int),lang:(string),enablecompletion(int)],
     *                     1:[courseid:(int)........]]
     */

    public function moodle_get_user_course($arrParam)
    {
        $param['userid']= $arrParam['userid'];
        $functionName = 'core_enrol_get_users_courses';
        $result=restcall($functionName, $param);
        return $result;
    }
    /**
     * Get category list in moodle.
     * @param int arrParam[]
     * @return array|null [0:[id:(int),name:(dtring),idnumber:(string),description:(string),descriptionformat:(int),
     *                        visible:(int),parent:(int),sortorder:(int),coursecount:(int)
     *                        visibleold:(int),timemodified:(int),depth(int),
     *                        path:(string),theme:(string)],
     *                     1:[id:(int)........]]
     */
    public function moodle_get_category_list($arrParam)
    {
        $param['']= $arrParam[''];
        $functionName = 'core_course_get_categories';
        $result=restcall($functionName, $param);
        return $result;
    }
    public function moodle_get_course_list()
    {
        $param['']='';
        $functionName = 'core_course_get_courses';
        $result=restcall($functionName, $param);
        return $result;
    }

    public function moodle_user_role_assign($arrParam, $parser = true)
    {
        $param = [];
        $param['assignments'][0]['userid'] = $arrParam['userid'];
        $param['assignments'][0]['roleid'] = $arrParam['roleid'];
        $param['assignments'][0]['contextid'] = $arrParam['contextid'];
        $functionName = 'core_role_assign_roles';
        $result=restcall($functionName, $param);
        return $result;
    }

    public function moodle_user_role_unassign($arrParam, $parser = true)
    {
        $param = [];
        $param['unassignments'][0]['userid'] = $arrParam['userid'];
        $param['unassignments'][0]['roleid'] = $arrParam['roleid'];
        $param['unassignments'][0]['contextid'] = $arrParam['contextid'];
        $functionName = 'core_role_unassign_roles';
        $result=restcall($functionName, $param);
        return $result;
    }
}

function restcall($functionName, $param)
{
         //$token = '808b9aa7b947a28685398312c23b16c4';
         //$domainname = 'http://localhost/moodle30';
         $data=SiteSetting::module('Lmsprogram')->setting;
         $token=$data['wstoken'];
         $domainname=$data['site_url'];
         $restformat = 'json';
         $serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionName;

         $curl = new curl();
         $restformat = ($restformat == 'json')?'&moodlewsrestformat=' . $restformat:'';
         $response = $curl->post($serverurl . $restformat, $param);
         $response=json_decode($response, true);


    try {
        if (isset($response['exception'])) {
             $e=$response['message'];
             throw new Exception($e);
        } else if (empty($response['warnings']) && isset($response['warnings'])) {
             $e="Updated successfully";
             throw new Exception($e);
        } else if (isset($response['warnings'])) {
             $e=$response['warnings'][0]['message'];
             throw new Exception($e);
        } else {
             $output=$response;
        }
    } catch (Exception $e) {
        $output=$e->getMessage();
    }

         return $output;
}
