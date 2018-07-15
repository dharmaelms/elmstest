<?php

namespace App\Listeners\User;

use App\Enums\User\EnrollmentSource;
use App\Services\User\IUserService;

class EnrollEntity
{
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * Create the event listener.
     * @param IUserService $userService
     */
    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param \App\Events\User\EntityEnrollmentByAdminUser $event
     */
    public function handleEntityEnrollmentByAdminUser($event)
    {
        $enrollment_source_data["source_type"] = EnrollmentSource::DIRECT_ENROLLMENT;
        $this->handleEnrollEntityEvent(
            $event->user_id,
            $event->entity_type,
            $event->entity_id,
            $event->valid_from,
            $event->expire_on,
            $enrollment_source_data
        );
    }

    /**
     * @param \App\Events\User\EntityEnrollmentThroughUserGroup $event
     */
    public function handleEntityEnrollmentThroughUserGroup($event)
    {
        $enrollment_source_data["source_type"] = EnrollmentSource::USER_GROUP;
        $enrollment_source_data["source_id"] = $event->user_group_id;
        $this->handleEnrollEntityEvent(
            $event->user_id,
            $event->entity_type,
            $event->entity_id,
            $event->valid_from,
            $event->expire_on,
            $enrollment_source_data
        );
    }

    public function handleEntityEnrollmentThroughSubscription($event)
    {
        $enrollment_source_data["source_type"] = EnrollmentSource::SUBSCRIPTION;
        $enrollment_source_data["subscription_slug"] = $event->subscription_slug;
        $this->handleEnrollEntityEvent(
            $event->user_id,
            $event->entity_type,
            $event->entity_id,
            $event->valid_from,
            $event->expire_on,
            $enrollment_source_data
        );
    }

    /**
     * @param int $user_id
     * @param string $entity_type
     * @param int $entity_id
     * @param int $valid_from
     * @param int $expire_on
     * @param array $enrollment_source_data
     */
    private function handleEnrollEntityEvent(
        $user_id,
        $entity_type,
        $entity_id,
        $valid_from,
        $expire_on,
        $enrollment_source_data
    ) {
        $entity_data["entity_type"] = $entity_type;
        $entity_data["entity_id"] = $entity_id;
        $entity_data["valid_from"] = $valid_from;
        $entity_data["expire_on"] = $expire_on;
        $this->userService->enrollEntityToUser($user_id, $entity_data, $enrollment_source_data);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\User\EntityEnrollmentByAdminUser',
            'App\Listeners\User\EnrollEntity@handleEntityEnrollmentByAdminUser'
        );

        $events->listen(
            'App\Events\User\EntityEnrollmentThroughUserGroup',
            'App\Listeners\User\EnrollEntity@handleEntityEnrollmentThroughUserGroup'
        );

        $events->listen(
            'App\Events\User\EntityEnrollmentThroughSubscription',
            'App\Listeners\User\EnrollEntity@handleEntityEnrollmentThroughSubscription'
        );
    }
}
