<?php
namespace App\Services\CustomFields;

interface ICustomService
{
    public function getCustomFields($filter, $searchKey, $start, $limit);

    public function getCustomFieldsCount($filter = "userfields", $searchKey = null);

    public function getValidateCustomField($filter = "userfields", $fieldname = null);

    public function insertCustomField($input, $filter, $program_type, $program_sub_type);

    public function editCustomField($id, $filter);

    public function getCustomFieldById($id);

    public function updateCustomField($input, $id, $filter, $field);

    public function deleteCustomField($id, $filter);

    public function getFormCustomFields($filter);

    public function insertModuleCustomField($input, $slug);

    public function insertPackageModuleCustomField($input, $slug);

    public function insertNewProgramCustomFields($program_id, $type);

    public function getFieldNames($filter);

    public function getFieldNamesExcept($filter, $id);

    public function getValidateCustomFieldValue($filter = "userfields", $fieldname = null);

    /**
     * @param array $field_labels
     * @return array
     */
    public function getCustomFieldDetails($field_labels = []);
}
