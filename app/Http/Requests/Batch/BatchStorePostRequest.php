<?php

namespace App\Http\Requests\Batch;

use App\Http\Requests\Request;
use App\Model\Program;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Country\ICountryService;
use Input;
use Session;
use Validator;
use Auth;
use Timezone;

/**
 * Class BatchStorePostRequest
 * @package App\Http\Requests\Batch
 */
class BatchStorePostRequest extends Request
{
    protected $countryList = null;

    /**
     * BatchStorePostRequest constructor.
     * @param ICountryService $countryService
     * @param IPricingService $priceService
     */
    public function __construct(ICountryService $countryService, IPricingService $priceService)
    {
        $this->countryService = $countryService;
        $this->countryList = $this->countryService->supportedCurrencies();
        $this->priceService = $priceService;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        Session::flash('pricing', 'enabled');
        Session::flash('pricing_action', 'add');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //Enrollment Validation
        Validator::extend('minimum_enrollment_less_than_manimum_enrollemnt', function ($attribute, $value, $parameters) {
            return (Input::get('batch_minimum_enrollment') <= Input::get('batch_maximum_enrollment')) ? true : false;
        });

        //Date Validation
        Validator::extend('start_date_less_than_end_date', function ($attribute, $value, $parameters) {
            return (strtotime(Input::get('batch_start_date')) <= strtotime(Input::get('batch_end_date'))) ? true : false;
        });

        //Last Enrollment Date Validation
        Validator::extend('start_date_less_than_enrollment_date', function ($attribute, $value, $parameters) {
            return (strtotime(Input::get('batch_last_enrollment_date')) <= strtotime(Input::get('batch_start_date'))) ? true : false;
        });

        //Price Validation
        Validator::extend('mark_comapre', function ($attribute, $value, $parameters) {
            $data = Input::all();
            $str = explode("mark_", $attribute);
            if ($data[$str[1]] >= $data[$attribute]) {
                return true;
            }
            return false;
        });

        //Dublicate Batches.
        Validator::extend('subdublicate', function ($attribute, $value, $parameters) {
            $data = array_merge(Input::all(), ['title' => Input::get('batch_name')]);
            if (Input::has('ctitle')) {
                return $this->priceService->checkDubSubscription($data['sellable_id'], $data['sellable_type'], $data['title'], $data['ctitle']);
            } else {
                return $this->priceService->checkDubSubscription($data['sellable_id'], $data['sellable_type'], $data['batch_name']);
            }
        });

        //Batch Dates must between course dates
        Validator::extend('batch_dates_between_course_date', function ($attribute, $value, $parameters) {
            $programs = Program::getAllProgramByIDOrSlug('course', Input::get('program_slug'))->first();
            if (strtotime(Input::get('batch_start_date')) >= Timezone::convertToUTC($programs['program_startdate'], Auth::user()->timezone, 'U')) {
                if (Timezone::convertToUTC($programs['program_enddate'], Auth::user()->timezone, 'U') >= strtotime(Input::get('batch_end_date'))) {
                    return true;
                }
                //return true;
            }
            return false;
        });

        //Enrollment Validation
        Validator::extend('enrolled_valiadtion', function ($attribute, $value, $parameters) {
            if (Input::has('batch_enrolled')) {
                return (Input::get('batch_enrolled') <= Input::get('batch_maximum_enrollment')) ? true : false;
            } else {
                return true;
            }
        });

        Validator::extend('after_enrollement', function ($attribute, $value, $parameters) {
            if (Input::has('batch_enrolled')) {
                if (Input::get('batch_enrolled') == 0) {
                    return true;
                }
                if (Input::has('ctitle')) {
                    if (Input::get('ctitle') === Input::get('batch_name')) {
                        return true;
                    } else {
                        return false;
                    }
                }
                return false;
            } else {
                return true;
            }
        });

        $rules = [];
        if (!empty($this->countryList)) {
            foreach ($this->countryList as $value) {
                $rules = array_merge($rules, [strtolower($value['currency_code']) => "required"]);
                $rules = array_merge($rules, ["mark_" . strtolower($value['currency_code']) => "mark_comapre"]);
            }
        }

        return array_merge(
            [
                'batch_name' => 'required|min:3|max:100|regex:/^[a-zA-Z0-9- ]+$/|subdublicate|after_enrollement',
                'batch_description' => 'min:3|max:500',
                'batch_location' => 'min:3|max:100',
                'batch_minimum_enrollment' => 'required|minimum_enrollment_less_than_manimum_enrollemnt:batch_maximum_enrollment',
                'batch_maximum_enrollment' => 'required|enrolled_valiadtion',
                'batch_start_date' => 'required|start_date_less_than_end_date',
                'batch_end_date' => 'required|batch_dates_between_course_date',
                'batch_last_enrollment_date' => 'required|start_date_less_than_enrollment_date'
            ],
            $rules
        );
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'batch_name.required' => trans('admin/batch/add.batch_name_required'),
            'batch_name.regex' => trans('admin/batch/add.batch_name_regex'),
            'batch_location.required' => trans('admin/batch/add.batch_location_required'),
            'batch_location.min' => trans('admin/batch/add.batch_location_min'),
            'batch_location.max' => trans('admin/batch/add.batch_location_max'),
            'batch_minimum_enrollment.required' => trans('admin/batch/add.batch_minimum_enrollment_required'),
            'batch_maximum_enrollment.required' => trans('admin/batch/add.batch_maximum_enrollment_required'),
            'batch_start_date.required' => trans('admin/batch/add.batch_start_date_required'),
            'batch_end_date.required' => trans('admin/batch/add.batch_end_date_required'),
            'batch_last_enrollment_date.required' => trans('admin/batch/add.batch_last_enrollment_date_required'),
            'batch_minimum_enrollment.minimum_enrollment_less_than_manimum_enrollemnt' => trans('admin/batch/add.minimum_enrollment_less_than_manimum_enrollemnt'),
            'batch_start_date.start_date_less_than_end_date' => trans('admin/batch/add.start_date_less_than_end_date'),
            'batch_last_enrollment_date.start_date_less_than_enrollment_date' => trans('admin/batch/add.start_date_less_than_enrollment_date'),
            'mark_comapre' => trans('admin/batch/add.mark_comapre'),
            'subdublicate' => trans('admin/batch/add.subdublicate'),
            'batch_description.min' => trans('admin/batch/add.batch_description_min'),
            'batch_description.max' => trans('admin/batch/add.batch_description_max'),
            'batch_dates_between_course_date' => trans('admin/batch/add.batch_dates_between_course_date'),
            'enrolled_valiadtion' => trans('admin/batch/add.enrolled_valiadtion'),
            'batch_name.after_enrollement' => trans('admin/batch/add.after_enrollement'),
        ];
    }
}
