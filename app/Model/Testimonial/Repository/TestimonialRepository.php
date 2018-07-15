<?php

namespace App\Model\Testimonial\Repository;

use App\Model\Testimonial\Entity\Testimonial;
use App\Model\Sequence;
use DB;
use App\Traits\GetNextSequence;

/**
 * Class TestimonialRepository
 * @package App\Model\Testimonial\Repository
 */
class TestimonialRepository implements ITestimonialRepository
{
    use GetNextSequence;
    /**
     * {@inheritdoc}
     */
    public function listtestimonials($filter)
    {
        if ($filter == 'ALL') {
            $testimonials = Testimonial::where('status', '!=', 'DELETED')
                            ->where('_id', '!=', 'id')
                            ->orderBy('sort_order', 'asc')
                            ->paginate(10);
        } else {
            $testimonials = Testimonial::where('status', '=', $filter)
                            ->where('_id', '!=', 'id')
                            ->orderBy('sort_order', 'asc')
                            ->paginate(10);
        }
        return $testimonials;
    }

    /**
     * {@inheritdoc}
     */
    public function addtestimonial($id, $input, $file_location, $file_name, $logo_dimension)
    {
        $array = [
            'id' => (int)$id,
            'name' => $input['name'],
            'logopath' => $file_location,
            'logoname' => $file_name,
            'diamension' => $logo_dimension,
            'description' => trim(strip_tags($input['testimonial_description'])),
            'short_description' => trim(strip_tags($input['testimonial_short_description'])),
            'status' => $input['status'],
            'created_at' => time(),
            'type' => $input['type'],
            'home_page_display_status' => isset($input['home_page_display_status']) ? $input['home_page_display_status'] : 'NO',
            'modified_at' => time(),
        ];
        return Testimonial::insert($array);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSortId($id, $nextval)
    {
        return Testimonial::where('id', '=', (int)$id)->update(['sort_order' => (int)$nextval]);
    }

    /**
     * {@inheritdoc}
     */
    public function sortlogos($id, $curval, $nextval)
    {
        $curval = (int)$curval;
        $nextval = (int)$nextval;

        if ($curval < $nextval) {
            $curval = $curval + 1;
            $nxtorders = Testimonial::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$curval, $nextval])->orderBy('sort_order', 'asc')->get(['sort_order', 'id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                Testimonial::where('id', '=', $nxtorder['id'])->decrement('sort_order');
            }
            return Testimonial::where('id', '=', (int)$id)->update(['sort_order' => $nextval]);
        }

        if ($curval > $nextval) {
            $curval = $curval - 1;
            $nxtorders = Testimonial::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$nextval, $curval])->orderBy('sort_order', 'asc')->get(['sort_order', 'id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                Testimonial::where('id', '=', $nxtorder['id'])->increment('sort_order');
            }
            return Testimonial::where('id', '=', (int)$id)->update(['sort_order' => $nextval]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTestimonialDetails($id)
    {
        return Testimonial::where('id', '=', (int)$id)->get()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function upadateTestimonial($id, $input, $file_location, $file_name, $logo_dimension)
    {
        $array = [
            'id' => (int)$id,
            'name' => $input['name'],
            'logopath' => $file_location,
            'logoname' => $file_name,
            'diamension' => $logo_dimension,
            'description' => trim(strip_tags($input['testimonial_description'])),
            'short_description' => trim(strip_tags($input['testimonial_short_description'])),
            'status' => $input['status'],
            'type' => $input['type'],
            'home_page_display_status' => isset($input['home_page_display_status']) ? $input['home_page_display_status'] : 'NO',
            'modified_at' => time(),
        ];
        return Testimonial::where('id', '=', (int)$id)
            ->update($array);
    }

    /**
     * {@inheritdoc}
     */
    public function deletetestimonial($id, $sort_order, $max_order)
    {
        if ($sort_order != $max_order) {
            $sort_order = $sort_order + 1;
            $nxtorders = Testimonial::where('status', '!=', 'DELETED')->whereBetween('sort_order', [$sort_order, $max_order])->orderBy('sort_order', 'asc')->get(['sort_order', 'id'])->toArray();
            foreach ($nxtorders as $nxtorder) {
                Testimonial::where('id', '=', $nxtorder['id'])->decrement('sort_order');
            }
        }
        return Testimonial::where('id', '=', (int)$id)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSortOrder()
    {
        return Testimonial::where('status', '!=', 'DELETED')->max('sort_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueId()
    {
        return Sequence::getSequence('testimonial_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getQuotesByPage($type, $fields, $limit)
    {
        if ($limit != '' && $type != '') {
            if ($type == 'home_page') {
                $data = Testimonial::where('status', 'ACTIVE')
                    ->where('type', $type)
                    ->where('home_page_display_status', 'YES')
                    ->orderBy('sort_order', 'asc')
                    ->take((int)$limit)
                    ->get($fields);
            } else {
                $data = Testimonial::where('status', 'ACTIVE')
                    ->where('type', $type)
                    ->orderBy('sort_order', 'asc')
                    ->take((int)$limit)
                    ->get($fields);
            }
        } elseif ($limit != '' && $type == '') {
            $data = Testimonial::where('status', 'ACTIVE')
                ->where('home_page_display_status', 'YES')
                ->orderBy('sort_order', 'asc')
                ->take((int)$limit)
                ->get($fields);
        } elseif ($limit == '' && $type != '') {
            if ($type == 'home_page') {
                $data = Testimonial::where('status', 'ACTIVE')
                    ->where('home_page_display_status', 'YES')
                    ->where('type', $type)
                    ->orderBy('sort_order', 'asc')
                    ->get($fields);
            } else {
                $data = Testimonial::where('status', 'ACTIVE')
                    ->where('type', $type)
                    ->orderBy('sort_order', 'asc')
                    ->get($fields);
            }
        } else {
            $paginate = config('app.testimonial_pagination');
            $data = Testimonial::where('status', 'ACTIVE')
                ->orderBy('sort_order', 'asc')
                ->paginate($paginate);
        }

        return $data;
    }
}
