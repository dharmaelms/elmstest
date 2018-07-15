@section('content')

<style type="text/css">
#notifi_div_tab{
	float: right;
	}
#announce_div_tab{
	float: right;
}
#content_list_announce{
	clear: both;
}
#content_list_announce {
	display: display;
}
#content_list_notification{
	display: none;
	width: 70%;
	float: right;
}
.Announcement_btn{
	color: red;
}
.not_read{
	color: green;
}
</style>
<div>
	<div id='notifi_div_tab'>
		<button class="Notifications_btn">
			Notifications		
		</button>
	</div>
	<div id='announce_div_tab'>
		<button class="Announcement_btn" >
			Announcements
		</button>
	</div>
</div>
<div id="content_list_announce">
<h1> List Announcements </h1>
<?php 
	if(isset($announcements))
	{
		if(!empty($announcements)){
			?>

					<div class="panel-body">
					<table border="0">
        <?php
        if(isset($spec_user_annonce) && !empty($spec_user_annonce)){
            foreach ($spec_user_annonce as $key => $value) {
              					?>
				<tr style="font-size:18px">
				<!-- <li id="basic-modal" class="xs2-margin"> -->
                    <td class="not_read">
          	    	  <?php
          		    	if($user_id>0){
          		    		if(isset($value['readers'])){
          		    			if(!in_array($user_id, $value['readers']['user'])){
          		    				if(in_array(9, $value['readers'],true)){
          		    					echo "true";
          		    				}
          		    				echo "New";
          		    			}
          		    		}else{
          		    			echo "New";
          		    		}
          		   		}
          	     		?>
                    </td>
          		<td style="width:20px" ></td>
              	<td >	<div >
              			<a href="{{URL::to('announcements/view-announcement/'.$value['announcement_id'])}}">
              				{{$value['announcement_title']}}
              			</a>
              		</div>
              		<div>
                  <?php
                    $content=strip_tags($value['announcement_content'], '<br><br/>');
                    $content=trim($content);
                    if(strlen($content) > $max_char_disp){ 
                      $disp_cont=substr($content,0,$max_char_disp).'.....';
                    }else{
                      $disp_cont=$content;
                    }
                  ?>
                   		<span style="color: black;"><?php print_r($disp_cont)?></span>
                	</div>
                  </td>
                   <td style="width:20px"></td>
                  <td>
                  <?php 
                  	if (array_key_exists("relations",$value))
  					{
  						foreach ($value['relations'] as $relation_name  => $relazn) {
  							if(is_array($relazn) && !empty($relazn) && $relation_name=='active_media_announcement_rel'){
  							
                  ?>
                  		<div>
                    		<img width='150px' src="{{URL::to('media_image/'.$relazn[0].'?id=id')}}" alt="Image loading...">
                  		</div>
                  		<?php
                  		 }
                  		}
                  	}
                  		 ?>
                  </td>
               </tr>
               <tr style="height:20px"></tr>
<?php 
              				}
              			}
              		?>	
			<?php
		
			foreach ($announcements as $key => $value) {
?>
				 <tr style="font-size:18px">
				<!-- <li id="basic-modal" class="xs2-margin"> -->
          <td class="not_read">
          	<?php
          		if(isset($user_id)){
          		    	if($user_id>0){
          		    		if(isset($value['readers'])){
          		    			if(!in_array($user_id, $value['readers']['user'])){
          		    				if(in_array($user_id, $value['readers']['user'])){
          		    					echo "";
          		    				}
          		    				echo "New";
          		    			}
          		    		}else{
          		    			echo "New";
          		    		}
          		   		}
          		   	}
          	?>
          </td>
          <td style="width:20px" ></td>
              	<td >	<div >
              			<a href="{{URL::to('announcements/view-announcement/'.$value['announcement_id'])}}">
              				{{$value['announcement_title']}}
              			</a>
              		</div>
              		<div>
                  <?php
                    $content=strip_tags($value['announcement_content'], '<br><br/>');
                    $content=trim($content);
                    if(strlen($content) > $max_char_disp){ 
                      $disp_cont=substr($content,0,$max_char_disp).'.....';
                    }else{
                      $disp_cont=$content;
                    }
                  ?>
                   		<span style="color: black;"><?php print_r($disp_cont)?></span>
                	</div>
                  </td>
                   <td style="width:20px"></td>
                  <td>
                  <?php 
                  	if (array_key_exists("relations",$value))
  					{
  						foreach ($value['relations'] as $relation_name  => $relazn) {
  							if(is_array($relazn) && !empty($relazn) && $relation_name=='active_media_announcement_rel'){
  							
                  ?>
                  		<div>
                    		<img width='150px' src="{{URL::to('media_image/'.$relazn[0].'?id=id')}}" alt="Image loading...">
                  		</div>
                  <?php
                  		 }
                  		}
                  	}
                  ?>
                  </td>
               </tr>
               <tr style="height:20px"></tr>
<?php 
		}
		?></table>
        	</div>
		<?php
	}
}
?>
</div>
<div id='content_list_notification'>
	<span>  Permission Denied Need to Login ......</span>
</div>
<div id="test">
	
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('.Announcement_btn').click(function(){
			$('#content_list_announce').show();
			$('#content_list_notification').hide();
			$(this).css('color','red');
			$('.Notifications_btn').css('color','black');
		});
		$('.Notifications_btn').click(function(){
			$('#content_list_announce').hide();
			$('#content_list_notification').show();
			$(this).css('color','red');
			$('.Announcement_btn').css('color','black');
			<?php 
				if($user_id>0){ 
					$url=URL::to('notification'); 
					?>
					<?php
				}
			?>
		});
		$('.not_read').each(function(){
			// $(this).parent
		});
	});
</script>
@stop