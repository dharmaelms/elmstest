<?php

namespace App\Model\Question\Repository;

/**
 * Interface IBaseRepository
 * @package App\Model\Question\Repository
 */
interface IBaseRepository
{
    /**
     * @param $data
     * @return mixed
     */
    public function add($data);

    /**
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);
}
