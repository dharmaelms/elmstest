<?php

namespace App\Model;

use Moloquent;

class QuizReminderLog extends Moloquent
{
    protected $collection = 'quiz_reminder_log';

    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

}
