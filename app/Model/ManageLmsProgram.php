<?php
namespace App\Model;

use App\Libraries\moodle\MoodleAPI;
use Auth;
use Moloquent;
use Timezone;

class ManageLmsProgram extends Moloquent
{
    protected $collection = 'lmsprogram';

    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'program_startdate', 'program_enddate', 'program_display_startdate', 'program_display_enddate'];

    public static function getLmsprogramId()
    {

        $cursor = self::where('program_id', '>', 0)->value('program_id');
        if (count($cursor) == 0) {
            return 1;
        } else {
            $cursor = self::where('program_id', '>', 0)->orderBy('program_id', 'desc')->limit(1)->get(['program_id'])->toArray();
            $program_id = $cursor[0]['program_id'] + 1;
            return $program_id;
        }
    }

    public static function addBatch($data)
    {
        $moodleapi = MoodleAPI::get_instance();
        $lmsprogram = self::where('program_id', '=', (int)$data['program_id'])->get()->toArray();

        $record = self::where('program_id', '=', (int)$data['program_id'])->get(['variant.id'])->toArray();
        $record = $record[0]['variant'];
        $arrayTemp = [];
        $i = 0;
        if (!empty($record)) {
            foreach ($record as $key => $val) {
                $arrayTemp[$i] = $val['id'];
                $i++;
            }


            $insertid = max($arrayTemp) + 1;
        } else {
            $insertid = 1;
        }
        $type = 'batch';
        $fields = ManageAttribute::getVariants($type);
        $batch = ['id' => $insertid, 'active_user_feed_rel' => []];
        foreach ($fields as $field) {
            $element = $field['attribute_name'];
            $batch += [$element => strtolower($data[$element])];
        }

        $paramlist['categoryid'] = SiteSetting::module('Lmsprogram', 'categoryid');
        $paramlist['fullname'] = $lmsprogram[0]['program_title'] . ' - ' . $batch['batchname'];
        $paramlist['shortname'] = $lmsprogram[0]['title_lower'] . $batch['batchname'];
        $paramlist['visible'] = 1;
        $paramlist['startdate'] = strtotime($data['startdate']);
        $result = $moodleapi->moodle_course_create($paramlist);
        $moodlecourseid = $result[0]['id'];
        $batch += ['lmscourseid' => $moodlecourseid];

        $curval = $data['curval'];
        $nextval = $data['sort_order'];
        if ($curval == $nextval) {
            $batch += ['sort_order' => (int)$nextval];
            self::where('program_id', '=', (int)$data['program_id'])->push('variant', $batch);
        } else {
            $b = self::where('program_id', '=', (int)$data['program_id'])->value('variant');
            $b = array_values($b);
            foreach ($b as $bb) {
                if ($bb['sort_order'] == $nextval) {
                    $type = 'batch';
                    $fields = ManageAttribute::getVariants($type);
                    $batchs = ['id' => $bb['id'], 'sort_order' => (int)$curval, 'lmscourseid' => $bb['lmscourseid'], 'active_user_feed_rel' => $bb['active_user_feed_rel']];
                    foreach ($fields as $field) {
                        $element = $field['attribute_name'];
                        $batchs += [$element => strtolower($bb[$element])];
                    }
                    self::where('program_id', '=', (int)$data['program_id'])->pull('variant', ['id' => (int)$batchs['id']]);
                    self::where('program_id', '=', (int)$data['program_id'])->push('variant', $batchs);
                }
            }

            $batch += ['sort_order' => (int)$nextval];
            self::where('program_id', '=', (int)$data['program_id'])->push('variant', $batch);
        }
    }

    public static function addLmsprogram($data, $slug, $name, $username, $varianttype, $mediaid)
    {
        $category = SiteSetting::module('Lmsprogram')->setting;
        $moodleapi = MoodleAPI::get_instance();
        $uid = self::getLmsprogramId();
        if ($mediaid) {
            Dam::updateDAMSRelation($mediaid, 'lmscourse_media_rel', (int)$uid);
        }
        $batch = [];
        $fields = ManageAttribute::getVariants($varianttype);
        if (!empty($fields) && $category['more_batches'] == 'on') {
            $batch = ['id' => 1, 'sort_order' => 1, 'active_user_feed_rel' => []];
            foreach ($fields as $field) {
                $record = $field['attribute_name'];

                $batch += [$record => $data[$record]];
            }
        } else {
            $batch = ['id' => 1, 'sort_order' => 1, 'active_user_feed_rel' => [], 'batchname' => $data['program_title'], 'startdate' => $data['program_startdate'], 'enddate' => $data['program_enddate']];
        }
        $program_keyword = explode(',', $data['program_keyword']);
        if (!$program_keyword) {
            $program_keyword = [];
        }
        //start of moodle

        $paramlist['categoryid'] = $category['categoryid'];

        if ($data['program_title'] == $batch['batchname']) {
            $paramlist['fullname'] = $data['program_title'];
            $paramlist['shortname'] = $data['title_lower'];
            $paramlist['startdate'] = strtotime($data['program_startdate']);
        } else {
            $paramlist['fullname'] = $data['program_title'] . ' - ' . $batch['batchname'];
            $paramlist['shortname'] = $data['title_lower'] . $batch['batchname'];
            $paramlist['startdate'] = strtotime($batch['startdate']);
        }
        //$paramlist['visible']=1;
        $paramlist['visible'] = ($data['status'] == 'active') ? 1 : 0;
        //$paramlist['summary']=$data['program_description'];
        $result = $moodleapi->moodle_course_create($paramlist);
        $moodlecourseid = $result[0]['id'];
        $batch += ['lmscourseid' => $moodlecourseid];
        //end of moodle

        self::insert(
            [
                'program_id' => $uid,
                'program_title' => $data['program_title'],
                'title_lower' => $data['title_lower'],
                'program_slug' => $slug,
                'program_description' => $data['program_description'],
                'program_startdate' => (int)Timezone::convertToUTC($data['program_startdate'], Auth::user()->timezone, 'U'),
                'program_enddate' => (int)Timezone::convertToUTC($data['program_enddate'], Auth::user()->timezone, 'U'),
                'program_display_startdate' => (int)Timezone::convertToUTC($data['program_display_startdate'], Auth::user()->timezone, 'U'),
                'program_display_enddate' => (int)Timezone::convertToUTC($data['program_display_enddate'], Auth::user()->timezone, 'U'),
                'program_cover_media' => $mediaid,
                //'program_review' => $data['program_review'],
                //'program_rating' => $data['program_rating'],
                'program_visibility' => $data['program_visibility'],
                //'program_sellability' => $data['program_sellability'],
                'status' => $data['status'],
                //'program_popular' => $data['program_popular'],
                //'product_delivery_type' => $data['product_delivery_type'],
                'duration' => [
                    'label' => 'Forever',
                    'days' => 'forever'
                ],
                'program_keyword' => $program_keyword,
                'created_by_name' => $name,
                'created_by_username' => $username,
                'created_at' => time(),
                'updated_at' => time(),
                'variant' => [$batch],
            ]
        );

        $curval = $data['curval'];
        $nextval = $data['sort_order'];
        if ($curval == $nextval) {
            self::where('program_id', '=', (int)$uid)->update(['sort_order' => (int)$nextval]);
        } else {
            self::sortBanners($uid, $curval, $nextval);
        }

        return 1;
    }

    public static function checkProgramTitle($programtitle, $id)
    {
        if ($id > 0) {
            return self::where('program_title', '=', $programtitle)->orWhere('program_title', 'like', $programtitle)->where('program_id', '!=', (int)$id)->count();
        } else {
            return self::where('program_title', '=', $programtitle)->orWhere('program_title', 'like', $programtitle)->count();
        }
    }

    public static function checkLowerTitle($lowertitle, $id)
    {
        if ($id > 0) {
            return self::where('title_lower', '=', $lowertitle)->orWhere('title_lower', 'like', $lowertitle)->where('program_id', '!=', (int)$id)->count();
        } else {
            return self::where('title_lower', '=', $lowertitle)->orWhere('title_lower', 'like', $lowertitle)->count();
        }
    }

    public static function checkBatchName($id, $data, $pid)
    {
        if ($id > 0) {
            $data['batchname'] = strtolower($data['batchname']);
            $batch = self::where('program_id', '=', (int)$pid)->where('variant.batchname', '=', $data['batchname'])->get(['variant.$.id'])->toArray();
            if (!empty($batch)) {
                if ($batch[0]['variant'][0]['id'] == (int)$id) {
                    $count = 0;
                } else {
                    $count = 1;
                }
                return $count;
            } else {
                $count = 0;
                return $count;
            }
        } else {
            $data['batchname'] = strtolower($data['batchname']);
            return self::where('program_id', '=', (int)$pid)->where('variant.batchname', '=', $data['batchname'])->get(['variant.$.id'])->count();
        }
    }

     /**
     * Method to get Lms program count
     *
     * @param  string $search
     * @return object
     */
    public static function getLmsprogramListCount($search = null){

        if(!empty($search)){
            return self::where('program_title', 'like', '%'.$search.'%')->count();
        }else{
            return self::where('program_id', '>', 0)->count();
        }

    }



    public static function getLmsprogramCount($status = 'all', $search = null)
    {

        if ($status == 'all' || $status == 'ALL') {
            if ($search) {
                return self::where('program_title', 'like', '%' . $search . '%')->count();
            } else {
                return self::where('program_id', '>', 0)->count();
            }
        } else {
            if ($search) {
                return self::where('status', 'like', $status)->where('program_title', 'like', '%' . $search . '%')->count();
            } else {
                return self::where('status', 'like', $status)->where('program_id', '>', 0)->count();
            }
        }
    }

    public static function getFilteredLmsprogramsWithPagination($status = 'all', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {
        $key = key($orderby);
        $value = $orderby[$key];

        if ($status == 'all' || $status == 'ALL') {
            if ($search) {
                return self::where('program_title', 'like', '%' . $search . '%')->orderBy($key, $value)->get()->toArray();
            } else {
                return self::where('program_id', '>', 0)->orderBy($key, $value)->get()->toArray();
            }
        } else {
            if ($search) {
                return self::where('status', 'like', $status)->where('program_title', 'like', '%' . $search . '%')->orderBy($key, $value)->get()->toArray();
            } else {
                return self::where('status', 'like', $status)->where('program_id', '>', 0)->orderBy($key, $value)->get()->toArray();
            }
        }
    }

    public static function deleteLmsProgram($id)
    {
        $moodleapi = MoodleAPI::get_instance();
        $lmsprograms = self::where('program_id', '=', (int)$id)->get()->toArray();
        if (isset($lmsprograms[0]['program_cover_media']) && $lmsprograms[0]['program_cover_media']) {
            Dam::removeMediaRelation($lmsprograms[0]['program_cover_media'], ['lmscourse_media_rel'], (int)$lmsprograms[0]['program_id']);
        }
        if (!empty($lmsprograms[0]['variant'])) {
            foreach ($lmsprograms[0]['variant'] as $lmsprogram) {
                $paramlist['id'] = $lmsprogram['lmscourseid'];
                $result = $moodleapi->moodle_course_delete($paramlist);
            }
        }
        self::where('program_id', '=', (int)$id)->delete();
        return 1;
    }

    public static function getLmsProgramUsingID($id = 'ALL')
    {
        if ($id == 'ALL') {
            return self::where('program_id', '>', 0)->get()->toArray();
        } else {
            return self::where('program_id', '=', (int)$id)->get()->toArray();
        }
    }

    public static function editLmsProgram($data, $slug, $name, $username, $id, $mediaid)
    {
        $type = 'batch';
        $fields = ManageAttribute::getVariants($type);
        $moodleapi = MoodleAPI::get_instance();
        $lmsprograms = self::where('program_id', '=', (int)$id)->get()->toArray();
        $feed_media_rel = 'lmscourse_media_rel';
        Dam::removeMediaRelation($lmsprograms[0]['program_cover_media'], [$feed_media_rel], (int)$lmsprograms[0]['program_id']);
        if ($mediaid) {
            Dam::updateDAMSRelation($mediaid, $feed_media_rel, (int)$lmsprograms[0]['program_id']);
        }
        $program_keyword = explode(',', $data['program_keyword']);
        if (!$program_keyword) {
            $program_keyword = [];
        }
        self::where('program_id', '=', (int)$id)->update([
            'program_title' => $data['program_title'],
            'title_lower' => $data['title_lower'],
            'program_slug' => $slug,
            'program_description' => $data['program_description'],
            'program_startdate' => (int)Timezone::convertToUTC($data['program_startdate'], Auth::user()->timezone, 'U'),
            'program_enddate' => (int)Timezone::convertToUTC($data['program_enddate'], Auth::user()->timezone, 'U'),
            'program_display_startdate' => (int)Timezone::convertToUTC($data['program_display_startdate'], Auth::user()->timezone, 'U'),
            'program_display_enddate' => (int)Timezone::convertToUTC($data['program_display_enddate'], Auth::user()->timezone, 'U'),
            'program_cover_media' => $mediaid,
            //'program_review' => $data['program_review'],
            //'program_rating' => $data['program_rating'],
            'program_visibility' => $data['program_visibility'],
            //'program_sellability' => $data['program_sellability'],
            'status' => $data['status'],
            //'program_popular' => $data['program_popular'],
            //'product_delivery_type' => $data['product_delivery_type'],
            'program_keyword' => $program_keyword,
            'created_by_username' => $username,
            'updated_at' => time(),
        ]);
        //moodle start
        if (!empty($lmsprograms[0]['variant'])) {
            foreach ($lmsprograms[0]['variant'] as $lmsprogram) {
                $paramlist['id'] = $lmsprogram['lmscourseid'];
                //if(!isset($lmsprogram['sorto_rder'])) {
                if (empty($fields) || SiteSetting::module('Lmsprogram', 'more_batches') == 'off') {
                    $paramlist['fullname'] = $data['program_title'];
                    $paramlist['shortname'] = $data['title_lower'];
                    $paramlist['startdate'] = strtotime($data['program_startdate']);
                    //update batch name
                    //pull batch
                    self::where('program_id', '=', (int)$id)->pull('variant', ['id' => (int)$lmsprogram['id']]);

                    //push batch
                    $batch = ['id' => $lmsprogram['id'], 'lmscourseid' => $lmsprogram['lmscourseid'],
                        'sort_order' => $lmsprogram['sort_order'], 'active_user_feed_rel' => $lmsprogram['active_user_feed_rel'],
                        'batchname' => $data['program_title'], 'startdate' => $data['program_startdate'],
                        'enddate' => $data['program_enddate']];
                    self::where('program_id', '=', (int)$id)->push('variant', $batch);
                } else {
                    $paramlist['fullname'] = $data['program_title'] . ' - ' . $lmsprogram['batchname'];
                    $paramlist['shortname'] = $data['title_lower'] . $lmsprogram['batchname'];
                }
                $paramlist['visible'] = ($data['status'] == 'active') ? 1 : 0;
                $result = $moodleapi->moodle_course_update($paramlist);
            }
        }
        //moodle end
        return 1;
    }

    public static function getBatchCount($search = null)
    {
        $count = 0;

        if ($search) {
            $lmsprograms = self::where('program_id', '>', 0)->where('program_title', 'like', '%' . $search . '%')->get()->toArray();
            if (!empty($lmsprograms)) {
                foreach ($lmsprograms as $lmsprogram) {
                    $variant = self::where('program_id', '=', (int)$lmsprogram['program_id'])->value('variant');
                    $count = $count + count($variant);
                }
            }
        } else {
            $lmsprograms = self::where('program_id', '>', 0)->get()->toArray();
            if (!empty($lmsprograms)) {
                foreach ($lmsprograms as $lmsprogram) {
                    $variant = self::where('program_id', '=', (int)$lmsprogram['program_id'])->value('variant');
                    $count = $count + count($variant);
                }
            }
        }
        return $count;
    }

    public static function getvariantrelation($programid)
    {
        $variant = self::where('program_id', '=', (int)$programid)->value('variant');

        return $variant;
    }

    public static function getvariantcount($programid)
    {
        $variant = self::where('program_id', '=', (int)$programid)->value('variant');
        return count($variant);
    }

    public static function getbatchcounts($search = null, $pid)
    {

        $variant = self::where('program_id', '=', (int)$pid)->value('variant');
        return count($variant);
    }

    public static function getBatchListAjaxs($pid)
    {
        $variant = self::where('program_id', '=', (int)$pid)->value('variant');
        $variant = array_values($variant);
        $collection = collect($variant);
        $sorted = $collection->sortBy('sort_order');
        $sorted->values()->all();
        return $sorted;
    }

    public static function getBatchMaxOrder($pid)
    {
        $variant = ManageLmsProgram::where('program_id', '=', (int)$pid)->value('variant');
        $variant = array_values($variant);
        $collection = collect($variant);

        $sorted = $collection->sortBy('sort_order');

        $max = 0;
        foreach ($sorted as $k => $v) {
            //$v['sort_order']=(int) $v['sort_order'];

            $max = max([$max, $v['sort_order']]);
        }

        return $max;
    }

    public static function deletebatch($id, $pid)
    {
        $moodleapi = MoodleAPI::get_instance();
        $lmscourse = self::where('program_id', '=', (int)$pid)->where('variant.id', '=', (int)$id)->get(['variant.$.id'])->toArray();
        $paramlist['id'] = $lmscourse[0]['variant'][0]['lmscourseid'];
        $result = $moodleapi->moodle_course_delete($paramlist);
        self::where('program_id', '=', (int)$pid)->pull('variant', ['id' => (int)$id]);
    }

    public static function getBatchUsingID($id, $pid)
    {
        return self::where('program_id', '=', (int)$pid)->where('variant.id', '=', (int)$id)->get(['variant.$.id'])->toArray();
    }

    public static function getBatchSort($pid)
    {
        $record = self::where('program_id', '=', (int)$pid)->get(['variant.sort_order'])->toArray();

        $record = $record[0]['variant'];

        $arrayTemp = [];
        $i = 0;
        if (!empty($record)) {
            foreach ($record as $key => $val) {
                $arrayTemp[$i] = $val['sort_order'];
                $i++;
            }


            $sortid = max($arrayTemp) + 1;
        } else {
            $sortid = 1;
        }

        return $sortid;
    }

    public static function updateBatch($data, $id, $pid)
    {
        $moodleapi = MoodleAPI::get_instance();
        $lmsprogram = self::where('program_id', '=', (int)$pid)->get()->toArray();
        $curbatch = self::where('program_id', '=', (int)$pid)->where('variant.id', '=', (int)$id)->get(['variant.$.id'])->toArray();

        $id = (int)$id;
        self::where('program_id', '=', (int)$pid)->pull('variant', ['id' => (int)$id]);
        $type = 'batch';
        $fields = ManageAttribute::getVariants($type);
        $batch = ['id' => $id, 'lmscourseid' => $curbatch[0]['variant'][0]['lmscourseid'],
            'active_user_feed_rel' => $curbatch[0]['variant'][0]['active_user_feed_rel']];
        foreach ($fields as $field) {
            $element = $field['attribute_name'];
            $batch += [$element => strtolower($data[$element])];
        }
        $curval = $data['curval'];
        $nextval = $data['sort_order'];
        if ($curval == $nextval) {
            $batch += ['sort_order' => (int)$nextval];
            self::where('program_id', '=', (int)$pid)->push('variant', $batch);
            //moodlestart
            $paramlist['id'] = $curbatch[0]['variant'][0]['lmscourseid'];
            $paramlist['fullname'] = $lmsprogram[0]['program_title'] . ' - ' . $batch['batchname'];
            $paramlist['shortname'] = $lmsprogram[0]['title_lower'] . $batch['batchname'];
            $paramlist['startdate'] = strtotime($batch['startdate']);
            $paramlist['visible'] = ($lmsprogram[0]['status'] == 'active') ? 1 : 0;
            $result = $moodleapi->moodle_course_update($paramlist);
            //moodleend
        } else {
            $b = self::where('program_id', '=', (int)$pid)->value('variant');
            $b = array_values($b);
            foreach ($b as $bb) {
                if ($bb['sort_order'] == $nextval) {
                    $type = 'batch';
                    $fields = ManageAttribute::getVariants($type);
                    $batchs = ['id' => $bb['id'], 'sort_order' => (int)$curval, 'lmscourseid' => $bb['lmscourseid'],
                        'active_user_feed_rel' => $bb['active_user_feed_rel']];
                    foreach ($fields as $field) {
                        $element = $field['attribute_name'];
                        $batchs += [$element => strtolower($bb[$element])];
                    }
                    self::where('program_id', '=', (int)$pid)->pull('variant', ['id' => (int)$batchs['id']]);
                    self::where('program_id', '=', (int)$pid)->push('variant', $batchs);
                    //moodle start
                    $paramlist['id'] = $bb['lmscourseid'];
                    $paramlist['fullname'] = $lmsprogram[0]['program_title'] . ' - ' . $batchs['batchname'];
                    $paramlist['shortname'] = $lmsprogram[0]['title_lower'] . $batchs['batchname'];
                    $paramlist['startdate'] = strtotime($batchs['startdate']);
                    $paramlist['visible'] = ($lmsprogram[0]['status'] == 'active') ? 1 : 0;
                    $result = $moodleapi->moodle_course_update($paramlist);
                    //moodle end
                }
            }

            $batch += ['sort_order' => (int)$nextval];
            self::where('program_id', '=', (int)$pid)->push('variant', $batch);
            //moodle start
            $paramlist['id'] = $curbatch[0]['variant'][0]['lmscourseid'];
            $paramlist['fullname'] = $lmsprogram[0]['program_title'] . ' - ' . $batch['batchname'];
            $paramlist['shortname'] = $lmsprogram[0]['title_lower'] . $batch['batchname'];
            $paramlist['startdate'] = strtotime($batch['startdate']);
            $paramlist['visible'] = ($lmsprogram[0]['status'] == 'active') ? 1 : 0;
            $result = $moodleapi->moodle_course_update($paramlist);
            //moodle end
        }

        return 1;
    }

    public static function sortBanners($id, $curval, $nextval)
    {
        $curval = (int)$curval;
        $nextval = (int)$nextval;

        if ($curval < $nextval) {
            $curval = $curval + 1;
            $nxtorders = self::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$curval, $nextval])->orderBy('sort_order', 'asc')->get(['sort_order', 'program_id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                self::where('program_id', '=', $nxtorder['program_id'])->decrement('sort_order');
            }
            return self::where('program_id', '=', (int)$id)->update(['sort_order' => $nextval]);
        }

        if ($curval > $nextval) {
            $curval = $curval - 1;
            $nxtorders = self::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$nextval, $curval])->orderBy('sort_order', 'asc')->get(['sort_order', 'program_id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                self::where('program_id', '=', $nxtorder['program_id'])->increment('sort_order');
            }
            return self::where('program_id', '=', (int)$id)->update(['sort_order' => $nextval]);
        }
    }

    public static function sortBatchList($id, $curval, $nextval, $pid)
    {

        $moodleapi = MoodleAPI::get_instance();
        $lmsprogram = self::where('program_id', '=', (int)$pid)->get()->toArray();
        $curbatch = self::where('program_id', '=', (int)$pid)->where('variant.id', '=', (int)$id)->get(['variant.$.id'])->toArray();

        $curval = (int)$curval;
        $nextval = (int)$nextval;

        $a = self::where('program_id', '=', (int)$pid)->value('variant');
        $a = array_values($a);
        foreach ($a as $aa) {
            if ($aa['id'] == $id) {
                $type = 'batch';
                $fields = ManageAttribute::getVariants($type);
                $batch = ['id' => $aa['id'], 'lmscourseid' => $aa['lmscourseid'], 'active_user_feed_rel' => $aa['active_user_feed_rel']];
                if (empty($fields) || SiteSetting::module('Lmsprogram', 'more_batches') == 'off') {
                    $batch += ['batchname' => $aa['batchname'], 'startdate' => $aa['startdate'], 'enddate' => $aa['enddate']];
                } else {
                    foreach ($fields as $field) {
                        $element = $field['attribute_name'];
                        $batch += [$element => $aa[$element]];
                    }
                }
            }
        }

        $b = self::where('program_id', '=', (int)$pid)->value('variant');
        $b = array_values($b);
        foreach ($b as $bb) {
            if ($bb['sort_order'] == $nextval) {
                $type = 'batch';
                $fields = ManageAttribute::getVariants($type);
                $batchs = ['id' => $bb['id'], 'sort_order' => (int)$curval, 'lmscourseid' => $bb['lmscourseid'], 'active_user_feed_rel' => $bb['active_user_feed_rel']];
                foreach ($fields as $field) {
                    $element = $field['attribute_name'];
                    $batchs += [$element => $bb[$element]];
                }
                self::where('program_id', '=', (int)$pid)->pull('variant', ['id' => (int)$batchs['id']]);
                self::where('program_id', '=', (int)$pid)->push('variant', $batchs);
            }
        }

        self::where('program_id', '=', (int)$pid)->pull('variant', ['id' => (int)$batch['id']]);
        $batch += ['sort_order' => (int)$nextval];
        self::where('program_id', '=', (int)$pid)->push('variant', $batch);
    }

    public static function removeLmscoureseCoverMedia($cids = [])
    {
        self::whereIn('program_id', $cids)->update(['program_cover_media' => '']);
    }

    public static function updateFeedRelation($pid, $arrname, $updateArr, $bid, $cid, $overwrite = false)
    {

        $batch = self::where('program_id', '=', (int)$pid)->where('variant.id', '=', (int)$bid)->get(['variant.$.id'])->toArray();
        $newbatch = [];
        foreach ($batch[0]['variant'][0] as $k1 => $k2) {
            if ($k1 != 'active_user_feed_rel') {
                $newbatch[$k1] = $k2;
            }
        }
        $newbatch['active_user_feed_rel'] = $updateArr;
        self::where('program_id', '=', (int)$pid)->pull('variant', ['id' => (int)$bid]);
        self::where('program_id', '=', (int)$pid)->push('variant', $newbatch, true);
        return true;
    }

    public static function getBatchDetails($lmscourseid)
    {
        $program = self::where('program_id', '>', 0)
            ->where('variant.lmscourseid', '=', (int)$lmscourseid)
            ->get(['program_title', 'program_description', 'program_cover_media', 'variant.$.id'])
            ->toArray();
        if (array_get($program, '0.variant.0')) {
            $batch['batchname'] = $program[0]['variant'][0]['batchname'];
            $batch['startdate'] = $program[0]['variant'][0]['startdate'];
            $batch['enddate'] = $program[0]['variant'][0]['enddate'];
            $batch['coursename'] = $program[0]['program_title'];
            $batch['coursedesc'] = $program[0]['program_description'];
            $batch['media'] = $program[0]['program_cover_media'];
            return $batch;
        } else {
            return null;
        }
    }

    public static function getLmsProgramDetails()
    {
        return self::where('program_id', '>', 0)->get()->toArray();
    }

    public static function getCourseList()
    {
        $moodleapi = MoodleAPI::get_instance();
        return $courses = $moodleapi->moodle_get_course_list();
    }

    public static function enrolUser($courseid, $batchid, $programid)
    {
        $new_users = [];
        $oldusers = [];
        $mdluser = [];
        $moodleapi = MoodleAPI::get_instance();
        $batch = self::where('program_id', '=', (int)$programid)->where('variant.lmscourseid', '=', (int)$courseid)->get(['variant.$.id'])->toArray();
        $i = 0;
        if (!empty($batch[0]['variant'][0]['active_user_feed_rel'])) {
            foreach ($batch[0]['variant'][0]['active_user_feed_rel'] as $uid) {
                $user = User::getUsersUsingID($uid);
                $user_id = array_get($user, '0.userid');
                if (isset($user_id)) {
                    $new_users[$i] = $user_id;
                    $i++;
                }
            }
        }

        $param['courseid'] = $courseid;
        $oldusers = $moodleapi->moodle_enrol_get($param);

        $j = 0;
        if (is_array($oldusers)) {
            foreach ($oldusers as $olduser) {
                $mdluser[$j] = $olduser['id'];
                $j++;
            }
        }

        $enrol = array_diff($new_users, $mdluser); //enrol
        $unenrol = array_diff($mdluser, $new_users); //unenrol
        if (!empty($enrol)) {
            foreach ($enrol as $en) {
                $enrolparam['roleid'] = 5;
                $enrolparam['userid'] = $en;
                $enrolparam['courseid'] = $courseid;
                $moodleapi->moodle_enrol($enrolparam);
            }
        }
        if (!empty($unenrol)) {
            foreach ($unenrol as $unen) {
                $unenrolparam['roleid'] = 5;
                $unenrolparam['userid'] = $unen;
                $unenrolparam['courseid'] = $courseid;
                $moodleapi->moodle_unenrol($unenrolparam);
            }
        }
    }

    public static function getBatchUsersCount($batchid, $programid)
    {
        $batch = self::where('program_id', '=', (int)$programid)->where('variant.id', '=', (int)$batchid)->get(['variant.$.id'])->toArray();
        return $batch[0]['variant'][0]['active_user_feed_rel'];
    }

    public static function checkEnrolUserCount($pid)
    {
        $count = 0;
        $records = self::where('program_id', '=', (int)$pid)->get(['variant.active_user_feed_rel'])->toArray();
        if (empty($records[0]['variant'])) {
            return $count;
        } else {
            foreach ($records[0]['variant'] as $record) {
                if (!empty($record['active_user_feed_rel'])) {
                    $count = 1;
                    return $count;
                }
            }
            return $count;
        }
    }
}
