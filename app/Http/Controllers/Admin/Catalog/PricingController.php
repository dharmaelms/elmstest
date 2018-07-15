<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Events\Elastic\Programs\ProgramAdded;
use App\Events\Elastic\Programs\ProgramRemoved;
use App\Events\Elastic\Programs\ProgramUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Batch\BatchStorePostRequest;
use App\Model\Packet;
use App\Model\Program;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Country\ICountryService;
use Input;
use URL;
use Validator;

class PricingController extends Controller
{
    protected $priceService = null;
    protected $countryService = null;
    protected $countryList = null;

    public function __construct(
        IPricingService $priceService,
        ICountryService $countryService
    )
    {


        $this->priceService = $priceService;
        $this->countryService = $countryService;
        $this->countryList = $this->countryService->supportedCurrencies();
    }

    /**
     * [postSaveSellability - Save new Subscription]
     * @method postSaveSellability
     * @return [type]              [redirecting to channel]
     * @author Rudragoud Patil
     */
    public function postSaveSellability()
    {
        $data = Input::all();
        $rules = [
            'title' => 'Required|subdublicate|regex:/^[a-zA-Z0-9- ]+$/',
            'duration' => 'Required'
        ];
        Validator::extend('subdublicate', function ($attribute, $value, $parameters) {
            $data = Input::all();
            return $this->priceService->checkDubSubscription($data['sellable_id'], $data['sellable_type'], $data['title']);
        });
        Validator::extend('mark_comapre', function ($attribute, $value, $parameters) {
            $data = Input::all();
            $str = explode("mark_", $attribute);
            if ($data[$str[1]] >= $data[$attribute]) {
                return true;
            }
            return false;
        });
        if ($data['subscription_type'] === "paid") {
            if (!empty($this->countryList)) {
                foreach ($this->countryList as $value) {
                    $rules = array_merge($rules, [strtolower($value['currency_code']) => "required"]);
                    $rules = array_merge($rules, ["mark_" . strtolower($value['currency_code']) => "mark_comapre"]);
                }
            }
        }
        $slug = Input::get('program_slug');
        $messages = [
            'subdublicate' => 'The title field is duplicate.',
            'mark_comapre' => 'The discounted price must be less than price.',
            'regex' => 'The title allows only alphanumeric, space and hyphen.'
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/contentfeedmanagement/edit-feed/' . $slug)->withInput()
                ->withErrors($validation)->with('pricing', 'enabled')->with('pricing_action', 'add');
        } elseif ($validation->passes()) {
            $msg = trans('admin/program.content_feed_add_success');
            $type = ['type' => Input::get('subscription_type')];
            $duration = ['duration_count' => $data['duration']];
            $data = array_merge($data, $type, $duration);
            $pv_data = $this->priceService->addPrice($data);
            $pv_data = $this->priceService->priceFirst($data);
            $this->priceService->addSubscriptions($pv_data, $data);

            return redirect('cp/contentfeedmanagement/edit-feed/' . Input::get('program_slug'))
                ->with('success_price', "Subscription added successfully")
                ->with('pricing', 'enabled');
        }
    }

    /**
     * [postSaveEditSellability Save-Edit Pricing]
     * @method postSaveEditSellability
     * @return [type]                  [description]
     * @author Rudragoud Patil
     */
    public function postSaveEditSellability()
    {
        $data = Input::all();
        $rules = [
            'title' => 'Required|subdublicate|regex:/^[a-zA-Z0-9- ]+$/',
            'duration' => 'Required'
        ];
        Validator::extend('subdublicate', function ($attribute, $value, $parameters) {
            $data = Input::all();
            return $this->priceService->checkDubSubscription($data['sellable_id'], $data['sellable_type'], $data['title'], $data['ctitle']);
        });
        Validator::extend('mark_comapre', function ($attribute, $value, $parameters) {
            $data = Input::all();
            $str = explode("mark_", $attribute);
            if ($data[$str[1]] >= $data[$attribute]) {
                return true;
            }
            return false;
        });

        if ($data['subscription_type'] === "paid") {
            if (!empty($this->countryList)) {
                foreach ($this->countryList as $value) {
                    $rules = array_merge($rules, [strtolower($value['currency_code']) => "required"]);
                    $rules = array_merge($rules, ["mark_" . strtolower($value['currency_code']) => "mark_comapre"]);
                }
            }
        }
        $slug = Input::get('program_slug');
        $messages = [
            'subdublicate' => 'The title field is duplicate.',
            'mark_comapre' => 'The discounted price must be less than price.',
            'regex' => 'The title allows only alphanumeric, space and hyphen.'
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        $json = [];
        foreach ($data as $key => $value) {
            $json[$key] = '';
        }
        foreach ($validation->getMessageBag()->toArray() as $key => $eachError) {
            $json = array_merge($json, [$key => $eachError[0]]);
        }
        if ($validation->fails()) {
            $json = array_merge($json, ['success' => 'error']);
            return response()->json($json);
        } elseif ($validation->passes()) {
            $msg = trans('admin/program.content_feed_add_success');
            $type = ['type' => Input::get('subscription_type')];
            $duration = ['duration_count' => $data['duration']];
            $data = array_merge($data, $type, $duration);
            $pv_data = $this->priceService->priceFirst($data);
            $this->priceService->updateSubscription($pv_data, $data);
            $json = ['success' => URL::to("cp/pricing/route-to-channel/$slug")];
            return response()->json($json);
        }
    }

    /**
     * [getRouteToChannel - rediects to Pricing on ajax edit]
     * @method getRouteToChannel
     * @param  [type]            $slug [description]
     * @return [type]                  [description]
     * @author Rudragoud Patil
     */
    public function getRouteToChannel($slug)
    {
        return redirect('cp/contentfeedmanagement/edit-feed/' . $slug)
            ->with('success_price', "Subscription edited successfully")
            ->with('pricing', 'enabled');
    }

    /**
     * [getDeleteSubscription delete Subscription]
     * @method getDeleteSubscription
     * @param  [type]                $subTitle     [title of Subscription]
     * @param  [type]                $sal_id       [Subscription type ID]
     * @param  [type]                $sal_type     [Subscription Type like Channel,Product,Course,etc]
     * @param  [type]                $program_slug [channel Slug]
     * @return [type]                              [description]
     * @author Rudragoud Patil
     */
    public function getDeleteSubscription($subTitle, $sal_id, $sal_type, $program_slug)
    {
        $data = [
            'sellable_id' => $sal_id,
            'sellable_type' => $sal_type,
            'title' => $subTitle
        ];
        $pv_data = $this->priceService->priceFirst($data);
        $this->priceService->deleteSubscriptions($pv_data, $data);
        return redirect('cp/contentfeedmanagement/edit-feed/' . $program_slug)
            ->with('pricing', 'enabled')
            ->with('success_price', "Subscription deleted successfully");
    }

    /**
     * [postEditSubscription load Edit form for ]
     * @method postEditSubscription
     * @return [type]               [description]
     * @author Rudragoud Patil
     */
    public function postEditSubscription()
    {
        $data = Input::all();
        $editItem['sellable_type'] = $data['sellable_type'];
        $editItem['sellable_id'] = $data['sellable_id'];
        $editItem['program_slug'] = $data['program_slug'];

        $sellableEntity = $this->priceService->getPricing($data);
        if (!empty($sellableEntity)) {
            foreach ($sellableEntity['subscription'] as $eachval) {
                if ($eachval['title'] === $data['slug']) {
                    $editItem['subdata'] = $eachval;
                }
            }
        }
        echo view(
            'admin/theme/Catalog/Pricing/edit_subscription',
            ['subscription' => $editItem, 'currency_support_list' => $this->countryList]
        );
    }

    public function getAddPrice($slug, $p_type = null)
    {
        if (empty($p_type)) {
            return redirect('cp/contentfeedmanagement/edit-feed/' . $slug)
                ->with('pricing', 'enabled')
                ->with('ap_success', 'Channel is added successfully');
        } else {
            return redirect('cp/contentfeedmanagement/edit-product/' . $slug)
                ->with('pricing', 'enabled')
                ->with('ap_success', 'Product is added successfully');
        }
    }

    public function postAddVariant()
    {
        $data = Input::all();
        $rules = [
            'title' => 'Required|regex:/^[a-zA-Z0-9- ]+$/|subdublicate'
        ];
        Validator::extend('mark_comapre', function ($attribute, $value, $parameters) {
            $data = Input::all();
            $str = explode("mark_", $attribute);
            if ($data[$str[1]] >= $data[$attribute]) {
                return true;
            }
            return false;
        });
        Validator::extend('subdublicate', function ($attribute, $value, $parameters) {
            $data = Input::all();
            return $this->priceService->checkDubSubscription($data['sellable_id'], $data['sellable_type'], $data['title']);
        });
        if ($data['subscription_type'] === "paid") {
            if (!empty($this->countryList)) {
                foreach ($this->countryList as $value) {
                    $rules = array_merge($rules, [strtolower($value['currency_code']) => "required"]);
                    $rules = array_merge($rules, ["mark_" . strtolower($value['currency_code']) => "mark_comapre"]);
                }
            }
        }
        $messages = [
            'subdublicate' => 'The title field is duplicate.',
            'mark_comapre' => 'The discounted price must be less than price.',
            'regex' => 'The title allows only alphanumeric, space and hyphen.'
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            $slug = Input::get('program_slug');
            return redirect('cp/contentfeedmanagement/edit-product/' . $slug)->withInput()
                ->withErrors($validation)->with('pricing', 'enabled')->with('pricing_action', 'add');
        } elseif ($validation->passes()) {
            $type = ['type' => Input::get('subscription_type')];
            $data = array_merge($data, $type);
            $this->priceService->addPrice($data);
            $pv_data = $this->priceService->priceFirst($data);
            $this->priceService->addVertical($pv_data, $data);
            $slug = Input::get('program_slug');
            return redirect('cp/contentfeedmanagement/edit-product/' . $slug)
                ->with('success_price', "Variant added successfully")
                ->with('pricing', 'enabled');
        }
    }

    public function postEditVariant()
    {
        $data = Input::all();
        $editItem['sellable_type'] = $data['sellable_type'];
        $editItem['sellable_id'] = $data['sellable_id'];
        $editItem['program_slug'] = $data['program_slug'];
        $editItem['subdata'] = $this->priceService->getVerticalBySlug($data, $data['slug']);
        echo view(
            'admin/theme/Catalog/Pricing/product/edit_subscription',
            ['subscription' => $editItem, 'currency_support_list' => $this->countryList]
        );
    }

    public function postSaveVariant()
    {
        $data = Input::all();
        $rules = [
            'title' => 'Required|regex:/^[a-zA-Z0-9- ]+$/|subdublicate'
        ];
        Validator::extend('mark_comapre', function ($attribute, $value, $parameters) {
            $data = Input::all();
            $str = explode("mark_", $attribute);
            if ($data[$str[1]] >= $data[$attribute]) {
                return true;
            }
            return false;
        });
        Validator::extend('subdublicate', function ($attribute, $value, $parameters) {
            $data = Input::all();
            return $this->priceService->checkDubSubscription($data['sellable_id'], $data['sellable_type'], $data['title'], $data['ctitle']);
        });
        if ($data['subscription_type'] === "paid") {
            if (!empty($this->countryList)) {
                foreach ($this->countryList as $value) {
                    $rules = array_merge($rules, [strtolower($value['currency_code']) => "required"]);
                    $rules = array_merge($rules, ["mark_" . strtolower($value['currency_code']) => "mark_comapre"]);
                }
            }
        }
        $slug = Input::get('program_slug');
        $messages = [
            'subdublicate' => 'The title field is duplicate.',
            'mark_comapre' => 'The discounted price must be less than price.',
            'regex' => 'The title allows only alphanumeric, space and hyphen.'
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        $json = [];
        foreach ($data as $key => $value) {
            $json[$key] = '';
        }
        foreach ($validation->getMessageBag()->toArray() as $key => $eachError) {
            $json = array_merge($json, [$key => $eachError[0]]);
        }
        if ($validation->fails()) {
            $json = array_merge($json, ['success' => 'error']);
            return response()->json($json);
        } elseif ($validation->passes()) {
            $data = Input::all();
            $type = ['type' => Input::get('subscription_type')];
            $data = array_merge($data, $type);
            $pv_data = $this->priceService->priceFirst($data);
            $this->priceService->updateVertical($pv_data, $data);
            $slug = Input::get('program_slug');
            $json = ['success' => URL::to("cp/pricing/variant/$slug")];
            return response()->json($json);
        }
    }

    public function getDeleteVariant($subTitle, $sal_id, $sal_type, $program_slug)
    {
        $data = [
            'sellable_id' => $sal_id,
            'sellable_type' => $sal_type,
            'title' => $subTitle
        ];
        $pv_data = $this->priceService->priceFirst($data);
        $this->priceService->deleteVertical($pv_data, $data);
        return redirect('cp/contentfeedmanagement/edit-product/' . $program_slug)
            ->with('pricing', 'enabled')
            ->with('success_price', "Variant deleted successfully");
    }

    public function getVariant($slug)
    {
        return redirect('cp/contentfeedmanagement/edit-product/' . $slug)
            ->with('success_price', "Variant edited successfully")
            ->with('pricing', 'enabled');
    }

    public function postAddBatch(BatchStorePostRequest $request)
    {
        $slug = Input::get('program_slug');
        $data = Input::all();
        $type = ['type' => Input::get('subscription_type'), 'title' => Input::get('batch_name'), 'desc' => ''];
        $course_id = $this->copyCourseByMasterCourse(
            $slug,
            Input::get('batch_name'),
            Input::get('batch_description'),
            Input::get('batch_start_date'),
            Input::get('batch_end_date')
        );
        $batch_info = [
            'batch_name' => Input::get('batch_name'),
            'batch_location' => Input::get('batch_location'),
            'batch_description' => Input::get('batch_description'),
            'batch_minimum_enrollment' => Input::get('batch_minimum_enrollment'),
            'batch_maximum_enrollment' => Input::get('batch_maximum_enrollment'),
            'batch_start_date' => Input::get('batch_start_date'),
            'batch_end_date' => Input::get('batch_end_date'),
            'batch_last_enrollment_date' => Input::get('batch_last_enrollment_date'),
            'batch_enrolled' => 0,
            'course_id' => $course_id
        ];
        $data = array_merge($data, $type);
        $this->priceService->addPrice($data);
        $pv_data = $this->priceService->priceFirst($data);
        $this->priceService->addVertical($pv_data, $data, $batch_info);
        return redirect('cp/contentfeedmanagement/edit-course/' . $slug)
            ->with('success_price', "Batch added successfully")
            ->with('pricing_action', 'list')
            ->with('pricing', 'enabled');
    }

    public function postEditBatch()
    {
        $data = Input::all();
        $editItem['sellable_type'] = $data['sellable_type'];
        $editItem['sellable_id'] = $data['sellable_id'];
        $editItem['program_slug'] = $data['program_slug'];
        $editItem['subdata'] = $this->priceService->getVerticalBySlug($data, $data['slug']);
        echo view(
            'admin/theme/Catalog/Pricing/batch/__update',
            ['batch_info' => $editItem, 'currency_code_list' => $this->countryList, 'program_sellability' => $data['program_sellability']]
        );
    }

    public function postSaveBatch(BatchStorePostRequest $request)
    {
        $data = Input::all();
        $type = ['type' => Input::get('subscription_type'), 'title' => Input::get('batch_name'), 'desc' => ''];
        $batch_info = [
            'batch_name' => Input::get('batch_name'),
            'batch_description' => Input::get('batch_description'),
            'batch_location' => Input::get('batch_location'),
            'batch_minimum_enrollment' => Input::get('batch_minimum_enrollment'),
            'batch_maximum_enrollment' => Input::get('batch_maximum_enrollment'),
            'batch_start_date' => Input::get('batch_start_date'),
            'batch_end_date' => Input::get('batch_end_date'),
            'batch_last_enrollment_date' => Input::get('batch_last_enrollment_date'),
            'batch_enrolled' => Input::get('batch_enrolled'),
            'course_id' => Input::get('course_id')
        ];
        $data = array_merge($data, $type);
        $pv_data = $this->priceService->priceFirst($data);
        $this->priceService->updateVertical($pv_data, $data, $batch_info);
        $this->updateCourse(
            Input::get('ctitle'),
            Input::get('batch_name'),
            Input::get('batch_start_date'),
            Input::get('batch_end_date'),
            Input::get('course_id')
        );
        Program::where('program_id', (int)$data['course_id'])
        ->update(['program_title' => $data['batch_name'], 'program_description' => $data['batch_description']]);
        if (config('elastic.service')) {
            event(new ProgramUpdated((int)$data['course_id'], true));
        }
        $slug = Input::get('program_slug');
        $json = ['success' => URL::to("cp/pricing/batch/$slug")];
        return response()->json($json);
    }

    public function getBatch($slug)
    {
        return redirect('cp/contentfeedmanagement/edit-course/' . $slug)
            ->with('success_price', "Batch edited successfully")
            ->with('pricing_action', 'list')
            ->with('pricing', 'enabled');
    }

    public function getDeleteBatch($subTitle, $sal_id, $sal_type, $program_slug)
    {
        $data = [
            'sellable_id' => $sal_id,
            'sellable_type' => $sal_type,
            'title' => $subTitle
        ];
        $pv_data = $this->priceService->priceFirst($data);
        $collection = collect($pv_data['vertical']);
        $course_id = null;
        $filtered = $collection->filter(function ($item) use ($subTitle, &$course_id) {
            if ($item['title'] === $subTitle) {
                $course_id = $item['course_id'];
            }
        });
        $this->priceService->deleteVertical($pv_data, $data);
        $this->deleteCourse($subTitle, $course_id);
        return redirect('cp/contentfeedmanagement/edit-course/' . $program_slug)
            ->with('pricing', 'enabled')
            ->with('success_price', "Batch deleted successfully");
    }

    public function copyCourseByMasterCourse($course_slug, $batch_name = 'no-name', $description, $start_date, $end_date)
    {

        $programs = Program::getAllProgramByIDOrSlug('course', $course_slug)->first();
        //dd($programs);
        $programs = array_except($programs->toArray(), ['_id', 'to', 'remove']);
        if (isset($programs['parent_id'])) {
            $parent_id = $programs['parent_id'];
            if ($parent_id == 0) {
                $parent_id = $programs['program_id'];
            }
        } else {
            $parent_id = 0;
        }

        $program_id = Program::uniqueProductId();
        $program_title = $batch_name;
        $title_lower = strtolower($batch_name);
        $program_slug = strtolower(str_replace(" ", "-", $batch_name));

        $programs = array_merge($programs, [
            'program_title' => $program_title,
            'program_description' => $description,
            'title_lower' => $title_lower,
            'program_slug' => "course-" . $program_slug . "-c" . $program_id,
            'program_id' => $program_id,
            'parent_id' => $parent_id,
            'program_startdate' => strtotime($start_date),
            'program_enddate' => strtotime($end_date),
            'program_display_startdate' => strtotime($start_date),
            'program_display_enddate' => strtotime($end_date),
            'created_at' => time(),
            'updated_at' => time()
        ]);
        Program::Insert($programs);
        if (config('elastic.service')) {
            event(new ProgramAdded($program_id));
        }
        return $program_id;
    }

    public function updateCourse($batch_name, $updated_batch_name, $start_date, $end_date, $course_id)
    {
        $program_title = $updated_batch_name;
        $title_lower = strtolower($updated_batch_name);
        $program_slug = "course-" . strtolower(str_replace(" ", "-", $batch_name)) . "-c" . $course_id;
        $update_program_slug = "course-" . strtolower(str_replace(" ", "-", $updated_batch_name)) . "-c" . $course_id;
        $programs = [
            'program_title' => $program_title,
            'title_lower' => $title_lower,
            'program_slug' => $update_program_slug,
            'program_startdate' => strtotime($start_date),
            'program_enddate' => strtotime($end_date),
            'program_display_startdate' => strtotime($start_date),
            'program_display_enddate' => strtotime($end_date),
            'created_at' => time(),
            'updated_at' => time()
        ];
        Program::where('program_slug', '=', $program_slug)
        ->where('program_type', '=', 'course')
        ->where('status', '!=', 'DELETED')
        ->update($programs);
        $program = Program::where('program_slug', '=', $update_program_slug)
            ->where('program_type', '=', 'course')
            ->where('status', '!=', 'DELETED')
            ->get()
            ->first();
        $slug_changed = $program_slug != $update_program_slug;
        if (config('elastic.service')) {
            event(new ProgramUpdated($program->program_id, $slug_changed));
        }
        if ($batch_name != $updated_batch_name) {
            Packet::where('feed_slug', '=', $program_slug)->update(['feed_slug' => $update_program_slug]);
        }
    }

    public function deleteCourse($batch_name, $course_id)
    {
        $program_slug = 'course-' . strtolower(str_replace(" ", "-", $batch_name)) . '-c' . $course_id;
        $program_id = Program::deleteCourse($program_slug);
        if (config('elastic.service')) {
            event(new ProgramRemoved($program_id));
        }
    }
}
