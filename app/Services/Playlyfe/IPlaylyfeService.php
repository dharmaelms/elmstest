<?php namespace App\Services\Playlyfe;

interface IPlaylyfeService
{
    public function isPlaylyfeEnabled();

    public function processEvent($eventData);

    public function createUser($data);

    public function getPlayerProfile($userID);

    public function getActivity($userID, $filter = "all", $skip = 0, $take = 3);

    public function renderChanges($change, $size);

    /**
     * Patch all actions
     * @param $input
     */
    public function patchActions($input);
}
