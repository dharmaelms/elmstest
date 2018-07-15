<?php

use App\Enums\RolesAndPermissions\SystemRoles;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use App\Services\Announcement\IAnnouncementService;
use App\Services\Assignment\IAssignmentService;
use App\Services\Program\IProgramService;
use App\Services\Quiz\IQuizService;
use App\Services\Event\IEventService;
use App\Services\User\IUserService;
use App\Services\UserGroup\IUserGroupService;
use App\Services\Survey\ISurveyService;
use App\Services\Role\IRoleService;
use App\Services\DAMS\IDAMsService;
use App\Services\FlashCard\IFlashCardService;
use App\Enums\Program\ElementType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Model\User;

/**
 * Check user has admin portal access or not
 *
 * @return bool
 */
function has_admin_portal_access()
{
    return !empty(Session::get("accessible_admin_modules", []));
}

/**
 * Checks if user has access to a particular module in admin portal.
 * This method returns true if user has any one permission out of multiple permissions available for that module
 *
 * @param string $module
 * @return bool
 */
function has_admin_module_access($module)
{
    $admin_modules = Session::get("accessible_admin_modules", []);
    return in_array($module, $admin_modules);
}

/**
 * Check if user has particular admin permission in given admin module
 * NOTE: This method should be used in blade files and can be used in controllers when
 *       you are checking overall permission. If you want to check permission in specific context or specific context
 *       instance role service has_permission method should be used
 *
 * @param string $module
 * @param string $permission_slug
 * @return bool
 */
function has_admin_permission($module, $permission_slug)
{
    $module_permissions = Session::get("admin_permissions.{$module}", []);
    return in_array($permission_slug, $module_permissions);
}

/**
 * This method should be used to get permission flag when has_permission method of role service
 *      returns permission flag along with permission data
 *
 * @param array $permission_info
 * @return bool
 */
function get_permission_flag($permission_info)
{
    return array_get($permission_info, 'has_permission');
}

/**
 * This method should be used to get permission data when has_permission method of role service
 *      returns permission flag along with permission data
 *
 * @param array $permission_data_with_flag
 * @return array
 */
function get_permission_data($permission_data_with_flag)
{
    return array_get($permission_data_with_flag, 'permission');
}

/**
 * Checks if user has permission in system level
 *
 * @param array $permission_data
 * @return bool
 */
function has_system_level_access($permission_data)
{
    return array_get($permission_data, 'system_level_access');
}

/**
 * Check if context overrides are available in permission data
 * NOTE: If $context_slug is null it checks if role is overridden in any of the contexts
 *       If $context_slug is set it checks if role is overridden in the given context
 *
 * @param array $permission_data
 * @param string $context_slug
 * @return bool
 */
function has_context_overrides($permission_data, $context_slug = null)
{
    return !is_null($context_slug)? array_has($permission_data, "context_overrides.{$context_slug}") :
        array_has($permission_data, "context_overrides");

}

/**
 * @param array $permission_data
 * @return array
 */
function get_overridden_context_data($permission_data)
{
    return array_get($permission_data, 'context_overrides', []);
}

/**
 * @param array $permission_data
 * @param string $context_type
 * @return array
 */
function get_instance_ids($permission_data, $context_type)
{
    return has_context_overrides($permission_data, $context_type)?
        array_get(get_overridden_context_data($permission_data), $context_type, []) : [];
}

/**
 * @param array $permission_data
 * @param string $content_type
 * @return array
 */
function get_user_accessible_elements($permission_data, $content_type)
{
    $programService = App::make(IProgramService::class);
    $instance_ids = get_instance_ids($permission_data, Contexts::PROGRAM);
    $program_details = $programService->getAllPrograms(["in_ids" => $instance_ids]);
    $program_ids = array_column($program_details, 'program_id');
    $program_slugs = array_column($program_details, 'program_slug');
    $program_elements = array_unique(get_user_program_elements($program_slugs, $content_type));
    $program_users_elements = get_user_accessible_program_elements($program_ids, $content_type);

    return array_unique(array_merge($program_elements, $program_users_elements));
}

/**
 * Get elements inside programs that the user is enrolled to
 * @param array $program_slugs
 * @param string $content_type
 * @return array
 */
function get_user_program_elements($program_slugs, $content_type)
{
    $programService = App::make(IProgramService::class);
    $packets = $programService->getPackets($program_slugs);
    $packets_elements = array_column($packets, 'elements');
    $packets_elements = array_collapse($packets_elements);
    $packets_elements = array_where($packets_elements, function ($key, $value) use ($content_type) {
        return $value['type'] === $content_type;
    });
    return array_column($packets_elements, 'id');
}

/**
 * Get contents that are created by users who are assigned to set of programs
 * @param array $program_ids
 * @param string $content_type
 * @return array
 */
function get_user_accessible_program_elements($program_ids, $content_type)
{
    $user_created_content_ids = [];
    $userService = App::make(IUserService::class);
    $roleService = App::make(IRoleService::class);
    $context_details = $roleService->getContextDetails(Contexts::PROGRAM, true);
    $roles_detail = array_get($context_details, 'roles', []);

    $roles_detail = array_where($roles_detail, function ($key, $value){
        return $value['slug'] !== SystemRoles::LEARNER;
    });

    $role_ids = array_column($roles_detail, 'id');
    $context_role_enrollment = $roleService->getContextRoleEnrolement(
        ['instance_id' => $program_ids, 'role_id'=> $role_ids]
    );
    $user_ids = $context_role_enrollment->pluck('user_id');
    $user_details = $userService->getListOfUsersDetails($user_ids);
    if (!$user_details->isEmpty()) {
        $usernames = array_column($user_details->toArray(), 'username');

        switch ($content_type) {
            case ElementType::ASSESSMENT:
                $quizService = App::make(IQuizService::class);
                $user_created_quizzes = $quizService->getQuizzesByUsername($usernames);
                $user_created_content_ids = array_column($user_created_quizzes, 'quiz_id');
                break;
            case ElementType::EVENT:
                $eventService = App::make(IEventService::class);
                $user_created_events =  $eventService->getEventsByUsername($usernames);
                $user_created_content_ids = array_column($user_created_events, 'event_id');
                break;
            case ElementType::MEDIA:
                $damsService = App::make(IDAMsService::class);
                $medias_created_by_users = $damsService->getMediasCreatedByUsers($usernames);
                $user_created_content_ids = $medias_created_by_users->pluck("id")->toArray();
                break;
            case ElementType::FLASHCARD:
                $flashcardService = App::make(IFlashCardService::class);
                $flashcards_created_by_users = $flashcardService->getFlashCardsCreatedByUsers($usernames);
                $user_created_content_ids = $flashcards_created_by_users->pluck("card_id")->toArray();
                break;
            case ElementType::SURVEY:
                $surveyService = App::make(ISurveyService::class);
                $user_created_survey =  $surveyService->getSurveysByUsername($usernames);
                $user_created_content_ids = $user_created_survey->pluck("id")->toArray();
                break;
            case ElementType::ASSIGNMENT:
                $assignmentService = App::make(IAssignmentService::class);
                $user_created_assignment =  $assignmentService->getAssignmentsByUsername($usernames);
                $user_created_content_ids = $user_created_assignment->pluck("id")->toArray();
                break;
        }
    }

    return $user_created_content_ids;
}

/**
 * @param array $accessible_element_ids
 * @param int $element_id
 * @return bool
 */
function is_element_exist($accessible_element_ids, $element_id)
{
    return in_array($element_id, $accessible_element_ids);
}

/**
 * @param array $accessible_element_ids
 * @param array $element_ids
 * @return boolean
 */
function are_elements_exist($accessible_element_ids, $element_ids)
{
    return are_values_exist($accessible_element_ids, $element_ids);
}

/**
 * @param array $permission_data
 * @param string $element_type
 * @param array $element_ids
 * @return bool
 */
function are_elements_accessible($permission_data, $element_type, $element_ids)
{
    if (has_system_level_access($permission_data)) {
        return true;
    } else {
        return are_elements_exist(
            get_user_accessible_elements($permission_data, $element_type),
            $element_ids
        );
    }
}

/**
 * Checks whether element is accessible by the user using permission data
 * @param array $permission_data
 * @param string $element_type
 * @param int $element_id
 * @return bool
 */
function is_element_accessible($permission_data, $element_type, $element_id)
{
    if (has_system_level_access($permission_data)) {
        return true;
    } else {
        return is_element_exist(
                get_user_accessible_elements($permission_data, $element_type),
                $element_id
            );
    }
}

/**
 * @param array $permission_data
 * @param string $relation_key
 * @return array
 */
function get_program_relation($permission_data, $relation_key)
{
    $programService = App::make(IProgramService::class);
    $filter_params["in_ids"] = get_instance_ids($permission_data, Contexts::PROGRAM);
    $program_details = $programService->getAllPrograms($filter_params);
    $program_rel = array_column($program_details, 'relations');
    $program_active_rel = array_column($program_rel, $relation_key);
    $program_active_rel = array_unique(array_collapse($program_active_rel));
    return $program_active_rel;
}

/**
 * @param array $permission_data
 * @return array
 */
function get_user_ids($permission_data)
{
    return get_program_relation($permission_data, 'active_user_feed_rel');
}

/**
 * @param array $permission_data
 * @return array
 */
function get_user_group_ids($permission_data)
{
    return get_program_relation($permission_data, 'active_usergroup_feed_rel');
}

/**
 * @param array $permission_data
 * @param int $user_id
 * @return bool
 */
function is_user_exist_in_context($permission_data, $user_id)
{
    if (has_system_level_access($permission_data)) {
        return true;
    } else {
        return in_array($user_id, get_user_ids($permission_data));
    }
}

/**
 * @param array $permission_data
 * @param array $user_ids
 * @return bool
 */
function are_users_exist_in_context($permission_data, $user_ids)
{
    if (has_system_level_access($permission_data)) {
        return true;
    } else {
        return are_values_exist(get_user_ids($permission_data), $user_ids);
    }
}

/**
 * @param array $permission_data
 * @param int $user_group_id
 * @return bool
 */
function is_user_group_exist_in_context($permission_data, $user_group_id)
{
    if (has_system_level_access($permission_data)) {
        return true;
    } else {
        return in_array($user_group_id, get_user_group_ids($permission_data));
    }
}

/**
 * @param array $permission_data
 * @param array $user_group_ids
 * @return bool
 */
function are_user_groups_exist_in_context($permission_data, $user_group_ids)
{
    if (has_system_level_access($permission_data)) {
        return true;
    } else {
        return are_values_exist(get_user_group_ids($permission_data), $user_group_ids);
    }
}

/**
 * Gets the announcement ids from the channel relation
 * @param array $permission_data
 * @param array $announcement_ids
 * @return bool
 */
function get_announcement_ids($permission_data)
{
    return get_program_relation($permission_data, 'contentfeed_announcement_rel');
}

/**
 * Gets the announcement ids from the channel, user and usergroup relations
 * @param array $permission_data
 * @return array
 */
function get_user_accessible_announcements($list_permission_data)
{
    $user_created_content_ids = [];
    $userService = App::make(IUserService::class);
    $usergroupService = App::make(IUserGroupService::class);
    $program_ids = get_instance_ids($list_permission_data, Contexts::PROGRAM);
    $userService = App::make(IUserService::class);
    $roleService = App::make(IRoleService::class);
    $context_details = $roleService->getContextDetails(Contexts::PROGRAM, true);
    $roles_detail = array_get($context_details, 'roles', []);

    $roles_detail = array_where($roles_detail, function ($key, $value){
        return $value['slug'] !== SystemRoles::LEARNER;
    });

    $role_ids = array_column($roles_detail, 'id');
    $context_role_enrollment = $roleService->getContextRoleEnrolement(
        ['instance_id' => $program_ids, 'role_id'=> $role_ids]
    );

    $user_ids = $context_role_enrollment->pluck('user_id');
    $user_details = $userService->getListOfUsersDetails($user_ids);
    $usernames = array_column($user_details->toArray(), 'username');
    $program_announcement_ids = get_announcement_ids($list_permission_data);
    $announcement_service = App::make(IAnnouncementService::class);
    $announcement_created_by_users = $announcement_service->getAnnouncementsCreatedByUsers($usernames);
    $user_created_announcement_ids = $announcement_created_by_users->pluck("announcement_id")->toArray();
    $announcement_ids = array_merge($user_created_announcement_ids, $program_announcement_ids);
    return array_unique($announcement_ids);
}

/**
 * @param array $permission_data
 * @param int $announcment_id
 * @return bool
 */
function is_announcement_accessible($permission_data, $announcement_id)
{
    if (has_system_level_access($permission_data)) {
        return true;
    } else {
        return in_array($announcement_id, get_user_accessible_announcements($permission_data));
    }
}

/**
 * @param array $permission_data
 * @param array $announcement_ids
 * @return bool
 */
function are_announcements_accessible($permission_data, $announcement_ids)
{
    if (has_system_level_access($permission_data)) {
        return true;
    } else {
        return are_values_exist(get_user_accessible_announcements($permission_data), $announcement_ids);
    }
}

/**
 * To check loged user wether admin user or not 
 * @param int $role_id
 * @return bool
 */
function is_admin_role($role_id)
{
    $roleService = App::make(IRoleService::class);
    $roles_info = $roleService->getRoleDetails((int)$role_id);
    return array_get($roles_info, 'is_admin_role', false);
}

function get_user_program_posts($permission_data, $content_type)
{
    $programService = App::make(IProgramService::class);
    $instance_ids = get_instance_ids($permission_data, Contexts::PROGRAM);
    $program_details = $programService->getAllPrograms(["in_ids" => $instance_ids]);
    $program_ids = array_column($program_details, 'program_id');
    $program_slugs = array_column($program_details, 'program_slug');
    $program_elements = array_unique(get_user_program_elements($program_slugs, $content_type));
    $packets = collect($programService->getPackets($program_slugs));
    $packet_ids = $packets->lists('packet_id')->toArray();
    return $packet_ids;
}
