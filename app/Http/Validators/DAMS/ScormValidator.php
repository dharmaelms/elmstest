<?php

namespace App\Http\Validators\DAMS;

use Illuminate\Support\Facades\Validator;
use ZipArchive;

class ScormValidator
{
    public static function extendValidatorToValidateScorm()
    {
        Validator::extend(
            "unsupported_files_not_exist",
            function ($attribute, $file) {
                $validation_flag = false;

                $zipArchive = new ZipArchive();
                $fileAbsolutePath = $file->getRealPath();

                $flag_zip_open = $zipArchive->open($fileAbsolutePath);
                if (($flag_zip_open === true) || ($flag_zip_open === ZipArchive::ER_EXISTS)) {
                    for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
                        $zipArchiveEntry = $zipArchive->statIndex($i);

                        $validation_flag = (($file_extension_index = strrpos($zipArchiveEntry["name"], ".")) !== false)?
                            !in_array(
                                substr($zipArchiveEntry["name"], ($file_extension_index + 1)),
                                config("app.scorm_incompatible_files")
                            ) : true;

                        if (!$validation_flag) {
                            break;
                        }
                    }
                }

                return $validation_flag;
            }
        );

        Validator::extend(
            "imsmanifest_exists",
            function ($attribute, $file) {
                $zipArchive = new ZipArchive();
                $fileAbsolutePath = $file->getRealPath();

                $flag_zip_open = $zipArchive->open($fileAbsolutePath);

                return (($flag_zip_open === true) || ($flag_zip_open === ZipArchive::ER_EXISTS))?
                    (($zipArchive->locateName("imsmanifest.xml") !== false) ? true : false) : false;
            }
        );
    }
}
