<?php

namespace App\Services\Common;

/**
 * Class ErrorCode
 *
 * @package App\Http\Services\Common
 */
abstract class ErrorCode
{
    const MODULE_NOT_FOUND = 011;

    const ACCESS_TOKEN_NOT_FOUND = 111;

    const PLAYER_NOT_FOUND = 120;

    const ACCESS_DENIED = 403;

    const CATEGORY_NOT_FOUND = 1101;

    const RELATION_NOT_FOUND = 1003;

    const IN_ACTIVE_USER = 1005;

    const ASSET_NOT_FOUND = 1201;

    const ASSET_REQUEST_ERROR = 1202;

    const QUESTION_NOT_FOUND = 1311;

    const QUESTION_KEYWORD_NOT_FOUND = 1312;

    const QUESTION_TAG_MAPPING_NOT_FOUND = 1313;

    const QUESTION_BANK_NOT_FOUND = 1321;

    const PROGRAM_QUESTION_NOT_FOUND = 512;

    const POST_NOT_FOUND = 1401;

    const POST_QUESTION_NOT_FOUND = 521;

    const NO_POST_ASSIGNED = 1403;

    const QUIZ_NOT_FOUND = 1601;

    const NO_QUIZ_ASSIGNED = 1603;

    const NO_QUESTIONS_FOUND = 1604;

    const QUIZ_ATTEMPT_CLOSED = 1605;

    const ATTEMPT_NOT_ALLOWED = 1606;

    const ANNOUNCEMENT_NOT_FOUND = 1701;

    const EVENT_NOT_FOUND = 1801;

    const NO_EVENT_ASSIGNED = 1802;

    const PROGRAM_NOT_FOUND = 1901;

    const NO_PROGRAM_ASSIGNED = 1902;

    // Error codes for roles and permissions module
    const CONTEXT_NOT_FOUND = 211;

    const ROLE_NOT_FOUND = 221;

    const USER_ROLE_MAPPING_NOT_FOUND = 231;

    const PERMISSION_NOT_FOUND = 241;

    // Error codes for user modules
    const USER_NOT_FOUND = 111;

    const USER_ENTITY_RELATION_NOT_FOUND = 112;

    //Error codes for usergroup
    const USER_GROUP_NOT_FOUND = 121;

    const MEDIA_NOT_FOUND = 2101;

    //Error codes for package
    const PACKAGE_NOT_FOUND = 300;

    const PACKAGE_CANNOT_UN_ASSIGN_PROGRAMS = 301;

    const NO_PACKAGE_ASSIGNED = 302;

    const BOX_DOCUMENT_NOT_FOUND = 2201;

    const BOX_DOCUMENT_UPLOAD_FAILED = 2201;

    const IN_SECURE_CONNECTION = 2301;

    const INVALID_REQUEST = 2302;

    const INVALID_CREDENTIALS = 2303;

    const MISSING_MANDATORY_FIELDS = 2304;

    const SSO_INVALID_TOKEN = 2305;

    const SSO_TOKEN_EXPIRED = 2306;

    const SSO_TOKEN_NOT_FOUND = 2307;
}
