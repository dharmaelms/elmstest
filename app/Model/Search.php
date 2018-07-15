<?php

namespace App\Model;

use Auth;
use Config;
use Moloquent;
use Solarium\Client;

class Search extends Moloquent
{
    public $timestamps = false;

    public static function solrSearch($input, $startpage = 0, $facets = [])
    {
        // print_r($facets);die;
        //$stop_words=Product::$stop_words;
        User::getUserSubscribedFeedIds();
        $perpage = 5;
        $perpage = SiteSetting::module('Search', 'results_per_page');
        $pg = 0;
        if ($startpage > 0) {
            $pg = $perpage * $startpage;
        }
        $config = Config::get('app.config');
        $tid = '';
        $term = $input['key'];
        $words = explode(' ', $term);
        // $words=array_diff($words,$stop_words);
        $term = implode(' ', $words);

        if (!empty($input['tid'])) {
            $tid = $input['tid'];
        }
        // create a client instance
        $client = new Client($config);
        // execute the ping query
        $query = $client->createSelect();
        $facetSet = $query->getFacetSet();
        $facetSet->setMinCount(1);
        // //$facetSet->setLimit(1);

        $facet = $facetSet->createFacetField('category_facet')->setField('category_facet')->addExclude('p1');
        $facet = $facetSet->createFacetField('format_facet')->setField('format_facet')->addExclude('p1');
        // $facet=$facetSet->createFacetField('program_title_facet')->setField('program_title_facet')->addExclude('p1');

        // //create a facet field instance and set options
        // $facet = $facetSet->createFacetMultiQuery('PublishYear')->addExclude('p1');
        // $facet->createQuery('1981-1990', 'PublishYear:[1981 TO 1990]');
        // $facet->createQuery('1991-2000', 'PublishYear:[1991 TO 2000]');
        // $facet->createQuery('2001-2010', 'PublishYear:[2001 TO 2010]');
        // $facet->createQuery('>2011','PublishYear:[2011 TO *]');

        // $facet = $facetSet->createFacetMultiQuery('Price.PriceAmount')->addExclude('p1');
        // $facet->createQuery('1-100', 'Price.PriceAmount:[1 TO 100]');
        // $facet->createQuery('101-200', 'Price.PriceAmount:[101 TO 200]');
        // $facet->createQuery('201-500', 'Price.PriceAmount:[201 TO 500]');
        // $facet->createQuery('501-1000', 'Price.PriceAmount:[501 TO 1000]');
        // $facet->createQuery('>1001', 'Price.PriceAmount:[1001 TO *]');

        // get the dismax component and set a boost query
        $edismax = $query->getEDisMax();
        $fields = 'all_title^10.0 all_keywords^7.0 all_description^5.0 question^2.0 answer^2.0';
        $options = $edismax->setQueryFields($fields);
        //echo $input['search_type'];die;
        // if(count($filter_query)>0 && $input['search_type'] =="shelf_simple")
        // {   $id=implode(" ",$filter_query);
        //  $query->createFilterQuery('productfilter')->setQuery('ProductIdentifier.IDValue:('.$id.') OR collection_meta:('.$id.')');
        // }
        // echo $options=$edismax->getQueryFields();die;
        // print_r($options);die;
        // this query is now a dismax quer4queryy

        $query->setQuery($term);
        $query->setResponseWriter('phps');
        $query->setStart($pg)->setRows($perpage);
        // if($tid)
        // { $sorts=explode("-", $tid);//print_r($sorts);die;
        //      $field=$sorts[0];
        //    $dir = ($sorts[1]=="high") ? $query::SORT_DESC : $query::SORT_ASC;
        //    $query->addSort($field, $dir);// die;
        // }
        // if($facetkey && $facetval)
        // {  if(strstr($facetval, "-"))
        //         {
        //             $facetval=str_replace("-", " TO ", $facetval);
        //      $query->createFilterQuery('price')->setQuery($facetkey.":[".$facetval.']')->addTag('p1');
        //         }
        //         elseif(strstr($facetval, ">"))
        //         {
        //             $facetval=str_replace(">", "", $facetval);
        //             $facetval .=" TO *";
        //      $query->createFilterQuery('price')->setQuery($facetkey.":[".$facetval.']')->addTag('p1');
        //         }
        //        else
        //        {
        //               $query->createFilterQuery('ss')->setQuery($facetkey.":".$facetval)->addTag('p1');
        //        }

        //        }
        // $query->createFilterQuery('statusportal')->setQuery("Status:\"sell\"");
        if (count($facets) > 0) {
            $j = 0;

            foreach ($facets as $facetkey => $eachfacetval) {
                ++$j;
                $str = '';
                $i = 0;

                foreach ($eachfacetval as $facet => $facetval) {
                    if ($i > 0) {
                        $str .= ' OR ';
                    }

                    if (strstr($facetval, '-')) {
                        $key = 'key' . $i . $j;
                        $facetval = str_replace('-', ' TO ', $facetval);
                        $str .= '[' . $facetval . ']';
                    } elseif (strstr($facetval, '>')) {
                        $key = 'keyss' . $i . $j;
                        $facetval = str_replace('>', '', $facetval);
                        $facetval .= ' TO *';
                        $str .= '[' . $facetval . ']';

                        //$query->createFilterQuery('price')->setQuery($facetkey.":[".$facetval.']')->addTag('p1');
                    } else {
                        $key = "$facetval" . $i . $j;
                        $str .= '"' . $facetval . '"';
                        //$query->createFilterQuery('ss')->setQuery($facetkey.":".$facetval)->addTag('p1');
                    }
                    ++$i;
                }

                $exclude = '';
                // switch($facetkey)
                //  {
                //   case'Price.PriceAmount':
                //   $exclude="pr";break;
                //   case'pname':
                //   $exclude="pn";break;
                //   case'PriceForm':
                //   $exclude="pf";break;
                //   case'PublishYear':
                //   $exclude="py";break;
                //  }
                $query->createFilterQuery($key)->setQuery($facetkey . ':(' . $str . ')')->addTag('p1');
            }
            //$query->createFilterQuery('price')->setQuery('Price.PriceAmount:([201 TO 500] OR  [501 TO 1000])')->addTag('p1');
        }

        //$query->addSort('PublishYear', $query::SORT_DESC);
        //$hl = $query->getHighlighting();
        // $hl->setFields('Title.TitleText');
        // $hl->setSimplePrefix('<b>');
        // $hl->setSimplePostfix('</b>');

        $relation = User::getUserSearchableContentIds();
        // echo "<pre>"; print_r($relation); die;
        if (!empty($relation)) {
            $query->createFilterQuery('fq')->setQuery($relation);
        } else {
            $user_filter = 'id:(null)';
            $query->createFilterQuery('ids')->setQuery($user_filter);
        }

        $result = $client->select($query);
        // echo "<pre>";print_r($result); die;
        // $highlighting = $result->getHighlighting();
        //$high=iterator_to_array($highlighting,true);

        //$high= array_map('iterator_to_array',$high);
        // echo "<pre>";print_r($high);
        $cnt = $result->getNumFound();
        // display facet counts
        //echo '<hr/>Facet counts for field "inStock":<br/>';
        $facet = $result->getFacetSet()->getFacet('category_facet');
        $facet = iterator_to_array($facet, true);
        $facets['category'] = $facet;
        $facet = $result->getFacetSet()->getFacet('format_facet');
        $facet = iterator_to_array($facet, true);
        $facets['format'] = $facet;
        // $facet = $result->getFacetSet('program_title_facet')->getFacet('program_title_facet');
        // $facet=iterator_to_array($facet,true);
        // $facets['programs']=$facet;

        // foreach (array_keys($facets['price'], 0, true) as $key) {
        //      unset($facets['price'][$key]);
        //  }
        // echo "<pre>";print_r($facets);die;
        // // echo '<hr/>Facet counts for field "inStock":<br/>';
        //  $facet = $result->getFacetSet('PublishYear')->getFacet('PublishYear');
        //  $facet=iterator_to_array($facet,true);
        //  $facets['year']=$facet;
        //  foreach (array_keys($facets['year'], 0, true) as $key) {
        //     unset($facets['year'][$key]);
        // }
        // // echo "<pre>";print_r($facet);
        // // echo '<hr/>Facet counts for field "inStock":<br/>';
        //  $facet = $result->getFacetSet('ProductForm')->getFacet('ProductForm');
        //  $facet=iterator_to_array($facet,true);
        //  $facets['binding']=$facet;
        ///echo "<pre>";print_r($facets);die;

        // echo '<hr/>Facet counts for field "inStock":<br/>';
        //$facet =$result->getFacetSet('pname')->getFacet('pname');
        //echo "<pre>";print_r($facet);
        //$facet=iterator_to_array($facet,true);
        //$facets['publisher']=$facet;

        $result = iterator_to_array($result, true);
        $result = array_map('iterator_to_array', $result);
        $result['results'] = $result;

        $result['cnt'] = $cnt;
        $result['facets'] = $facets;
        $result['pg'] = $pg;
        $result['per_page'] = $perpage;
        // echo "<pre>";print_r($result); die;

        return $result;
        // display the total number of documents found by solr
    }

    public static function solrAdvancedSearch($fields = [], $type = 'portal', $startpage = 0, $facets = [])
    {
        // echo "<pre>";print_r($fields);die;
        $results = [];
        $tid = '';
        $perpage = 10;
        $perpage = SiteSetting::module('Search', 'results_per_page');
        $pg = 0;
        $pg = $perpage * $startpage;

        //echo 'Solarium library version: ' . Solarium\Client::VERSION . ' - ';
        $config = Config::get('app.config');
        // if(!empty($fields['tid']))
        // $tid=$fields['tid'];

        // create a client instance
        $client = new Client($config);
        // get a select query instance
        $query = $client->createSelect();
        $qstring = '';
        // set a query (all prices starting from 12)

        if (!empty($fields['title'])) {
            $qstring .= ' all_title:"' . $fields['title'] . '"';
        }
        if (!empty($fields['description'])) {
            if (!empty($fields['title'])) {
                $qstring .= ' AND all_description:"' . $fields['description'] . '"';
            } else {
                $qstring .= 'all_description:"' . $fields['description'] . '"';
            }
        }

        if (!empty($fields['keywords'])) {
            if (!empty($fields['description']) || !empty($fields['title'])) {
                $qstring .= ' AND all_keywords:"' . $fields['keywords'] . '"';
            } else {
                $qstring .= ' all_keywords:"' . $fields['keywords'] . '"';
            }
        }

        $category = '';
        if (!empty($fields['category'])) {
            foreach ($fields['category'] as $each) {
                $category .= '"' . $each . '"' . ' OR ';
            }
            $category = rtrim($category, ' OR ');
            if (!empty($fields['description']) || !empty($fields['title']) || !empty($fields['keywords'])) {
                $qstring .= ' AND category:(' . $category . ') ';
            } else {
                $qstring .= ' category:(' . $category . ') ';
            }
        }

        $format = '';
        if (!empty($fields['format'])) {
            foreach ($fields['format'] as $each) {
                $format .= '"' . $each . '"' . ' OR ';
            }
            $format = rtrim($format, ' OR ');
            if (!empty($fields['description']) || !empty($fields['title']) || !empty($fields['keywords']) || !empty($fields['category'])) {
                $qstring .= ' AND doc_type:(' . $format . ') ';
            } else {
                $qstring .= ' doc_type:(' . $format . ') ';
            }
        }

        if (!empty($fields['date'])) {
            if ($fields['date'] == 'dates') {
                if (isset($fields['end']) && isset($fields['start']) && $fields['end'] >= $fields['start']) {
                    if (!empty($fields['description']) || !empty($fields['title']) || !empty($fields['keywords']) || !empty($fields['category']) || !empty($fields['format'])) {
                        $qstring .= ' AND ( program_startdate:[ ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['start']))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['end']))) . ' ] OR' .
                            ' packet_publish_date:[' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['start']))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['end']))) . '] OR' .
                            ' quiz_start_date:[' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['start']))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['end']))) . '] OR' .
                            ' event_start_date:[' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['start']))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['end']))) . '] )';
                    } else {
                        $qstring .= ' ( program_startdate:[ ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['start']))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['end']))) . ' ] OR' .
                            ' packet_publish_date:[' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['start']))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['end']))) . '] OR' .
                            ' quiz_start_date:[' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['start']))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['end']))) . '] OR' .
                            ' event_start_date:[' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['start']))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($fields['end']))) . '] )';
                    }
                }
            } elseif ($fields['date'] == 'days') {
                if ($fields['days'] == 15) {
                    $end_date = date('Y-m-d H:i:s', strtotime('-15 days'));
                } elseif ($fields['days'] == 30) {
                    $end_date = date('Y-m-d H:i:s', strtotime('-30 days'));
                } elseif ($fields['days'] == 90) {
                    $end_date = date('Y-m-d H:i:s', strtotime('-90 days'));
                } elseif ($fields['days'] == 180) {
                    $end_date = date('Y-m-d H:i:s', strtotime('-180 days'));
                }

                if (!empty($fields['description']) || !empty($fields['title']) || !empty($fields['keywords']) || !empty($fields['category']) || !empty($fields['format'])) {
                    $qstring .= 'AND ( program_startdate:[ ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($end_date))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', time())) . ' ] OR' .
                        ' packet_publish_date:[' . str_replace('+00:00', 'Z', gmdate('c', strtotime($end_date))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', time())) . '] OR' .
                        ' quiz_start_date:[ ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($end_date))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', time())) . ' ] OR' .
                        ' event_start_date:[ ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($end_date))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', time())) . ' ] )';
                } else {
                    $qstring .= ' program_startdate:[ ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($end_date))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', time())) . ' ] OR' .
                        ' packet_publish_date:[' . str_replace('+00:00', 'Z', gmdate('c', strtotime($end_date))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', time())) . '] OR' .
                        ' quiz_start_date:[ ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($end_date))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', time())) . ' ] OR' .
                        ' event_start_date:[ ' . str_replace('+00:00', 'Z', gmdate('c', strtotime($end_date))) . ' TO ' . str_replace('+00:00', 'Z', gmdate('c', time())) . ' ] ';
                }
            }
            //else
            // {
            //     echo "all";
            // }
        }
        // echo $qstring; /die;
        // var_dump($qstring);
        //echo "<br>";
        // $qstring1 =' program_startdate:[ 2015-06-07T13:36:50Z' .' TO '.'2015-07-29T13:36:50Z ]';
        //echo $qstring1;die;

        $relation = User::getUserSearchableContentIds();
        // echo $relation; die;
        if (!empty($relation)) {
            $query->createFilterQuery('fq')->setQuery($relation);
        } else {
            $qstring .= ' id:(null)';
        }
        $query->setQuery($qstring);
        $query->setResponseWriter('phps');
        $query->setQueryDefaultOperator('OR');
        //$query->setStart($pg)->setRows($perpage);
        $query->setStart($pg)->setRows($perpage);
        $result = $client->select($query);
        $result = iterator_to_array($result, true);
        $result = array_map('iterator_to_array', $result);

        $facetSet = $query->getFacetSet();
        $facetSet->setMinCount(1);
        //$facetSet->setLimit(1);

        $facet = $facetSet->createFacetField('category_facet')->setField('category_facet')->addExclude('p1');
        $facet = $facetSet->createFacetField('format_facet')->setField('format_facet')->addExclude('p1');
        // $facet=$facetSet->createFacetField('program_title_facet')->setField('program_title_facet')->addExclude('p1');


        // //create a facet field instance and set options
        // $facet = $facetSet->createFacetMultiQuery('PublishYear')->addExclude('p1');
        // $facet->createQuery('1981-1990', 'PublishYear:[1981 TO 1990]');
        // $facet->createQuery('1991-2000', 'PublishYear:[1991 TO 2000]');
        // $facet->createQuery('2001-2010', 'PublishYear:[2001 TO 2010]');
        // $facet->createQuery('>2011','PublishYear:[2011 TO *]');

        // $facet = $facetSet->createFacetMultiQuery('Price.PriceAmount')->addExclude('p1');
        // $facet->createQuery('1-100', 'Price.PriceAmount:[1 TO 100]');
        // $facet->createQuery('101-200', 'Price.PriceAmount:[101 TO 200]');
        // $facet->createQuery('201-500', 'Price.PriceAmount:[201 TO 500]');
        // $facet->createQuery('501-1000', 'Price.PriceAmount:[501 TO 1000]');
        // $facet->createQuery('>1001', 'Price.PriceAmount:[1001 TO *]');

        // if($tid)
        // { $sorts=explode("-", $tid);//print_r($sorts);die;
        //     $field=$sorts[0];
        //    $dir = ($sorts[1]=="high") ? $query::SORT_DESC : $query::SORT_ASC;
        //    $query->addSort($field, $dir);
        // }
        // if(!empty($qstring))
        // {
        // if($type =="admin")
        //     $query->createFilterQuery('status')->setQuery("Status".":"."\"no sell\" OR Status:\"sell\"");
        // elseif($type=="portal")
        //     $query->createFilterQuery('statusportal')->setQuery("Status:\"sell\"");
        // }else return;
        // $query->createFilterQuery('availability')->setQuery("ProductAvailability:(20 OR 21)");
        if (count($facets) > 0) {
            $j = 0;
            foreach ($facets as $facetkey => $eachfacetval) {
                ++$j;
                $str = '';
                $i = 0;// echo $eachfacet; //print_r($eachfacetval);
                foreach ($eachfacetval as $facet => $facetval) {
                    if ($i > 0) {
                        $str .= ' OR ';
                    }

                    if (strstr($facetval, '-')) {
                        $key = 'key' . $i . $j;
                        $facetval = str_replace('-', ' TO ', $facetval);
                        $str .= '[' . $facetval . ']';
                    } elseif (strstr($facetval, '>')) {
                        $key = 'keyss' . $i . $j;
                        $facetval = str_replace('>', '', $facetval);
                        $facetval .= ' TO *';
                        $str .= '[' . $facetval . ']';

                        //$query->createFilterQuery('price')->setQuery($facetkey.":[".$facetval.']')->addTag('p1');
                    } else {
                        $key = "$facetval" . $i . $j;
                        $str .= '"' . $facetval . '"';
                        //$query->createFilterQuery('ss')->setQuery($facetkey.":".$facetval)->addTag('p1');
                    }
                    ++$i;
                }
                // echo $key,$facetkey,$str;die;
                $query->createFilterQuery($key)->setQuery($facetkey . ':(' . $str . ')')->addTag('p1');
            }
            //$query->createFilterQuery('price')->setQuery('Price.PriceAmount:([201 TO 500] OR  [501 TO 1000])')->addTag('p1');
        }
        $hl = $query->getHighlighting();
        $hl->setFields('all_title');
        $hl->setSimplePrefix('<b>');
        $hl->setSimplePostfix('</b>');
        $result = $client->select($query);
        $highlighting = $result->getHighlighting();
        $high = iterator_to_array($highlighting, true);

        $high = array_map('iterator_to_array', $high);
        // echo "<pre>";print_r($high);
        $cnt = $result->getNumFound();
        // display facet counts
        //echo '<hr/>Facet counts for field "inStock":<br/>';
        $facet = $result->getFacetSet()->getFacet('category_facet');
        $facet = iterator_to_array($facet, true);
        $facets['category'] = $facet;
        $facet = $result->getFacetSet()->getFacet('format_facet');
        $facet = iterator_to_array($facet, true);
        $facets['format'] = $facet;
        // $facet = $result->getFacetSet('program_title_facet')->getFacet('program_title_facet');
        //  $facet=iterator_to_array($facet,true);
        //  $facets['programs']=$facet;

        $result = iterator_to_array($result, true);
        $result = array_map('iterator_to_array', $result);
        $results['results'] = $result;

        $results['cnt'] = $cnt;
        $results['facets'] = $facets;
        $results['pg'] = $pg;
        $results['per_page'] = $perpage;

        return $results;
    }

    public static function getSearch($search_log, $search_count = 0, $result_format = 0)
    {
        $channel = $post = $document = $image = $video = $event = $audio = $quiz = 0;
        if (isset($result_format['channel'])) {
            $channel = $result_format['channel'];
        }
        if (isset($result_format['post'])) {
            $post = $result_format['post'];
        }
        if (isset($result_format['document'])) {
            $document = $result_format['document'];
        }
        if (isset($result_format['audio'])) {
            $audio = $result_format['audio'];
        }
        if (isset($result_format['quiz'])) {
            $quiz = $result_format['quiz'];
        }
        if (isset($result_format['image'])) {
            $image = $result_format['image'];
        }
        if (isset($result_format['video'])) {
            $video = $result_format['video'];
        }
        if (isset($result_format['event'])) {
            $event = $result_format['event'];
        }
        $email = '';
        $ip = $_SERVER['REMOTE_ADDR'];
        if (Auth::check()) {
            $email = Auth::user()->email;
            $uid = Auth::user()->uid;
        }
        $date = date('Y-m-d H:i:s');
        $search_type = $search_log['type'];

        if ($search_type == 'simple' && !isset($search_log['tid'])) {
            self::insert([
                'search_term' => $search_log['key'], 'type' => $search_type, 'num_results' => $search_count, 'date' => $date, 'ip_address' => $ip, 'email' => $email, 'uid' => $uid, 'channel' => $channel, 'post' => $post, 'document' => $document, 'image' => $image, 'video' => $video, 'event' => $event, 'quiz' => $quiz, 'audio' => $audio,
            ]);
        } elseif ($search_type == 'advanced_search' && !isset($search_log['tid'])) {
            self::insert([
                'search_type' => $search_type, 'num_results' => $search_count, 'date' => $date, 'ip_address' => $ip, 'email' => $email, 'uid' => $uid, 'channel' => $channel, 'post' => $post, 'document' => $document, 'image' => $image, 'video' => $video, 'event' => $event, 'quiz' => $quiz, 'audio' => $audio,
                'search_term' => [
                    'Keyword' => $search_log['keywords'],
                    'Title' => $search_log['title'],

                ],
            ]);
        }
    }
}
