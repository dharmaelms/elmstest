<?php

namespace App\Model\CustomFields\Repository;

use App\Model\CustomFields\Entity\CustomFields;
use App\Model\Program;
use App\Model\User;
use App\Model\Package\Entity\Package;
use Auth;

/**
 * Class CustomRepository
 * @package App\Model\CustomFields\Repository
 */
class CustomRepository implements ICustomRepository
{
    /**
     * {@inheritdoc}
     */
    public function getCustomFields($filter, $searchKey, $start, $limit)
    {
        return CustomFields::GetType($filter)
            ->FieldSearch($searchKey)
            ->orderby('created_at', 'desc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomFieldsCount($filter = "userfields", $searchKey = null)
    {
        return CustomFields::GetType($filter)
            ->FieldSearch($searchKey)
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getValidateCustomField($filter = "userfields", $fieldname = null)
    {
        if ($filter == 'userfields') {
            $res = User::where($fieldname, 'exists', true)->get()->toArray();
            return count($res);
        } else {
            return Program::where($fieldname, 'exists', true)
                ->FilterCustomType($filter)->count();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function insertCustomField($input, $filter, $program_type, $program_sub_type)
    {
        $id = CustomFields::uniqueId();
        $now = time();
        $added_by = Auth::user()->username;
        $mark_as_mandatory = isset($input['mark_as_mandatory']) ? "yes" : "no";

        if ($filter == 'userfields') {
            $edited_by_user = isset($input['edited_by_user']) ? "yes" : "no";
            $array = [
                'id' => $id,
                'fieldname' => $input['fieldname'],
                'fieldlabel' => $input['fieldlabel'],
                'program_type' => $program_type,
                'program_sub_type' => $program_sub_type,
                'mark_as_mandatory' => $mark_as_mandatory,
                'edited_by_user' => $edited_by_user,
                'status' => $input['status'],
                'created_at' => $now,
                'created_by' => $added_by,
                'updated_at' => $now,
                'updated_by' => $added_by
            ];

            User::where('uid', 'exists', true)->update([$input['fieldname'] => '']);
        } else {
            $array = [
                'id' => $id,
                'fieldname' => $input['fieldname'],
                'fieldlabel' => $input['fieldlabel'],
                'program_type' => $program_type,
                'program_sub_type' => $program_sub_type,
                'mark_as_mandatory' => $mark_as_mandatory,
                'status' => $input['status'],
                'created_at' => $now,
                'created_by' => $added_by,
                'updated_at' => $now,
                'updated_by' => $added_by
            ];

            Program::FilterCustomType($filter)->update([$input['fieldname'] => '']);
        }

        return CustomFields::insert($array);
    }

    /**
     * {@inheritdoc}
     */
    public function editCustomField($id, $filter)
    {
        return CustomFields::where('id', '=', (int)$id)
            ->GetType($filter)
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomFieldById($id)
    {
        return CustomFields::where('id', '=', (int)$id)
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomField($input, $id, $filter, $field)
    {
        $mark_as_mandatory = isset($input['mark_as_mandatory']) ? "yes" : "no";

        if ($filter == 'userfields') {
            $edited_by_user = isset($input['edited_by_user']) ? "yes" : "no";
            $array = [
                'fieldname' => $input['fieldname'],
                'fieldlabel' => $input['fieldlabel'],
                'mark_as_mandatory' => $mark_as_mandatory,
                'edited_by_user' => $edited_by_user,
                'status' => $input['status'],
                'updated_at' => time(),
                'updated_by' => Auth::user()->username
            ];

            User::where($field, 'exists', true)->unset($field);
            User::where('uid', 'exists', true)->update([$input['fieldname'] => '']);
        } else {
            $array = [
                'fieldname' => $input['fieldname'],
                'fieldlabel' => $input['fieldlabel'],
                'mark_as_mandatory' => $mark_as_mandatory,
                'status' => $input['status'],
                'updated_at' => time(),
                'updated_by' => Auth::user()->username
            ];

            Program::FilterCustomType($filter)->where($field, 'exists', true)->unset($field);
            Program::FilterCustomType($filter)->update([$input['fieldname'] => '']);
        }

        return CustomFields::where('id', '=', (int)$id)
            ->update($array);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCustomField($id, $filter)
    {
        $field = CustomFields::where('id', '=', (int)$id)->value('fieldname');

        if ($filter == 'userfields') {
            User::where($field, 'exists', true)->unset($field);
        } else {
            Program::FilterCustomType($filter)->where($field, 'exists', true)->unset($field);
        }

        return CustomFields::where('id', '=', (int)$id)->GetType($filter)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getFormCustomFields($filter)
    {
        return CustomFields::GetType($filter)
            ->where('status', '=', 'ACTIVE')
            ->orderby('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function insertModuleCustomField($input, $slug)
    {
        foreach ($input as $key => $value) {
            Program::where('program_slug', $slug)->update([$key => $value]);
        }

        return Program::where('program_slug', $slug)->update(['updated_at' => time()]);
    }

    /**
     * {@inheritdoc}
     */
    public function insertpackageModuleCustomField($input, $slug)
    {
        foreach ($input as $key => $value) {
            Package::where('package_slug', $slug)->update([$key => $value]);
        }

        return Package::where('package_slug', $slug)->update(['updated_at' => time()]);
    }

    /**
     * {@inheritdoc}
     */
    public function insertNewProgramCustomFields($program_id, $type)
    {
        $fields = CustomFields::GetType($type)
            ->where('status', '!=', 'DELETED')
            ->get()
            ->toArray();
        foreach ($fields as $field) {
            Program::where('program_id', (int)$program_id)->update([$field['fieldname'] => '']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames($filter)
    {
        return CustomFields::GetType($filter)->lists('fieldname')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNamesExcept($filter, $id)
    {
        return CustomFields::GetType($filter)->where('id', '!=', (int)$id)->lists('fieldname')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getValidateCustomFieldValue($filter = "userfields", $fieldname = null)
    {
        if ($filter == 'userfields') {
            $result = User::where($fieldname, 'exists', true)->lists($fieldname)->all();
        } else {
            $result = Program::where($fieldname, 'exists', true)
                ->FilterCustomType($filter)->lists($fieldname)->all();
        }

        foreach ($result as $value) {
            if ($value != '') {
                return true;
            }
        }

        return false;

    }

    /**
     * {@inheritdoc}
     */
    public function getCustomFieldDetails($field_labels = []) 
    {
        return CustomFields::where('program_type', '=', 'user')
            ->where('status', '=', 'ACTIVE')
            ->whereIn('fieldlabel', $field_labels)
            ->get();
    }
}
