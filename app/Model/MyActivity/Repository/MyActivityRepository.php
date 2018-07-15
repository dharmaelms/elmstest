<?php
namespace App\Model\MyActivity\Repository;

use App\Model\MyActivity;

/**
 * class MyActivityRepository
 * @package App\Model\MyActivity\Repository
 */
class MyActivityRepository implements IMyActivityRepository
{
    /**
     * {@inheritdoc}
     */
    public function addAttemptStatus($quiz_name, $quiz_id, $return_url)
    {
        return MyActivity::getInsertActivity([
                        'module' => 'assessment',
                        'action' => 'continue attempt',
                        'module_name' => $quiz->quiz_name,
                        'module_id' => (int)$quiz->quiz_id,
                        'url' => $return_url,
                    ]);
    }

    /**
     * {@inheritdoc}
     */
    public function addActivity($data)
    {
        return MyActivity::getInsertActivity($data);
    }
}
