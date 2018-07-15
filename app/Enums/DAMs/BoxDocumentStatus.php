<?php
namespace App\Enums\DAMs;

use App\Enums\BaseEnum;

/**
 * Class BoxDocumentStatus
 *
 * @package App\Enums\BaseEnum
 */
final class BoxDocumentStatus extends BaseEnum
{
    /**
     * Document is added to DAMs collection.
     */
    const PENDING = 'PENDING';

    /**
     * JOB starts uploading file to BOX.
     */
    const UPLOADING = 'UPLOADING';

    /**
     * After BOX upload success by JOB.
     */
    const UPLOADED = 'UPLOADED';
}
