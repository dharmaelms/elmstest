<?php

namespace App\Libraries;

use Auth;
use Carbon;
use Timezone as TZ;

class Helpers
{
    /**
     * Convert number of seconds to hours, minutes and seconds
     * @param integer $seconds
     * @return string
     */
    public static function secondsToTimeString($duration)
    {
        if ($duration <= 0) {
            return '0 Seconds';
        }
        $periods = [
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1
        ];

        $parts = [];

        foreach ($periods as $name => $dur) {
            $div = floor($duration / $dur);

            if ($div == 0) {
                continue;
            } elseif ($div == 1) {
                    $parts[] = $div . " " . $name;
            } else {
                $parts[] = $div . " " . $name . "s";
            }
            $duration %= $dur;
        }

        $last = array_pop($parts);

        if (empty($parts)) {
            return $last;
        } else {
            return join(', ', $parts) . " and " . $last;
        }
    }

    public static function highlight($keyword, $str)
    {
        $keyword = implode('|', explode(' ', preg_quote($keyword)));
        $str = preg_replace("/($keyword)/i", '<span class="yellow-bg"><b>$0</b></span>', $str);
        return $str;
    }
    /**
     * this function is used to convert bytes to GB, MB, KB
     * @param  integer $bytes
     * @return string
     */
    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
    /**
     * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
     *
     * @param string $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param string $ending Ending to be appended to the trimmed string.
     * @param boolean $exact If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     *
     * @return string Trimmed string.
     */
    public static function truncate($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
    {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = [];
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                    // if tag is a closing tag
                    } elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                    // if tag is an opening tag
                    } elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length> $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1]+1-$entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if ($total_length>= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= $ending;
        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
    }

    /**
     * Method used to substr without breaking words
     * @param string $string string
     * @param int $start
     * @param int $length
     * @return string
     */
    public static function stripString($string, $start, $length)
    {
        if (strlen($string) < $length) {
            return $string;
        }
        return preg_replace('/\s+?(\S+)?$/', '', substr($string, $start, $length));
    }

    /**
     * Method for timestamp to hours
     * @param int $seconds
     * @param string $format - optional
     * @return string
     */
    public static function secondsToString($timestamp, $format = 'H:i:s')
    {
        return gmdate($format, (int)$timestamp);
    }

    /**
     * Method to calculate percentage
     * @param int $numerator
     * @param int $denominator
     * @param int $percentage
     * @return double
     */
    public static function getPercentage($numerator, $denominator, $percentage = 100)
    {
        $denominator = (int) $denominator >= 1 ? $denominator : 1;
        return round(($numerator/$denominator) * $percentage, 2);
    }

    /**
     * validateDates convert date string to timestamp and validate with threshold date limits
     * @param  string $start_date date string
     * @param  string $end_date date string
     * @return array Array of start and end date as timestamp
     */
    public static function validateDates($start_date, $end_date)
    {
        $default_start_date = Carbon::today()->subDays((int)config('app.default_date_range_selected'))->getTimestamp();
        $default_end_date = time();
        if (!is_null($start_date) && $start_date != '' && !is_null($end_date) && $end_date != '') {
            $default_start_date = (int)TZ::convertToUTC($start_date, Auth::user()->timezone, "U");
            $default_end_date = (int)TZ::convertToUTC($end_date, Auth::user()->timezone, "U");
            //Adding a end of the day
            $default_end_date = $default_end_date + (24*60*60) - 1;
        }
        return ["start_date" => $default_start_date, "end_date" => $default_end_date];
    }
}
