<?php

namespace App\Exceptions\Box;

use App\Exceptions\ApplicationException;
use App\Services\Common\ErrorCode;

class BoxDocumentUploadFailedException extends ApplicationException
{
    public function __construct($message = null)
    {
        parent::__construct(ErrorCode::BOX_DOCUMENT_UPLOAD_FAILED, $message);
    }
}
