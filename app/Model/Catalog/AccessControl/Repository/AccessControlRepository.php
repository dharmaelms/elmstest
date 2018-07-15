<?php

namespace App\Model\Catalog\AccessControl\Repository;

use App\Model\Program;
use App\Model\Transaction;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\Package\Entity\Package;

/**
 * Class AccessControlRepository
 * @package App\Model\Catalog\AccessControl\Repository
 */
class AccessControlRepository implements IAccessControlRepository
{

    /**
     * @param $p_data
     * @param $u_data
     * @return void
     */
    public function enroll($p_data, $u_data)
    {
        if (isset($p_data['p_type']) && $p_data['p_type'] == "package") {
            $program = Package::getActivePackage($p_data['p_slug']);
        } else {
            $program = Program::getProgram($p_data['p_slug']);
        }

        if (isset($p_data['p_type']) && $p_data['p_type'] == "package") {
            $slug = Package::getPackageDetailsByID($p_data['p_id']);
            $trans_id = Transaction::uniqueTransactionId();
            $transaction = $this->mTData($trans_id, $u_data);
            $transaction_details = $this->mTDetDataForPackage($trans_id, $p_data, $u_data['uid'], $p_data['p_id'], $slug);
            Transaction::insert($transaction);
            TransactionDetail::insert($transaction_details);
            foreach ($program[0]['program_ids'] as $child_id) {
                $slug = Program::getProgramDetailsByID($child_id);
                $trans_id = Transaction::uniqueTransactionId();
                $transaction = $this->mTData($trans_id, $u_data);
                $transaction_details = $this->mTDetData($trans_id, $p_data, $u_data['uid'], $child_id, $slug);
                Transaction::insert($transaction);
                TransactionDetail::insert($transaction_details);
            }
        } else {
            $trans_id = Transaction::uniqueTransactionId();
            $transaction = $this->mTData($trans_id, $u_data);
            $transaction_details = $this->mTDetData($trans_id, $p_data, $u_data['uid']);
            Transaction::insert($transaction);
            TransactionDetail::insert($transaction_details);
        }
    }

    /**
     * @param $trans_id
     * @param $u_data
     * @return array
     */
    private function mTData($trans_id, $u_data)
    {
        $now = time();
        return [
            'DAYOW' => date('l', $now),
            'DOM' => (int)date('j', $now),
            'DOW' => (int)date('w', $now),
            'DOY' => (int)date('z', $now),
            'MOY' => (int)date('n', $now),
            'WOY' => (int)date('W', $now),
            'YEAR' => (int)date('Y', $now),
            'trans_level' => 'usergroup',
            'id' => null,
            'created_date' => time(),
            'trans_id' => (int)$trans_id,
            'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
            'access_mode' => 'assigned_by_ecommerce_admin',
            'added_by' => $u_data['username'],
            'added_by_name' => $u_data['firstname'] . ' ' . $u_data['lastname'],
            'created_at' => time(),
            'updated_at' => time(),
            'type' => 'subscription',
            'status' => 'COMPLETE', // This is transaction status
        ];
    }

    /**
     * @param $trans_id
     * @param $p_data
     * @param $u_id
     * @param null $program_id
     * @param null $slug
     * @return array
     */
    private function mTDetData($trans_id, $p_data, $u_id, $program_id = null, $slug = null)
    {
        if ($program_id > 0) {
            return [
                'trans_level' => 'user',
                'id' => $u_id,
                'trans_id' => (int)$trans_id,
                'program_id' => (int)$program_id,
                'package_id' => $p_data['p_id'],
                'program_sub_type' => 'collection',
                //'program_slug' => $p_data['p_slug'],
                'program_slug' => $slug['program_slug'],
                'type' => $p_data['p_type'],
                'program_title' => $slug['program_title'],
                'duration' => [ // Using the same structure from duration master
                    'label' => 'Forever',
                    'days' => 'forever',
                ],
                'start_date' => '', // Empty since the duration is forever
                'end_date' => '', // Empty since the duration is forever
                'created_at' => time(),
                'updated_at' => time(),
                'status' => 'COMPLETE',
            ];
        } else {
            return [
                'trans_level' => 'user',
                'id' => $u_id,
                'trans_id' => (int)$trans_id,
                'program_id' => $p_data['p_id'],
                'program_slug' => $p_data['p_slug'],
                'type' => $p_data['p_type'],
                'program_title' => $p_data['p_title'],
                'duration' => [ // Using the same structure from duration master
                    'label' => 'Forever',
                    'days' => 'forever',
                ],
                'start_date' => '', // Empty since the duration is forever
                'end_date' => '', // Empty since the duration is forever
                'created_at' => time(),
                'updated_at' => time(),
                'status' => 'COMPLETE',
            ];
        }
    }

    /**
     * @param $trans_id
     * @param $p_data
     * @param $u_id
     * @param null $program_id
     * @param null $slug
     * @return array
     */
    private function mTDetDataForPackage($trans_id, $p_data, $u_id, $program_id = null, $slug = null)
    {
        return [
            'trans_level' => 'user',
            'id' => $u_id,
            'trans_id' => (int)$trans_id,
            'program_id' => (int)$program_id,
            'package_id' => $p_data['p_id'],
            'program_sub_type' => 'collection',
            //'program_slug' => $p_data['p_slug'],
            'program_slug' => $slug['package_slug'],
            'type' => $p_data['p_type'],
            'program_title' => $slug['package_title'],
            'duration' => [ // Using the same structure from duration master
                'label' => 'Forever',
                'days' => 'forever',
            ],
            'start_date' => '', // Empty since the duration is forever
            'end_date' => '', // Empty since the duration is forever
            'created_at' => time(),
            'updated_at' => time(),
            'status' => 'COMPLETE',
        ];
    }

    /**
     * @param $u_id
     * @param $product_id
     * @return void
     */
    public function updateRelation($u_id, $product_id, $p_type = null)
    {
        if (isset($p_type) && $p_type == "package") {
            $program = Package::getPackageDetailsByID($product_id);
        } else {
            $program = Program::getProgramDetailsByID($product_id);
        }

        if (isset($p_type) && $p_type == "package") {
            Program::updateFeedRelation($product_id, 'active_user_feed_rel', $u_id);
            User::addUserRelation($u_id, ['user_parent_feed_rel'], $product_id);
            foreach ($program['program_ids'] as $child_id) {
                User::addUserRelation($u_id, ['user_package_feed_rel'], $child_id);
            }
        } elseif ($program['program_type'] === "course") {
            Program::updateFeedRelation($product_id, 'active_user_feed_rel', $u_id);
            User::addUserRelation($u_id, ['user_course_rel'], $product_id);
        } else {
            Program::updateFeedRelation($product_id, 'active_user_feed_rel', $u_id);
            User::addUserRelation($u_id, ['user_feed_rel'], $product_id);
        }
    }


    /**
     * @param $l_pgm_id
     * @param $u_id
     * @return void
     */
    public function unEnrollSubscription($l_pgm_id, $u_id)
    {

        $p_list = Program::whereIn('program_id', $l_pgm_id)->get();
        if (!empty($p_list)) {
            $p_list = $p_list->toArray();
            foreach ($p_list as $key => $program) {
                if (isset($program['relations'])) {
                    if (isset($program['relations']['active_user_feed_rel'])) {
                        if (isset($program['program_sub_type']) && $program['program_sub_type'] == 'collection') {
                            TransactionDetail::updateStatusByLevel(
                                'user',
                                $u_id,
                                (int)$program['program_id'],
                                ['status' => 'IN-ACTIVE'],
                                'collection',
                                (int)$program['program_id']
                            );
                            User::removeUserRelation($u_id, ['user_parent_feed_rel'], (int)$program['program_id']);
                            Program::removeFeedRelation($program['program_id'], ['active_user_feed_rel'], $u_id);
                            if (
                                isset($program['child_relations']['active_channel_rel']) &&
                                !empty($program['child_relations']['active_channel_rel'])
                            ) {
                                foreach ($program['child_relations']['active_channel_rel'] as $child_id) {
                                    TransactionDetail::updateStatusByLevel(
                                        'user',
                                        $u_id,
                                        (int)$child_id,
                                        ['status' => 'IN-ACTIVE'],
                                        'collection',
                                        (int)$program['program_id']
                                    );
                                    User::removeUserRelation($u_id, ['user_package_feed_rel'], (int)$child_id);
                                }
                            }
                        } else {
                            TransactionDetail::updateStatusByLevel(
                                'user',
                                $u_id,
                                (int)$program['program_id'],
                                ['status' => 'IN-ACTIVE']
                            );
                            User::removeUserRelation($u_id, ['user_feed_rel'], (int)$program['program_id']);
                            Program::removeFeedRelation($program['program_id'], ['access_request_granted'], $u_id);
                            Program::removeFeedRelation($program['program_id'], ['active_user_feed_rel'], $u_id);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $p_id
     * @param $u_id
     * @param $subscription
     * @return void
     */
    public function updateTransaction($p_id, $u_id, $subscription)
    {
        Transaction::where('id', '=', $u_id)
            ->where('program_id', '=', (int)$p_id)
            ->update(['subscription' => $subscription, ['upsert' => true]]);
    }
}
