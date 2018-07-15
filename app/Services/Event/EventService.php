<?php

namespace App\Services\Event;

use Auth;
use App\Enums\Program\ElementType;
use App\Exceptions\Post\PostNotFoundException;
use App\Exceptions\Post\NoPostAssignedException;
use App\Exceptions\Program\NoProgramAssignedException;
use App\Model\Post\IPostRepository;
use App\Model\Event\IEventRepository;
use App\Services\Program\IProgramService;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use Helpers;

/**
 * Class EventService
 *
 * @package App\Services\Event
 */
class EventService implements IEventService
{
    /*
     * @var \App\Model\Event\IEventRepository
     */
    private $event_repository;

    /**
     * @var App\Model\UserGroup\Repository\IUserGroupRepository
     */
    private $usergroup_repository;

    /**
     * @var App\Services\Program\IProgramService
     */
    private $program_service;

    /**
     * @var App\Model\Post\IPostRepository
     */
    private $post_repository;

    /**
     * EventService constructor.
     * @param IEventRepository $event_repository
     */
    public function __construct(
        IEventRepository $event_repository,
        IUserGroupRepository $usergroup_repository,
        IProgramService $program_service,
        IPostRepository $post_repository
    ) {
        $this->event_repository = $event_repository;
        $this->usergroup_repository = $usergroup_repository;
        $this->program_service = $program_service;
        $this->post_repository = $post_repository;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getUpcomingEvents($page, $limit)
    {
        $events_list = $this->getAllEventsAssigned();
        $start = ($page * $limit) - $limit;
        $upcoming_events = $this->event_repository->getUpcomingEvents($events_list, $start, $limit);
        $data = [];

        foreach ($upcoming_events as $key => $event) {
            $row = new \stdClass;
            $row->id = $event->event_id;
            $row->name = $event->event_name;
            $this->getEventDescription($row, $event);
            $row->type = $event->event_type;
            $row->date = $event->start_time->timezone(Auth::user()->timezone)->format('M d');
            $row->day = $event->start_time->timezone(Auth::user()->timezone)->format('d');
            $row->month = $event->start_time->timezone(Auth::user()->timezone)->format('m');
            $row->year = $event->start_time->timezone(Auth::user()->timezone)->format('Y');
            $row->time = $event->start_time->timezone(Auth::user()->timezone)->format('g:i a');
            $row->start_time = $event->start_time->timezone(Auth::user()->timezone)->timestamp;
            $row->end_time = $event->end_time->timezone(Auth::user()->timezone)->timestamp;
            $row->event_host_id = $event->event_host_id;

            $data[$key] = $row;
        }
        return $data;
    }

    private function getEventDescription(&$row, &$event)
    {
        //creating a DOM Object
        $doc = new \DOMDocument();
        // loading the HTML
        @$doc->loadHTML(html_entity_decode($event->event_description));

        //collecting all the html tags present in $announcement->announcement_content and putting in $tags
        $tags = $doc->getElementsByTagName('*');

        // restricting $announcement->announcement_content to 150 characters
        $row->description = html_entity_decode(Helpers::truncate(html_entity_decode($event->event_description), 150));

        // constructing an array to allow only <p>, <html> and <body> tags
        $allowed_tags = ['p', 'html', 'body'];
        foreach ($tags as $value) {
            // Checking $value->tagName are in $allowed_tags array
            if (!in_array($value->tagName, $allowed_tags)) {
                // if $value->tagName is not in the $allowed_tags empty $row->description
                $row->description = '';
                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsByUsername($usernames = [])
    {
        $results = $this->event_repository->getEventsByUsername($usernames);
        return $results->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllEventsAssigned()
    {
        if (!is_admin_role(Auth::user()->role)) {
            $assigned_events = array_get(Auth::user(), 'attributes.relations.user_event_rel', []);
            $assigned_ug = array_get(Auth::user(), 'attributes.relations.active_usergroup_user_rel', []);
            if (!empty($assigned_ug)) {
                $usergroup_events = $this->usergroup_repository->get(['ugid' => $assigned_ug])->map(function ($group) {
                    return array_get($group, 'attributes.relations.usergroup_event_rel', []);
                });
                $assigned_events = array_merge($assigned_events, array_flatten(array_filter($usergroup_events->toArray())));
            }
            $hosting_events = $this->event_repository->getHostingEvents(Auth::user()->uid);
            $program_events = array_flatten($this->getProgramEvents());
            $events = array_unique(array_merge($assigned_events, $hosting_events, $program_events), SORT_REGULAR);
        } else {
            $events = $this->event_repository->getAllActiveEvents();
        }
        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramEvents()
    {
        $event_ids = [];
        try {
            try {
                $program_slugs = $this->program_service->getProgramSlugs();
            } catch (NoProgramAssignedException $e) {
                $program_slugs = [];
            }
            if (!empty($program_slugs)) {
                $posts = $this->post_repository->getAllPostsByProgramSlugs($program_slugs);
                $event_ids = $this->post_repository->getElementsFromPosts($posts, ElementType::EVENT);
            }
        } catch (PostNotFoundException $e) {
            $event_ids = [];
        } catch (NoPostAssignedException $e) {
            $event_ids = [];
        }
        return $event_ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewEventCount(array $event_ids, array $date)
    {
        return $this->event_repository->getNewEventCount($event_ids, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsDataUsingIDS($ids)
    {
        return $this->event_repository->getEventsDataUsingIDS($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewEvents(array $event_ids, array $date, $start, $limit)
    {
        return $this->event_repository->getNewEvents($event_ids, $date, $start, $limit);
    }
    
    /**
     * @param  array $event_ids
     * @inheritdoc
     */
    public function countActiveEvents($event_ids)
    {
        return $this->event_repository->countActiveEvents($event_ids);
    }
}
