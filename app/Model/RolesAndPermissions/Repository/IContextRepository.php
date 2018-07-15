<?php

namespace App\Model\RolesAndPermissions\Repository;

interface IContextRepository
{
    /**
     * Find context using unique id
     *
     * @var int $id
     *
     * @return \App\Model\RolesAndPermissions\Entity\Context
     *
     * @throws \App\Exceptions\RolesAndPermissions\ContextNotFoundException
     */
    public function find($id);

    /**
     * @param string $attribute
     * @param int|string|array|boolean $value
     * @param bool $include_roles
     * @return \App\Model\RolesAndPermissions\Entity\Context
     */
    public function findByAttribute($attribute, $value, $include_roles = false);

    /**
     * Get available contexts
     *
     * @param bool $include_roles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($include_roles = false);
}
