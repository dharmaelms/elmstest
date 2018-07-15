<?php

namespace App\Exceptions\Category;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class CategoryNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::CATEGORY_NOT_FOUND, $message);
    }
}
