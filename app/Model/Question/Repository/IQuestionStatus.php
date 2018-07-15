<?php

namespace App\Model\Question\Repository;

/**
 * Interface IQuestionStatus
 * @package App\Model\Question\Repository
 */
interface IQuestionStatus
{
    /**
     *
     */
    const ACTIVE = "ACTIVE";

    /**
     *
     */
    const DRAFT = "DRAFT";

    /**
     *
     */
    const DELETED = "DELETED";
}
