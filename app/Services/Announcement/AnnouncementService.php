<?php

namespace App\Services\Announcement;

use App\Exceptions\Announcement\AnnouncementNotFoundException;
use App\Model\Announcement\IAnnouncementRepository;
use Helpers;
use Auth;

/**
 * Class AnnouncementService
 *
 * @package App\Services\Announcement
 */
class AnnouncementService implements IAnnouncementService
{
    /**
     * @var \App\Model\Announcement\IAnnouncementRepository
     */
    private $announcement_repository;

    /**
     * AnnouncementService constructor.
     * @param IAnnouncementRepository $announcement_repository
     */
    public function __construct(IAnnouncementRepository $announcement_repository)
    {
        $this->announcement_repository = $announcement_repository;
    }

    /**
     * {@inheritdoc}
     * @throws AnnouncementNotFoundException
     */
    public function getAnnouncements($start, $limit)
    {
        $data = [];

        if (is_admin_role(Auth::user()->role)) {
            $columns = ['announcement_id', 'announcement_title', 'announcement_content'];
            $result = $this->announcement_repository->getAdminUserAnnouncements($start, $limit, $columns);
        } else {
            $result = $this->announcement_repository->getUserAnnouncements($start, $limit);
        }

        if ($result->count() == 0) {
            throw new AnnouncementNotFoundException();
        } else {
            foreach ($result as $announcement) {
                $row = new \stdClass;
                $row->id = $announcement->announcement_id;
                $row->title = $announcement->announcement_title;

                $this->getAnnouncementDescription($row, $announcement);

                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     * @param $row
     * @param $announcement
     */
    private function getAnnouncementDescription(&$row, &$announcement)
    {
        //creating a DOM Object
        $doc = new \DOMDocument();

        // loading the HTML
        @$doc->loadHTML(html_entity_decode($announcement->announcement_content));

        //collecting all the html tags present in $announcement->announcement_content and putting in $tags
        $tags = $doc->getElementsByTagName('*');

        // restricting $announcement->announcement_content to 150 characters
        $row->description = html_entity_decode(Helpers::truncate(html_entity_decode($announcement->announcement_content), 150));

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
    public function getAllPublicAnnouncements($start_id, $limit, $device)
    {
        return $this->announcement_repository->getAllPublicAnnouncements($start_id, $limit, $device);
    }
    /**
     * {@inheritdoc}
     */
    public function getAllPrivateAnnouncements()
    {
        return $this->announcement_repository->getAllPrivateAnnouncements();
    }
    /**
     * {@inheritdoc}
     */
    public function getUnReadPublicAnnouncementsCount($user_id, $announcement_ids, $device = 'web')
    {
        return $this->announcement_repository->getUnReadPublicAnnouncementsCount($user_id, $announcement_ids, $device);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAnnouncements($filter_params = [])
    {
        return $this->announcement_repository->getAllAnnouncements($filter_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnnouncementsCreatedByUsers($usernames = [])
    {
        $results = $this->announcement_repository->getAnnouncementsCreatedByUsers($usernames);
        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAnnouncementCount(array $announcement_ids, array $date)
    {
        return $this->announcement_repository->getNewAnnouncementCount($announcement_ids, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAnnouncements(array $announcement_ids, array $date, $start, $limit)
    {
        return $this->announcement_repository->getNewAnnouncements($announcement_ids, $date, $start, $limit);
    }
}
