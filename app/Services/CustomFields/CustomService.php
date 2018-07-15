<?php

namespace App\Services\CustomFields;

use App\Model\CustomFields\Repository\ICustomRepository;

/**
 * Class CustomService
 * @package App\Services\CustomFields
 */
class CustomService implements ICustomService
{
    /**
     * @var ICustomRepository
     */
    private $custom_repository;

    /**
     * CustomService constructor.
     * @param ICustomRepository $custom_repository
     */
    public function __construct(ICustomRepository $custom_repository)
    {
        $this->custom_repository = $custom_repository;
    }

    /**
     * @param $filter
     * @param $searchKey
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getCustomFields($filter, $searchKey, $start, $limit)
    {
        return $this->custom_repository->getCustomFields($filter, $searchKey, $start, $limit);
    }

    /**
     * @param string $filter
     * @param null $searchKey
     * @return mixed
     */
    public function getCustomFieldsCount($filter = "userfields", $searchKey = null)
    {
        return $this->custom_repository->getCustomFieldsCount($filter, $searchKey);
    }

    /**
     * @param string $filter
     * @param null $fieldname
     * @return mixed
     */
    public function getValidateCustomField($filter = "userfields", $fieldname = null)
    {
        return $this->custom_repository->getValidateCustomField($filter, $fieldname);
    }

    /**
     * @param $input
     * @param $filter
     * @param $program_type
     * @param $program_sub_type
     * @return mixed
     */
    public function insertCustomField($input, $filter, $program_type, $program_sub_type)
    {
        return $this->custom_repository->insertCustomField($input, $filter, $program_type, $program_sub_type);
    }

    /**
     * @param $id
     * @param $filter
     * @return mixed
     */
    public function editCustomField($id, $filter)
    {
        return $this->custom_repository->editCustomField($id, $filter);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCustomFieldById($id)
    {
        return $this->custom_repository->getCustomFieldById($id);
    }

    /**
     * @param $input
     * @param $id
     * @param $filter
     * @param $field
     * @return mixed
     */
    public function updateCustomField($input, $id, $filter, $field)
    {
        return $this->custom_repository->updateCustomField($input, $id, $filter, $field);
    }

    /**
     * @param $id
     * @param $filter
     * @return mixed
     */
    public function deleteCustomField($id, $filter)
    {
        return $this->custom_repository->deleteCustomField($id, $filter);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getFormCustomFields($filter)
    {
        return $this->custom_repository->getFormCustomFields($filter);
    }

    /**
     * @param $input
     * @param $slug
     * @return mixed
     */
    public function insertModuleCustomField($input, $slug)
    {
        return $this->custom_repository->insertModuleCustomField($input, $slug);
    }

    /**
     * @param $input
     * @param $slug
     * @return mixed
     */
    public function insertPackageModuleCustomField($input, $slug)
    {
        return $this->custom_repository->insertpackageModuleCustomField($input, $slug);
    }

    /**
     * @param $program_id
     * @param $type
     * @return mixed
     */
    public function insertNewProgramCustomFields($program_id, $type)
    {
        return $this->custom_repository->insertNewProgramCustomFields($program_id, $type);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getFieldNames($filter)
    {
        return $this->custom_repository->getFieldNames($filter);
    }

    /**
     * @param $filter
     * @param $id
     * @return mixed
     */
    public function getFieldNamesExcept($filter, $id)
    {
        return $this->custom_repository->getFieldNamesExcept($filter, $id);
    }

    /**
     * @param string $filter
     * @param null $fieldname
     * @return mixed
     */
    public function getValidateCustomFieldValue($filter = "userfields", $fieldname = null)
    {
        return $this->custom_repository->getValidateCustomFieldValue($filter, $fieldname);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomFieldDetails($field_labels = []) 
    {
        return $this->custom_repository->getCustomFieldDetails($field_labels);
    }
}
