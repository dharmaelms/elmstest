<?php

namespace App\Helpers\DAMS;

use Illuminate\Support\Facades\Storage;

class ScormHelper
{
    /**
     * @param string $scormFileLocation
     * @return array
     */
    public static function getScormConfigData($scormFileLocation)
    {
        $scorm_data = [];

        $imsManifestXMLFilePath = "{$scormFileLocation}/imsmanifest.xml";

        if (Storage::disk("public_dams")
            ->exists($imsManifestXMLFilePath)) {
            $scormManifestReader = new \XMLReader();
            $scorm_manifest_file_content = Storage::disk("public_dams")
                ->get($imsManifestXMLFilePath);
            $scormManifestReader->xml($scorm_manifest_file_content);

            $scorm_version = null;
            $scorm_launch_file = null;
            while ($scormManifestReader->read()) {
                if ($scormManifestReader->nodeType !== \XMLReader::END_ELEMENT) {
                    switch ($scormManifestReader->name) {
                        case "schemaversion":
                            $scorm_version = $scormManifestReader->readInnerXml();
                            break;
                        case "resource":
                            $attributeScormType = $scormManifestReader->getAttribute("adlcp:scormtype");
                            if (!is_null($attributeScormType) && ($attributeScormType === "sco")) {
                                $scorm_launch_file = $scormManifestReader->getAttribute("href");
                            }
                            break;
                        case "adlcp:masteryscore":
                            $mastery_score = $scormManifestReader->readInnerXml();
                            $scorm_data["scorm_mastery_score"] = is_numeric($mastery_score)?
                                (int) $mastery_score : null;
                            break;
                    }
                }

                if (!is_null($scorm_version) && !is_null($scorm_launch_file)) {
                    break;
                }
            }

            $scormManifestReader->close();

            $scorm_data["scorm_version"] = $scorm_version;
            $scorm_data["scorm_launch_file"] = $scorm_launch_file;
        }

        return $scorm_data;
    }
}
