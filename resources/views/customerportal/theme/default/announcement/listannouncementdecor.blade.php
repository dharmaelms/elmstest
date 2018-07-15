

    	<?php 
    		if(isset($announcements)&& !empty($announcements)){ 
    			foreach ($announcements as $key => $value) {
    				?>
            <!-- <div class=''>  -->
            <li>
                        <div class="img-div"><img src="{{ URL::to('portal/theme/default/img/announce/announcementDefault.png') }}" alt="Announcement"></div>
                        <div class="data-div">
                        <strong>{{$value['announcement_title']}}
    			<!-- 	<a href="{{URL::to('announcements/view-announcement/'.$value['announcement_id'])}}" class="list-group-item">
    					<span style="color:green"> -->
    	<?php
    					if(isset($user_id) && $user_id>0){
    						if($user_id>0){
    							if(isset($value['readers'])){
          		    				if(!in_array($user_id, $value['readers']['user'])){
          		    					if(in_array($user_id, $value['readers']['user'])){
          		    						echo 'true';
          		    					}
          		    					echo '<span class="badge badge-roundless badge-success">NEW</span></strong>';
          		    				}
          		    			}else{
          		    				echo '<span class="badge badge-roundless badge-success">NEW</span></strong>';
          		    			}
          		    		}
          		    	}
    	?>
    					</span>
            			</h4>
                  <p class="list-group-item-text">
                  <?php
                    $content=strip_tags($value['announcement_content'], '<br>');
                    $content=trim($content);
                    if(strlen($content) > $max_char_disp){ 
                      $disp_cont=substr($content,0,$max_char_disp).'.....';
                    }else{
                      $disp_cont=$content;
                    }
                  ?>
                    {!! $disp_cont !!}
            			</p>
                  <div style="align:right">
                    
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
                  </div>
        	<?php
    			}
    		}
    	?>
