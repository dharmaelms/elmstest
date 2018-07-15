<?php

namespace App\Services\ChannelCompletionTillDate;

use App\Model\ChannelCompletionTillDate\Repository\ChannelCompletionTillDateRepository;

/**
 * class ChannelCompletionTillDateService
 * @package App\Services\ChannelCompletionTillDate
 */
class ChannelCompletionTillDateService implements IChannelCompletionTillDateService
{
    private $cha_com_td_repo;
    
    public function __construct(ChannelCompletionTillDateRepository $cha_com_td_repo)
    {
        $this->cha_com_td_repo = $cha_com_td_repo;
    }
    /**
     * {@inheritdoc}
     */
    public function getSpecificChannelUserCompletion($channel_id, $user_ids)
    {
        return $this->cha_com_td_repo->getSpecificChannelUserCompletion($channel_id, $user_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserChannelCompletionDetails($channel_id, $user_id)
    {
        return $this->cha_com_td_repo->getUserChannelCompletionDetails($channel_id, $user_id);
    }
}