<?php namespace App\Services\Testimonial;

use App\Model\Testimonial;
use App\Model\Testimonial\Repository\ITestimonialRepository;
use Config;
use File;
use Imagick;

/**
 * Class TestimonialService
 * @package App\Services\Testimonial
 */
class TestimonialService implements ITestimonialService
{
    /**
     * @var ITestimonialRepository
     */
    private $testimonial_repository;

    /**
     * TestimonialService constructor.
     * @param ITestimonialRepository $testimonial_repository
     */
    public function __construct(ITestimonialRepository $testimonial_repository)
    {
        $this->testimonial_repository = $testimonial_repository;
    }

    /**
     * @param string $filter
     * @return mixed
     */
    public function listTestimonials($filter = 'ALL')
    {
        $list_testimonials = $this->testimonial_repository->listtestimonials($filter);
        return $list_testimonials;
    }

    /**
     * @param $input
     * @param string $type
     * @param string $id
     * @return mixed
     */
    public function createTestimonials($input, $type = 'add', $id = '')
    {

        $file_location = ' ';
        $file_original_name = ' ';

        $profile_pic_path = config('app.testimonials_path');
        $user_profile_pic_resolution = Config::get('app.user_profile_pic_resolution');
        if (isset($input['file']) && !empty($input['file'])) {
            $file = $input['file'];
            $file_client_name = $file->getClientOriginalName();
            $file_name = pathinfo($file_client_name, PATHINFO_FILENAME);
            $file_extension = pathinfo($file_client_name, PATHINFO_EXTENSION);
            if (empty($id)) {
                $id = $this->testimonial_repository->getUniqueId();
            }

            $file_original_name = $file_name . $id . '.' . $file_extension;
            $file_location = $profile_pic_path . $file_original_name;
            $file->move($profile_pic_path, $file_location);
            $image_obj = new Imagick($file_location);
            if (isset($input['w']) && !empty($input['w']) && isset($input['h']) && !empty($input['h']) && isset($input['x']) && !empty($input['x']) && isset($input['y']) && !empty($input['y'])) {
                $w = $input['w'];
                $h = $input['h'];
                $x = $input['x'];
                $y = $input['y'];
                $image_obj->cropImage($w, $h, $x, $y);
            }

            $required_sizes = explode('x', $user_profile_pic_resolution);
            $logo_size = $profile_pic_path . $file_name . $id . '_' . $user_profile_pic_resolution . '.' . $file_extension;
            if ($required_sizes[0] < $image_obj->getImageWidth() && $required_sizes[1] < $image_obj->getImageHeight()) {
                $image_obj->resizeImage($required_sizes[0], $required_sizes[1], Imagick::FILTER_LANCZOS, 1, true);
            }
            $image_obj->writeImage($logo_size);
            $logo_dimension = $file_name . $id . '_' . $user_profile_pic_resolution . '.' . $file_extension;
        } elseif ($input['old_file']) {
            $file_original_name = $input['old_file'];
            $file_location = $profile_pic_path . $file_original_name;
            $logo_dimension = $input['old_diamension'];
        } else {
            $file_original_name = '';
        }
        if ($type == 'edit') {
            $partner = $this->testimonial_repository->upadateTestimonial($id, $input, $file_location, $file_original_name, $logo_dimension);
        } else {
            $partner = $this->testimonial_repository->addtestimonial($id, $input, $file_location, $file_original_name, $logo_dimension);
        }

        $curval = $input['curval'];
        $nextval = $input['sort_order'];
        if ($curval == $nextval) {
            $data = $this->testimonial_repository->updateSortId($id, $nextval);
        } else {
            $this->sortLogos($id, $curval, $nextval);
        }
        return $partner;
    }

    /**
     * @param $id
     * @param $curval
     * @param $nextval
     * @return mixed
     */
    public function sortLogos($id, $curval, $nextval)
    {
        return $this->testimonial_repository->sortlogos($id, $curval, $nextval);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getTestimonialDetails($id)
    {
        return $this->testimonial_repository->getTestimonialDetails($id);
    }

    /**
     * @param $id
     * @param $logo_name
     * @param $sort_order
     * @return mixed
     */
    public function deleteTestimonials($id, $logo_name, $sort_order)
    {
        $path = config('app.profile_pic_path');
        $delete_file = $path . $logo_name;

        if (File::exists($path)) {
            $a = File::delete($delete_file);
        }
        $max_order = $this->getMaxSortOrder();
        $res = $this->testimonial_repository->deletetestimonial($id, $sort_order, $max_order);
        return $res;
    }

    /**
     * @return mixed
     */
    public function getMaxSortOrder()
    {
        return $this->testimonial_repository->getMaxSortOrder();
    }

    /**
     * @param string $type
     * @param array $selected_fields
     * @param string $limit
     * @return array|mixed
     */
    public function getQuotesByPage($type = 'home_page', $selected_fields = [], $limit = '')
    {
        $fields = ['name', 'description'];
        $quotes = [];

        if (is_array($selected_fields) && count($selected_fields) > 0) {
            $fields = array_merge($fields, $selected_fields);
        }
        $quotes = $this->testimonial_repository->getQuotesByPage($type, $fields, $limit);

        return $quotes;
    }
}
