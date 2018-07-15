<?php

namespace App\Listeners\User;

use App\Enums\User\EnrollmentSource;
use App\Services\User\IUserService;

class UnenrollEntity
{
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * UnenrollEntity constructor.
     * @param IUserService $userService
     */
    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param \App\Events\User\EntityUnenrollmentByAdminUser $event
     */
    public function handleEntityUnenrollmentByAdminUser($event)
    {
        $this->handleEntityUnenrollment(
            $event->user_id,
            $event->entity_type,
            $event->entity_id,
            EnrollmentSource::DIRECT_ENROLLMENT
        );
    }

    /**
     * @param \App\Events\User\EntityUnenrollmentThroughUserGroup $event
     */
    public function handleEntityUnenrollmentThroughUserGroup($event)
    {
        $this->handleEntityUnenrollment(
            $event->user_id,
            $event->entity_type,
            $event->entity_id,
            EnrollmentSource::USER_GROUP,
            $event->user_group_id
        );
    }

    private function handleEntityUnenrollment($user_id, $entity_type, $entity_id, $source_type, $source_id = null)
    {
        $this->userService->unenrollUserFromEntity($user_id, $entity_type, $entity_id, $source_type, $source_id);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\User\EntityUnenrollmentByAdminUser',
            'App\Listeners\User\UnenrollEntity@handleEntityUnenrollmentByAdminUser'
        );

        $events->listen(
            'App\Events\User\EntityUnenrollmentThroughUserGroup',
            'App\Listeners\User\UnenrollEntity@handleEntityUnenrollmentThroughUserGroup'
        );
    }
}
