<?php

namespace App\Model\Dams\Repository;

use App\Model\Dam;
use URL;
use App\Exceptions\Dams\MediaNotFoundException;
use stdClass;

/**
 * Class DamsRepository
 * @package App\Model\Dams\Repository
 */
class DamsRepository implements IDamsRepository
{
    /**
     * @inheritDoc
     */
    public function get($filter_params = [])
    {
        return Dam::filter($filter_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaEmbedCode(Dam $media)
    {
        $embedCode = null;
        switch ($media->type) {
            case "video":
                $embedCode = $media->embed_code;
                break;
            case "audio":
                $embedCode = self::getAudioEmbedCode($media);
                break;
            case "image":
                $embedCode = self::getImageEmbedCode($media);
                break;
            case "document":
                break;
        }

        return str_replace(['http:','https:'], '', $embedCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getAudioEmbedCode(Dam $audio)
    {
        if ($audio->asset_type === "file") {
            return "<iframe src=\"" . URL::route("media", ["id" => $audio->_id]) . "\" frameborder=\"0\" width=\"71%\" height=\"60px\" class=\"question-media\" data-media-id=\"{$audio->_id}\"></iframe>";
        } else {
            return "<a href=\"{$audio->url}\" target=\"_blank\" class=\"question-media\" data-media-id=\"{$audio->_id}\">{$audio->name}</a>";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImageEmbedCode(Dam $image)
    {
        if ($image->asset_type === "file") {
            if (isset($image->public_file_location)) {
                $imageLocation = base_path() . "/public/" . $image->public_file_location;
                if (file_exists($imageLocation)) {
                    list($width, $height) = getimagesize($imageLocation);
                    $newWidth = 365; // TODO: Analyse the reason for hardcoded value
                    if ($width < $newWidth) {
                        $newWidth = $width;
                    }
                    $newHeight = ($height / $width) * $newWidth;
                    return "<img src=\"" . URL::to("media_image/{$image->_id}") . "\" class=\"question-media\" data-media-id=\"{$image->_id}\" width=\"{$newWidth}\" height=\"{$newHeight}\">";
                }
                // TODO: Else condition to send default image.
            }
            // TODO: Else condition to send default image.
        } else {
            return "<a href=\"{$image->url}\" target=\"_blank\" class=\"question-media\" data-media-id=\"{$image->_id}\">{$image->name}</a>";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateBoxDetailsStatus($document, $data)
    {
        $box_details = new stdClass();

        $box_details->document_id = $data->document_id;
        $box_details->status = $data->status;
        $box_details->uploaded_at = $data->uploaded_at;
        $box_details->folder_name = ((isset($data->folder_name)) ? $data->folder_name : "");
        $document->box_details = $box_details;
        $document->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getMedia($key, $id_type = '_id')
    {
        if (in_array($id_type, ['_id', 'id'])) {
            $media = Dam::where($id_type, $key)->where('status', '!=', 'DELETED')->get()->first();
        }
        if (!isset($media) || is_null($media)) {
            throw new MediaNotFoundException();
        }
        return $media;
    }

    /**
     * @inheritdoc
     */
    public function updateTabDamsRelation($program_id, $tab_slug, $ids)
    {
        Dam::whereIn('_id', $ids)->push('relations.tab_dam_rel.'.$program_id, $tab_slug);
    }

    /**
     * @inheritdoc
     */
    public function updatePackageTabDamsRelation($package_id, $tab_slug, $ids)
    {
        Dam::whereIn('_id', $ids)->push('relations.package_tab_dam_rel.'.$package_id, $tab_slug);
    }

    /**
     * @inheritdoc
     */
    public function removeTabDamsRelation($program_id, $tab_slug)
    {
        Dam::where('relations.tab_dam_rel.'.$program_id, '$exists', true)
                ->pull('relations.tab_dam_rel.'.$program_id, $tab_slug);
    }

    /**
     * @inheritdoc
     */
    public function removePackageTabDamsRelation($package_id, $tab_slug)
    {
        Dam::where('relations.package_tab_dam_rel.'.$package_id, '$exists', true)
                ->pull('relations.package_tab_dam_rel.'.$package_id, $tab_slug);
    }

    public function getTypeScormRecords($assigned_items, $type, $start = 0, $limit = 10)
    {
        $query =  DAM::where('type', '=', $type)
                   ->where('relations.dams_packet_rel', 'exists', true)
                   ->where('relations.dams_packet_rel', '!=', []);
        if (!empty($assigned_items)) {
                    $query->whereIn('id', $assigned_items);
        }
        if ($limit > 0) {
            $query->skip((int)$start)
                ->take((int)$limit);
        }
        return $query->orderBy('id', 'desc')->get(['id', 'relations.dams_packet_rel', 'name']);
    }

    /**
     * @inheritdoc
     */
    public function getDAMSDataUsingIDS($ids)
    {
        return DAM::whereIn('id', $ids)->get()->toArray();
    }

    /**
     * @inheritdoc
     */
    public function getItemsCount($item_ids, $date)
    {
        $query = DAM::where('status', '=', 'ACTIVE');
        if (!empty($item_ids)) {
            $query->whereIn('id', $item_ids);
        }
        return $query->whereBetween('created_at', $date)->count();
    }

    /**
     * @inheritdoc
     */
    public function getNewItems($item_ids, $date, $start, $limit)
    {
        $query = DAM::whereBetween('created_at', $date);
        if (!empty($item_ids)) {
            $query->whereIn('id', $item_ids);
        }
        return $query->skip((int)$start)->take((int)$limit)->orderBy('created_at', 'desc')->get(['name', 'type']);
    }


    /**
     * @inheritdoc
     */
    public function countActiveItems($items_ids, $item_type)
    {
        if (!empty($items_ids)) {
            return DAM::whereIn('id', $items_ids)->where('type', $item_type)->where('status', 'ACTIVE')->count();
        } else {
            return DAM::where('status', 'ACTIVE')->where('type', $item_type)->count();
        }
    }
}
