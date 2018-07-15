<?php

namespace App\Model\Module\Repository;

use App\Exceptions\Module\ModuleNotFoundException;
use App\Exceptions\RolesAndPermissions\PermissionNotFoundException;
use App\Model\Module\Entity\Module;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ModuleRepository implements IModuleRepository
{
    /**
     * @inheritDoc
     */
    public function find($id)
    {
        try {
            return Module::findOrFail((int) $id);
        } catch (ModelNotFoundException $e) {
            throw new ModuleNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findByAttribute($attribute, $value)
    {
        try {
            return Module::where($attribute, $value)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ModuleNotFoundException();
        }
    }

    /**
     * @inheritDoc
     */
    public function findPermissionInModule($module, $permission, $permission_type = null)
    {
        try {
            //When module is numeric value it is considered as module id else it is considered as module slug

            $module_permissions = ($module instanceof Module) ? $module->permissions() :
                                    (is_numeric($module) ? $this->find($module) :
                                        $this->findByAttribute("slug", $module)->permissions());

            //When permission is numeric value it is considered as permission id
            // else it is considered as permission slug

            return is_numeric($permission)? $module_permissions->findOrFail($permission) :
                                            $module_permissions->type($permission_type)
                                                                ->where("slug", $permission)
                                                                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new PermissionNotFoundException();
        }
    }
}
