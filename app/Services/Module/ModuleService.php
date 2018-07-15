<?php

namespace App\Services\Module;

use App\Model\Module\Repository\IModuleRepository;

class ModuleService implements IModuleService
{
    /**
     * @var IModuleRepository
     */
    private $moduleRepository;

    /**
     * ModuleService constructor.
     * @param IModuleRepository $moduleRepository
     */
    public function __construct(IModuleRepository $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * @inheritDoc
     */
    public function getModulePermissionDetails($module, $permission, $permission_type = null)
    {
        $module = is_numeric($module)? $this->moduleRepository->find($module) :
                                        $this->moduleRepository->findByAttribute("slug", $module);

        $module_permission = $this->moduleRepository->findPermissionInModule($module, $permission, $permission_type);

        return [
            "module" => ["id" => $module->id, "name" => $module->name, "slug" => $module->slug],
            "permission" => [
                "id" => $module_permission->id,
                "name" => $module_permission->name,
                "slug" => $module_permission->slug,
                "type" => $module_permission->type,
            ]
        ];
    }
}