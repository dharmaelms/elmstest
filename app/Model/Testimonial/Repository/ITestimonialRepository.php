<?php namespace App\Model\Testimonial\Repository;

/**
 * Interface ITestimonialRepository
 * @package App\Model\Testimonial\Repository
 */
interface ITestimonialRepository
{
    /**
     * @param $filter
     * @return mixed
     */
    public function listtestimonials($filter);

    /**
     * @param $id
     * @param $input
     * @param $file_location
     * @param $file_name
     * @param $logo_dimension
     * @return mixed
     */
    public function addtestimonial($id, $input, $file_location, $file_name, $logo_dimension);

    /**
     * @param $id
     * @param $nextval
     * @return mixed
     */
    public function updateSortId($id, $nextval);

    /**
     * @param $id
     * @param $curval
     * @param $nextval
     * @return mixed
     */
    public function sortlogos($id, $curval, $nextval);

    /**
     * @param $id
     * @return mixed
     */
    public function getTestimonialDetails($id);

    /**
     * @param $id
     * @param $input
     * @param $file_location
     * @param $file_name
     * @param $logo_dimension
     * @return mixed
     */
    public function upadateTestimonial($id, $input, $file_location, $file_name, $logo_dimension);

    /**
     * @param $id
     * @param $sort_order
     * @param $max_order
     * @return mixed
     */
    public function deletetestimonial($id, $sort_order, $max_order);

    /**
     * @return mixed
     */
    public function getMaxSortOrder();

    /**
     * @return mixed
     */
    public function getUniqueId();

    /**
     * @param $type
     * @param $fields
     * @param $limit
     * @return mixed
     */
    public function getQuotesByPage($type, $fields, $limit);


}
