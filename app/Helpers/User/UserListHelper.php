<?php

namespace App\Helpers\User;

use App\Enums\RolesAndPermissions\SystemRoles;

class UserListHelper
{
    public function generateUserListCheckbox($user_id)
    {
        return "<input type='checkbox' value='{$user_id}'>";
    }

    public function generateRolesDropdown($roles, $user_id, $assigned_role_id)
    {
        $dropdown_values_html = "";

        foreach ($roles as $role) {
            $selected = "";
            if (isset($assigned_role_id)) {
                if ($role["id"] === $assigned_role_id) {
                    $selected = "selected";
                }
            } else {
                $learner_role = array_get($roles, SystemRoles::LEARNER);
                if (array_get($learner_role, "id") === $role["id"]) {
                    $selected = "selected";
                }
            }

            $dropdown_values_html .= "<option value=\"{$role["id"]}\" {$selected}>
                                        {$role["name"]}
                                      </option>";
        }

        $dropdown_html = "<select name=\"role\" id=\"user_{$user_id}\">
                            {$dropdown_values_html}
                          </select>";

        return $dropdown_html;
    }
}
