<?php
namespace App\Model\CustomFields\Repository;

/**
 * Interface ICustomRepository
 * @package App\Model\CustomFields\Repository
 */
interface ICustomRepository
{
    /**
     * @param $filter
     * @param $searchKey
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getCustomFields($filter, $searchKey, $start, $limit);

    /**
     * @param string $filter
     * @param null $searchKey
     * @return mixed
     */
    public function getCustomFieldsCount($filter = "userfields", $searchKey = null);

    /**
     * @param string $filter
     * @param null $fieldname
     * @return mixed
     */
    public function getValidateCustomField($filter = "userfields", $fieldname = null);

    /**
     * @param $input
     * @param $filter
     * @param $program_type
     * @param $program_sub_type
     * @return mixed
     */
    public function insertCustomField($input, $filter, $program_type, $program_sub_type);

    /**
     * @param $id
     * @param $filter
     * @return mixed
     */
    public function editCustomField($id, $filter);

    /**
     * @param $id
     * @return mixed
     */
    public function getCustomFieldById($id);

    /**
     * @param $input
     * @param $id
     * @param $filter
     * @param $field
     * @return mixed
     */
    public function updateCustomField($input, $id, $filter, $field);

    /**
     * @param $id
     * @param $filter
     * @return mixed
     */
    public function deleteCustomField($id, $filter);

    /**
     * @param $filter
     * @return mixed
     */
    public function getFormCustomFields($filter);

    /**
     * @param $input
     * @param $slug
     * @return mixed
     */
    public function insertModuleCustomField($input, $slug);

    /**
     * @param $input
     * @param $slug
     * @return mixed
     */
    public function insertpackageModuleCustomField($input, $slug);

    /**
     * @param $program_id
     * @param $type
     * @return mixed
     */
    public function insertNewProgramCustomFields($program_id, $type);

    /**
     * @param $filter
     * @return mixed
     */
    public function getFieldNames($filter);

    /**
     * @param $filter
     * @param $id
     * @return mixed
     */
    public function getFieldNamesExcept($filter, $id);

    /**
     * @param string $filter
     * @param null $fieldname
     * @return mixed
     */
    public function getValidateCustomFieldValue($filter = "userfields", $fieldname = null);

    /**
     * @param array $field_labels
     * @return array
     */
    public function getCustomFieldDetails($field_labels = []);
}
