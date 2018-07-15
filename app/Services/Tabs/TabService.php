<?php

namespace App\Services\Tabs;

use App\Model\Dams\Repository\IDamsRepository;
use App\Model\Tabs\Repository\ITabRepository;

/**
 * Class TabService
 * @package App\Services\Tabs
 */
class TabService implements ITabService
{
    /**
     * @var ITabRepository
     */
    private $tab_repository;

    private $dams_repository;
    /**
     * TabService constructor.
     * @param ITabRepository $tab_repository
     */
    public function __construct(ITabRepository $tab_repository, IDamsRepository $dams_repository)
    {
        $this->tab_repository = $tab_repository;
        $this->dams_repository = $dams_repository;
    }

    /**
     * @param $data
     */
    public function saveTab($data)
    {
        $pid = $data['p_id'];
        $r_data = $this->tab_repository->getTabs($pid);
        if (!empty($r_data)) {
            $tabs = null;
            foreach ($r_data as $eachVal) {
                if (isset($eachVal['tabs']) && !empty($eachVal['tabs'])) {
                    $newtab = [[
                        'title' => $data['title'],
                        'slug' => $this->mSlug($data['title']),
                        'description' => $data['description'],
                        'media_ids' => array_get($data, 'p_media_ids', []),
                        'created_at' => time()
                    ]];
                    $tabs = array_merge($eachVal['tabs'], $newtab);
                } else {
                    $newtab = [
                        'slug' => $this->mSlug($data['title']),
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'media_ids' => array_get($data, 'p_media_ids', []),
                        'created_at' => time()
                    ];
                    $tabs = [$newtab];
                }
            }
            $this->dams_repository->updateTabDamsRelation(
                $pid,
                $this->mSlug($data['title']),
                array_get($data, 'p_media_ids', [])
            );
            $this->tab_repository->save($pid, $tabs);
        }
    }

    /**
     * @param $data
     */
    public function saveEditTab($data)
    {
        $pid = $data['p_id'];
        $r_data = $this->tab_repository->getTabs($pid);
        if (!empty($r_data)) {
            foreach ($r_data as $eachVal) {
                if (isset($eachVal['tabs']) && !empty($eachVal['tabs'])) {
                    $tempArray = null;
                    $i = 0;
                    foreach ($eachVal['tabs'] as $eachTab) {
                        if ($eachTab['slug'] === $this->mSlug($data['ctitle'])) {
                            $newtab = [
                                'title' => $data['title'],
                                'slug' => $this->mSlug($data['title']),
                                'description' => $data['description'],
                                'media_ids' => array_get($data, 'p_media_ids', []),
                                'created_at' => time()
                            ];
                        } else {
                            $newtab = $eachTab;
                        }
                        $tempArray[$i] = $newtab;
                        $i++;
                    }
                }
            }
            $this->dams_repository->removeTabDamsRelation($pid, $this->mSlug($data['title']));
            $this->dams_repository->updateTabDamsRelation(
                $pid,
                $this->mSlug($data['title']),
                array_get($data, 'p_media_ids', [])
            );
            $this->tab_repository->save($pid, $tempArray);
        }
    }

    /**
     * @param $title
     * @return string
     */
    public function mSlug($title)
    {
        $slug = str_replace(" ", "-", $title);
        return strtolower($slug);
    }

    /**
     * @param $pid
     * @return mixed|string
     */
    public function getTabs($pid)
    {
        if (!empty($pid)) {
            return $this->tab_repository->getTabs($pid);
        } else {
            return "Invalid Program ID";
        }
    }

    /**
     * @param $p_id
     * @param $slug
     * @return mixed
     */
    public function deleteTab($p_id, $slug)
    {
        $data = $this->tab_repository->getTabs($p_id);
        if (!empty($data)) {
            foreach ($data as $value) {
                $tempArray = null;
                $i = 0;
                $p_slug = $value['program_slug'];
                foreach ($value['tabs'] as $eachVal) {
                    if ($eachVal['slug'] === $slug) {
                        continue;
                    }
                    $tempArray[$i] = $eachVal;
                    $i++;
                }
            }
            $this->tab_repository->save($p_id, $tempArray);
            return $p_slug;
        }
    }

    /**
     * @param $pid
     * @param $slug
     * @return null
     */
    public function getTabBySlug($pid, $slug)
    {
        if (!empty($slug) && !empty($pid)) {
            $data = $this->tab_repository->getTabBySlug($pid, $slug);
            foreach ($data as $eachValue) {
                if (isset($eachValue['tabs'])) {
                    foreach ($eachValue['tabs'] as $tab) {
                        if ($tab['slug'] === $slug) {
                            return $tab;
                        }
                    }
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * @param $p_id
     * @param $p_type
     * @param $title
     * @param null $c_slug
     * @return bool
     */
    public function cDuplicate($p_id, $p_type, $title, $c_slug = null)
    {
        $tab_array = $this->tab_repository->getTabs($p_id);
        $tab = collect(collect($tab_array)->first());
        if (isset($tab['tabs'])) {
            $data = $tab['tabs'];
        } else {
            $data = null;
        }
        if (!empty($data)) {
            $slug = $this->mSlug($title);
            if (empty($c_slug)) {
                foreach ($data as $value) {
                    if ($value['slug'] === $slug) {
                        return false;
                    }
                }
                return true;
            } else {
                $c_slug = $this->mSlug($c_slug);
                if ($c_slug === $slug) {
                    return true;
                } else {
                    foreach ($data as $value) {
                        if ($value['slug'] === $slug) {
                            return false;
                        }
                    }
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * @param $p_id
     * @param $p_type
     * @param $title
     * @param $c_slug
     * @return bool
     */
    public function checkDuplicateTab($p_id, $p_type, $title, $c_slug = null)
    {
        $tab_array = $this->tab_repository->getPackageTabs($p_id);
        $tab = collect(collect($tab_array)->first());
        if (isset($tab['tabs'])) {
            $data = $tab['tabs'];
        } else {
            $data = null;
        }
        if (!empty($data)) {
            $slug = $this->mSlug($title);
            if (empty($c_slug)) {
                foreach ($data as $value) {
                    if ($value['slug'] === $slug) {
                        return false;
                    }
                }
                return true;
            } else {
                $c_slug = $this->mSlug($c_slug);
                if ($c_slug === $slug) {
                    return true;
                } else {
                    foreach ($data as $value) {
                        if ($value['slug'] === $slug) {
                            return false;
                        }
                    }
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * @param $data
     */
    public function savePackageTab($data)
    {
        $pid = $data['p_id'];
        $r_data = $this->tab_repository->getPackageTabs($pid);
        if (!empty($r_data)) {
            $tabs = null;
            foreach ($r_data as $eachVal) {
                if (isset($eachVal['tabs']) && !empty($eachVal['tabs'])) {
                    $newtab = [[
                        'title' => $data['title'],
                        'slug' => $this->mSlug($data['title']),
                        'description' => $data['description'],
                        'media_ids' => array_get($data, 'p_media_ids', []),
                        'created_at' => time()
                    ]];
                    $tabs = array_merge($eachVal['tabs'], $newtab);
                } else {
                    $newtab = [
                        'slug' => $this->mSlug($data['title']),
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'media_ids' => array_get($data, 'p_media_ids', []),
                        'created_at' => time()
                    ];
                    $tabs = [$newtab];
                }
            }
            $this->dams_repository->updatePackageTabDamsRelation(
                $pid,
                $this->mSlug($data['title']),
                array_get($data, 'p_media_ids', [])
            );
            $this->tab_repository->savePackageTab($pid, $tabs);
        }
    }

    /**
     * @param $pid
     * @param $slug
     */
    public function getPackageTabBySlug($pid, $slug)
    {
        if (!empty($slug) && !empty($pid)) {
            $data = $this->tab_repository->getPackageTabBySlug($pid, $slug);
            foreach ($data as $eachValue) {
                if (isset($eachValue['tabs'])) {
                    foreach ($eachValue['tabs'] as $tab) {
                        if ($tab['slug'] === $slug) {
                            return $tab;
                        }
                    }
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * @param $data
     */
    public function saveEditPackageTab($data)
    {
        $pid = $data['p_id'];
        $r_data = $this->tab_repository->getPackageTabs($pid);
        if (!empty($r_data)) {
            foreach ($r_data as $eachVal) {
                if (isset($eachVal['tabs']) && !empty($eachVal['tabs'])) {
                    $tempArray = null;
                    $i = 0;
                    foreach ($eachVal['tabs'] as $eachTab) {
                        if ($eachTab['slug'] === $this->mSlug($data['ctitle'])) {
                            $newtab = [
                                'title' => $data['title'],
                                'slug' => $this->mSlug($data['title']),
                                'description' => $data['description'],
                                'media_ids' => array_get($data, 'p_media_ids', []),
                                'created_at' => time()
                            ];
                        } else {
                            $newtab = $eachTab;
                        }
                        $tempArray[$i] = $newtab;
                        $i++;
                    }
                }
            }
            $this->dams_repository->removePackageTabDamsRelation($pid, $this->mSlug($data['title']));
            $this->dams_repository->updatePackageTabDamsRelation(
                $pid,
                $this->mSlug($data['title']),
                array_get($data, 'p_media_ids', [])
            );
            $this->tab_repository->savePackageTab($pid, $tempArray);
        }
    }

    /**
     * @param $p_id
     * @param $slug
     * @return mixed
     */
    public function deletePackageTab($p_id, $slug)
    {
        $data = $this->tab_repository->getPackageTabs($p_id);
        if (!empty($data)) {
            foreach ($data as $value) {
                $tempArray = null;
                $i = 0;
                $p_slug = $value['package_slug'];
                foreach ($value['tabs'] as $eachVal) {
                    if ($eachVal['slug'] === $slug) {
                        continue;
                    }
                    $tempArray[$i] = $eachVal;
                    $i++;
                }
            }
            $this->tab_repository->savePackageTab($p_id, $tempArray);
            return $p_slug;
        }
    }
}
