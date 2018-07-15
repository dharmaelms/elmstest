<?php

namespace App\Exceptions\Box;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class BoxDocumentNotFoundException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::BOX_DOCUMENT_NOT_FOUND, $message);
    }
}
