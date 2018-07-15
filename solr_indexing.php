<?php
set_time_limit(0);
ini_set("display_errors", 1);
//include_once('dbconfig.php');
//include_once('search_config.php');
//include_once('config.php');
//require 'cron_lock.php';
$m = new Mongo("mongodb://localhost");
$db = $m->selectDB('ultron');
/**
 -- Index Programs/Packet/Element
 -- Index Media
 -- Index assessment
 --  
 -- Q&A will be part of packet 
 -- Packet  will be unit of search results for Programs of type feed 
   
 **/

 	 $last_indexed_at='';
 	 //$last_indexed_at=LastIndexedAt($db);  
     //IndexingStartedOn($db);
 	 Programs_Index($db,$id='',$last_indexed_at);
 	 Media_Index($db,$id='',$last_indexed_at);
     Assessment_Index($db,$id='',$last_indexed_at);
     Events_Index($db,$id='',$last_indexed_at);
     Packet_Index($db,$id='');
     Announcement_Index($db,$id='');
     //IndexingCompletedOn($db);

function LastIndexedAt($db)
{
	$collections = new MongoCollection($db, 'search_indexing_log');
	$cursor = $collections->find()->sort(array('id' => -1))->limit(1);
	$last_indexed_at='';
	foreach($cursor as $each)
	{
		$last_indexed_at=$each['indexing_started_on'];
	}
	return $last_indexed_at;
}

/**
-- Index all  programs or by the given program id

**/
function Programs_Index($db,$id='',$last_indexed_at){
	// echo "last_indexed_at=".$last_indexed_at."<br>"; 
	//  echo "created_at=". gmdate('Y-m-d H:i:s',1430373405)."<pre>";
	//  	 echo "updated_at=". gmdate('Y-m-d H:i:s',1431339527)."<pre>";
	//  	 echo "last_indexed_at=". gmdate('Y-m-d H:i:s',$last_indexed_at)."<pre>";

	$collections = new MongoCollection($db, 'program');

	$filter=array();
	if($id)
		$filter=array('program_id'=>$id);
	if($last_indexed_at=='')
	{
		$cursor = $collections->find(array(),array('program_id','program_title','program_description','program_slug',
		                               'program_startdate','program_enddate','program_type','program_cover_media'
		                               ,'program_cover_video','program_cover_banner','status',
		                                'program_keywords','program_categories'));
	}
	else
	{
		echo 'program indexing'; die;
		echo  $last_indexed_at;

		$cursor = $collections->find(array('$or'=>array(array('created_at'=>array('$gte'=>$last_indexed_at)),array('updated_at'=>array('$gte'=>$last_indexed_at)))),array('program_id','program_title','program_description','program_slug',
		                               'program_startdate','program_enddate','program_type','program_cover_media'
		                               ,'program_cover_video','program_cover_banner','status',
		                                'program_keywords','program_categories'));
		// foreach($cursor as $each)
		// {
		// 	echo "<pre>";print_r($each);
		// }
	}
		

	$i=0;
	$records='';
	foreach($cursor as $data)
	{   
	    $categories=$data['program_categories']; 
	    unset($data['program_categories']);
        $records[]=$data;
    
        $records[$i]['id']="program_".$data['program_id']; 
        // $records[$i]['doc_type']=$data['program_type'];
        $records[$i]['doc_type']="channel"; 
         //remove cATEGORY IDS AND ADD CATEGORY NAME TO INDEX
        if(isset($records[$i]['program_startdate'])){
		    $records[$i]['program_startdate'] =date("Y-m-d", $records[$i]['program_startdate'])."T00:00:01Z";
		    $records[$i]['program_enddate'] =date("Y-m-d", $records[$i]['program_enddate'])."T00:00:01Z";
	    }

		$collections_category = new MongoCollection($db, 'categories');
	    $cursor_cat = $collections_category->find(array('category_id' => array('$in'=>$categories),'status' =>"ACTIVE" ),array('category_name'));
	    foreach($cursor_cat as $cat_data)
		{   
			$records[$i]['category'][] = $cat_data['category_name'];
	    }   

		      
		$i++;
	}

	// echo "<pre>";print_r($records);die;
	$jrecs=json_encode($records);

	// print_r($jsub);
	curl_post($jrecs);
}

function Media_index($db,$id='',$last_indexed_at){ 

	echo "last_indexed_at=".$last_indexed_at."<br>"; 
	 // echo "created_at=". gmdate('Y-m-d H:i:s',1430373405)."<pre>";
	 // 	 echo "updated_at=". gmdate('Y-m-d H:i:s',1431339527)."<pre>";
	echo "last_indexed_at=". gmdate('Y-m-d H:i:s',$last_indexed_at)."<pre>";

	$collections = new MongoCollection($db, 'dams');
	$filter=array();$records=array();
	if($id)
		$filter=array('unique_name'=>$uname);
	if($last_indexed_at=='')
	{
		$media_cursor=$collections->find($filter,array('id','name','unique_name','type','asset_type',
		                               'description','short_description'
		                               ,'tags','visibility','created_at','public_file_location'));
	}
	else
	{
		$media_cursor=$collections->find(array('$or'=>array(array('created_at'=>array('$gte'=>$last_indexed_at)),array('updated_at'=>array('$gte'=>$last_indexed_at)))),array('id','name','unique_name','type','asset_type',
		                               'description','short_description'
		                               ,'tags','visibility','created_at','public_file_location'));
	}
		// foreach($media_cursor as $each)
		// {
		// 	echo "<pre>";print_r($each);
		// 	echo 'media_created_at='.gmdate('Y-m-d H:i:s',$each['created_at']);
		// }
		
	$i=0;
	$records='';
	
	foreach($media_cursor as $data)
	{
      
        $created_at =date("Y-m-d", $data['created_at'])."T00:00:01Z";
       unset($data['created_at']);
       $records[]=$data;
       // $records[$i]['id']="media_".$data['unique_name'];
       $records[$i]['id']="media_".$data['id'];
       $records[$i]['media_id']=$data['id'];
       $records[$i]['doc_type']=$data['type'];
       unset($data['type']);
       $records[$i]['type']="media";
       $i++;
    }	

    ///print_r($records);die;
$jrecs=json_encode($records);
//print_r($jrecs);die;
// print_r($jsub);
curl_post($jrecs);	

}

function Assessment_Index($db,$id='',$last_indexed_at){
	$collections = new MongoCollection($db, 'quizzes');
	$filter=array();
	if($id)
		$filter=array('quiz_id'=>$id);
	if($last_indexed_at=='')
	{
		$assessment_cursor=$collections->find($filter,array('quiz_id','quiz_name','quiz_description',
		'keywords','status','created_at','start_time'));
	}
	else
	{
		$assessment_cursor=$collections->find(array('$or'=>array(array('created_at'=>array('$gte'=>$last_indexed_at)),array('updated_at'=>array('$gte'=>$last_indexed_at)))),array('quiz_id','quiz_name','quiz_description',
		'keywords','status','created_at','start_time'));
	}
	// foreach($assessment_cursor as $each)
	// 	{
	// 		echo "<pre>";print_r($each);
	// 		echo 'media_created_at='.gmdate('Y-m-d H:i:s',$each['created_at']);
	// 	}

	$i=0;
	$records='';
	
	foreach($assessment_cursor as $data)
	{   //print_r($data);die;
        $created_date =date("Y-m-d", $data['created_at'])."T00:00:01Z";
        $quiz_start_date =date("Y-m-d", $data['start_time'])."T00:00:01Z";
        unset($data['created_at']);unset($data['_id']);unset($data['start_time']);
        $records[]=$data;
        $records[$i]['quiz_id'] = $data['quiz_id'];
        $records[$i]['created_at']=$created_date;
        $records[$i]['quiz_start_date']=$quiz_start_date;
        $records[$i]['id']="quiz_".$data['quiz_id'];
         $records[$i]['doc_type']="quiz";
        $i++;
    }	

 // echo "<pre>";print_r($records); die;
$jrecs=json_encode($records);
//print_r($jrecs);die;
// print_r($jsub);
curl_post($jrecs);	

}

function Events_Index($db,$id='',$last_indexed_at){
	$collections = new MongoCollection($db, 'events');
	$filter=array();
	if($id)
		$filter=array('event_id'=>$id);
	if($last_indexed_at=='')
	{
		$assessment_cursor=$collections->find($filter,array('event_id','event_name','event_description',
		'keywords','status','created_at','start_time'));
	}
	else
	{
		$assessment_cursor=$collections->find(array('$or'=>array(array('created_at'=>array('$gte'=>$last_indexed_at)),array('updated_at'=>array('$gte'=>$last_indexed_at)))),array('event_id','event_name','event_description',
		'keywords','status','created_at','start_time'));
	}
	
	$i=0;
	$records='';
	
	foreach($assessment_cursor as $data)
	{   //print_r($data);die;
        $created_date =date("Y-m-d", $data['created_at'])."T00:00:01Z";
        $event_start_date =date("Y-m-d", $data['start_time'])."T00:00:01Z";

        unset($data['created_at']);unset($data['_id']);unset($data['start_time']);
        $records[]=$data;
        $records[$i]['event_id']= $data['event_id'];
        $records[$i]['created_at']=$created_date;
        $records[$i]['event_start_date']=$event_start_date;
        $records[$i]['id']="event_".$data['event_id'];
         $records[$i]['doc_type']="event";
        $i++;
    }	

 //print_r($records);
$jrecs=json_encode($records);
//print_r($jrecs);die;
// print_r($jsub);
curl_post($jrecs);	

}


/* Packets and Q&A Indexing */
function Packet_Index($db,$packet_id='')
{
	$collections = new MongoCollection($db, 'packets');
	$filter=array();
	if($packet_id)
	   $filter=array('packet_id'=>$packet_id);
		$packet_cursor=$collections->find($filter,array('packet_id','packet_title','packet_slug','feed_slug','packet_publish_date','packet_description','packet_cover_media','created_at'));
	
	$i=0;
	$records='';
	
	foreach($packet_cursor as $data)
	{   
        $created_date =date("Y-m-d", $data['created_at'])."T00:00:01Z";
        $packet_publish_date =date("Y-m-d", $data['packet_publish_date'])."T00:00:01Z";
        unset($data['created_at']);unset($data['_id']);
        $records[]=$data;
        $records[$i]['created_at']=$created_date;
        $records[$i]['packet_id'] = $data['packet_id'];
        $records[$i]['id']="packet_".$data['packet_id'];
        $records[$i]['packet_title']=$data['packet_title'];
        $records[$i]['packet_slug']=$data['packet_slug'];
        $records[$i]['packet_publish_date']= $packet_publish_date;
        $records[$i]['feed_slug']=$data['feed_slug'];
        $records[$i]['packet_description']=$data['packet_description'];
        // $records[$i]['doc_type']="packet";
        $records[$i]['doc_type']="post";

        $collections_packets_faq = new MongoCollection($db, 'packets_faq');
        $collections_packets_faq_ans = new MongoCollection($db, 'packets_faq_ans');

		$packets_faq_cursor = $collections_packets_faq->find(array('packet_id' =>$data['packet_id']),array('question','id') );
				    
	    foreach($packets_faq_cursor as $question_data)
		{    
			$records[$i]['question'][] = $question_data['question'];
			//$records[$i]['packet_description'][] = $package_data['packet_description'];

			$packets_faq_ans_cursor = $collections_packets_faq_ans->find(array('ques_id' =>(int)$question_data['id']),array('answer'));
			foreach($packets_faq_ans_cursor as $answer_data)
			{
				$records[$i]['answer'][] = $answer_data['answer'];
			}
		}
        $i++;
    }	
	// echo "<pre>"; print_r($records); 
    $jrecs=json_encode($records);
    curl_post($jrecs);	


}

/* Announcement Indexing */
function Announcement_Index($db,$announcement_id='')
{
	$collections = new MongoCollection($db, 'announcements');
	$filter=array();
	if($announcement_id)
	   $filter=array('announcement_id'=>$announcement_id);
		$announcement_cursor=$collections->find($filter,array('announcement_id','announcement_title','announcement_type','announcement_content','created_at','schedule','created_by_name',));
	
	$i=0;
	$records='';
	
	foreach($announcement_cursor as $data)
	{   
        $created_date =date("Y-m-d", $data['created_at'])."T00:00:01Z";
        $announcement_scheduled_date =date("Y-m-d", $data['schedule'])."T00:00:01Z";
        unset($data['created_at']);unset($data['_id']);unset($data['schedule']);
        $creater='';
        if(isset($data['created_by_name'])){$creater=$data['created_by_name'];}
        unset($data['created_by_name']);
        $records[]=$data;
        $records[$i]['created_at']=$created_date;
        $records[$i]['announcement_id'] = $data['announcement_id'];
        $records[$i]['id']="announcement_".$data['announcement_id'];
        $records[$i]['announcement_title']=$data['announcement_title'];
        $records[$i]['announcement_content']=$data['announcement_content'];
        $records[$i]['announcement_scheduled_date']= $announcement_scheduled_date;
        $records[$i]['announcement_type']=$data['announcement_type'];
        //$records[$i]['announcement_for']=$data['announcement_for'];
        $records[$i]['announcement_creater_name']=$creater;
        // $records[$i]['doc_type']="packet";
        $records[$i]['doc_type']="announcement";

        $i++;
    }	
	// echo "<pre>"; print_r($records); 
    $jrecs=json_encode($records);
    curl_post($jrecs);	


}

function IndexingStartedOn($db)
{
	echo 'Search Logging Starts:'.date('Y-m-d H:i:s')."<br>";
		echo 'converted unix time='. gmdate('Y-m-d H:i:s',time())."<br>"; 
		echo  gmdate("Y-m-d H:i:s", time())."<br>";
		echo time()."<br>";

// echo '[ '.str_replace('+00:00', 'Z', gmdate('c', time())).']'; die;
	$collections = new MongoCollection($db, 'search_indexing_log');

	$cursor = $collections->find()->sort(array('id' => -1))->limit(1);
	$last_indexed_id=0;
	foreach($cursor as $each)
	{
		$last_indexed_id=$each['id'];
	}
	echo $last_indexed_id;
	echo $last_indexed_id=$last_indexed_id+1;
	$collections->insert(array(
		'id'=>(int)$last_indexed_id,
		'indexing_started_on'=>time()
		));

}
function IndexingCompletedOn($db)
{
	$collections = new MongoCollection($db, 'search_indexing_log');

	$cursor = $collections->find()->sort(array('id' => -1))->limit(1);
	$last_indexed_id='';
	foreach($cursor as $each)
	{
		$last_indexed_id=$each['id'];
	}
	echo $last_indexed_id; 
	$data=array('$set'=>array('indexing_completed_on'=>time()));
	$collections->update(array('id'=>(int)$last_indexed_id),$data); 
}

function curl_post($jrecs){
	$ch = curl_init();   
	curl_setopt($ch, CURLOPT_URL, "http://localhost:8983/solr/Test/update?commit=true");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jrecs);
	$response = curl_exec($ch);
	if(curl_errno($ch)){   
    echo 'Curl error: ' . curl_error($ch);
}
	echo "<pre>";print_r($response);echo "dsfdsfdsf4324324";//die;
}
