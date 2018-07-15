<?php
namespace App\Model;

use Moloquent;

class ManageAttribute extends Moloquent
{
    protected $collection = 'attributes';

    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @to get attribute_id
     * @param  no
     * @returns int attribute_id
     */
    public static function getAttributeId()
    {

        $cursor = self::where('attribute_id', '>', 0)->value('attribute_id');
        if (count($cursor) == 0) {
            return 1;
        } else {
            $cursor = self::where('attribute_id', '>', 0)->orderBy('attribute_id', 'desc')->limit(1)->get(['attribute_id'])->toArray();
            $attribute_id = $cursor[0]['attribute_id'] + 1;
            return $attribute_id;
        }
    }

    /**
     * @inserts attribute record
     * @param  array $data
     * @returns 1
     */
    public static function addAttribute($data)
    {


        self::insert(
            [
                'attribute_id' => self::getAttributeId(),
                'attribute_type' => $data['attribute_type'],
                'attribute_name' => $data['attribute_name'],
                'attribute_label' => $data['attribute_label'],
                'visibility' => $data['visibility'],
                'mandatory' => $data['mandatory'],
                'default' => $data['default'],
                'ecommerce' => $data['ecommerce'],
                'unique' => $data['unique'],
                'datatype' => $data['datatype'],
                'created_at' => time(),
                'updated_at' => time(),
            ]
        );

        return 1;
    }

    /**
     * @update attribute record
     * @param array $data
     * @param int $id
     * @returns 1
     */
    public static function updateAttribute($data, $id)
    {
        self::where('attribute_id', '=', (int)$id)->update(['updated_at' => time(),
            'attribute_type' => $data['attribute_type'],
            'attribute_name' => $data['attribute_name'],
            'attribute_label' => $data['attribute_label'],
            'visibility' => $data['visibility'],
            'mandatory' => $data['mandatory'],
            'default' => $data['default'],
            'ecommerce' => $data['ecommerce'],
            'unique' => $data['unique'],
            'datatype' => $data['datatype']]);
        return 1;
    }

    /**
     * @to get attribute count
     * @param  string $status
     * @param string $search key
     * @return int count
     */
    public static function getAttributeCount($status = 'all', $search = null)
    {

        if ($status == 'all' || $status == 'ALL') {
            if ($search) {
                return self::where('attribute_name', 'like', '%' . $search . '%')->count();
            } else {
                return self::where('attribute_id', '>', 0)->count();
            }
        } else {
            if ($search) {
                return self::where('attribute_type', 'like', $status)->where('attribute_name', 'like', '%' . $search . '%')->count();
            } else {
                return self::where('attribute_type', 'like', $status)->where('attribute_id', '>', 0)->count();
            }
        }
    }

    /**
     * @to display attribute records
     * @param  int $start ,int $limit,string $status
     * @return Json Response for filtered attribute records
     */
    public static function getFilteredAttributesWithPagination($status = 'all', $start = 0, $limit = 10, $orderby = ['created_at' => 'desc'], $search = null)
    {

        $key = key($orderby);
        $value = $orderby[$key];
        if ($status == 'all' || $status == 'ALL') {
            if ($search) {
                return self::where('attribute_name', 'like', '%' . $search . '%')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('attribute_id', '>', 0)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        } else {
            if ($search) {
                return self::where('attribute_type', 'like', $status)->where('attribute_name', 'like', '%' . $search . '%')->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            } else {
                return self::where('attribute_type', 'like', $status)->where('attribute_id', '>', 0)->orderBy($key, $value)->skip((int)$start)->take((int)$limit)->get()->toArray();
            }
        }
    }

    /**
     * @to check attribute exist with same name in create
     * @param  string $attributename
     * @return attribute count by matching attribute name
     */
    public static function checkattribute($attributename, $type)
    {
        //return self::where('attribute_name', '=', $attributename)->count();
        return self::where('attribute_type', '=', $type)->where('attribute_name', '=', $attributename)->orWhere('attribute_name', 'like', $attributename)->count();
    }

    /**
     * @to check attribute exist with same name in edit
     * @param  string $attributename ,int  $id
     * @return attribute count by matching attribute name
     */
    public static function checkEditAttribute($attributename, $id, $type)
    {
        //return self::where('attribute_name', '=', $attributename)->where('attribute_id','!=',(int)$id)->count();
        return self::where('attribute_type', '=', $type)->where('attribute_name', '=', $attributename)->orWhere('attribute_name', 'like', $attributename)->where('attribute_id', '!=', (int)$id)->count();
    }

    /**
     * @to delete attribute
     * @param  int $id
     * @return 1
     */
    public static function deleteAttribute($id)
    {
        self::where('attribute_id', '=', (int)$id)->delete();

        return 1;
    }

    /**
     * @to get specific attribute record
     * @param  int $id
     * @return attribute record
     */
    public static function getAttributeUsingID($id = 'ALL')
    {
        if ($id == 'ALL') {
            return self::where('id', '>', 0)->get()->toArray();
        } else {
            return self::where('attribute_id', '=', (int)$id)->get()->toArray();
        }
    }

    public static function updateAttributeFeedRelation($category, $feeds)
    {
        self::where('attribute_id', '=', (int)$category)->unset('relations');
        if ($feeds) {
            $feeds = explode(',', $feeds);
        } else {
            $feeds = [];
        }

        foreach ($feeds as $each) {
            self::where('attribute_id', '=', (int)$category)->push('relations.assigned_product', (int)$each, true);
        }

        return;
    }

    public static function getFeedsRelation($cat_id)
    {
        $data = self::where('attribute_id', '=', (int)$cat_id)->get()->toArray();

        return $data;
    }

    public static function getDropDownFilter()
    {

        return self::select('attribute_type')->groupBy('attribute_type')->get()->toArray();
    }

    public static function getVariants($varianttype)
    {
        return self::where('attribute_type', 'like', $varianttype)->get()->toArray();
    }

    public static function getRules($varianttype)
    {
        $fields = self::getVariants($varianttype);
        $rules = [];
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if ($field['mandatory'] == 1) {
                    $rules += [$field['attribute_name'] => 'Required'];
                }
            }
            return $rules;
        } else {
            return $rules;
        }
    }
}
