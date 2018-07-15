<?php namespace App\Services\Testimonial;

interface ITestimonialService
{

    /**
     * [listtestimonials description]
     * @method listtestimonials
     * @param  string $filter [ACTIVE/INACTIVE/ALL]
     * @return [array]                   [returns list of testimonials based on filter]
     */
    public function listTestimonials($filter = 'ALL');

    /**
     * [createTestimonials description]
     * @method createTestimonials
     * @param  [array]             $input [Input fields]
     * @param  string $type [add/edit]
     * @param  string $id [Testimonial id if $type is edit ]
     * @return [string]                    [Success or failure response]
     */
    public function createTestimonials($input, $type = 'add', $id = '');

    public function sortLogos($id, $curval, $nextval);

    public function getTestimonialDetails($id);

    public function deleteTestimonials($id, $logo_name, $sort_order);

    public function getMaxSortOrder();

    public function getQuotesByPage($type = 'home_page', $selected_fields = [], $limit = '');
}
