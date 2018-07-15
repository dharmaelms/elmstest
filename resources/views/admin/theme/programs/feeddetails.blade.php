<style>
	.feedinfo tr td{
		word-break: break-all;
	}
	#akamai_player_wrapper{
		width:100% !important;
	}
</style>
<div class="row">
	<?php use App\Model\Program;
	if(isset($feed) && !empty($feed)): ?>
		<div class="col-md-12">
			<div class="col-md-6">
				<table class="table table-bordered feedinfo" style="table-layout: fixed;csword-wrap: break-word;">
					<tr>
						<th>{{trans('admin/program.title')}}</th>
						<td>{!! $feed['program_title'] !!}</td>
					</tr>
					<!--<tr>
						<th>Slug</th>
						<td>{!! $feed['program_slug'] !!}</td>
					</tr>-->
					<?php if(empty($feed['program_shortname'])) { ?>
					<tr>
						<th>{{trans('admin/program.short_name')}}</th>
						<td>NA</td>
					</tr>
					<?php } else {?>
					<tr>
						<th>{{trans('admin/program.short_name')}}</th>
						<td>{!! $feed['program_shortname'] !!}</td>
					</tr>
					<?php }?>
					<tr>
						<th>{{trans('admin/program.description')}}</th>
						<td>{!! $feed['program_description'] !!}</td>
					</tr>
					<tr>
						<th>{{trans('admin/program.start_date')}}</th>
						<td>{!! $feed['program_startdate'] !!}</td>
					</tr>
					<tr>
						<th>{{trans('admin/program.end_date')}}</th>
						<td>{!! $feed['program_enddate'] !!}</td>
					</tr>
					<tr>
						<th>{{trans('admin/program.review')}}</th>
						<td>{!! $feed['program_review'] !!}</td>
					</tr>
					<tr>
						<th>{{trans('admin/program.rating')}}</th>
						<td>{!! $feed['program_rating'] !!}</td>
					</tr>
					<tr>
						<th>{{trans('admin/program.visibility')}}</th>
						<td>{!! $feed['program_visibility'] !!}</td>
					</tr>
					@if(config("app.ecommerce"))
						<tr>
							<th>{{trans('admin/program.sellability')}}</th>
							<td>{!! $feed['program_sellability'] !!}</td>
						</tr>
					@endif
					<tr>
						<th>{{trans('admin/program.status')}}</th>
						<td>{!! $feed['status'] !!}</td>
					</tr>
					@if(!empty($feed['package_names']))
					<tr>
						<th>{{trans('admin/program.package')}}</th>
						<td>
						<?php
							$name='';
							$i=1;
						?>
						@foreach($feed['package_names'] as $field)
				           <?php echo $name = $i.') '.$field['package_title'].'<br/>';
							$i++;
							?>
						@endforeach
						</td>
					</tr>
					@endif
					<?php if(isset($feed['child_relations']['active_channel_rel']) && !empty($feed['child_relations']['active_channel_rel'])) {?>
					<tr>
						<th>{{trans('admin/program.content_feed')}}s</th>
						<td>
						<?php
						$name='';
						$i=1;
						foreach($feed['child_relations']['active_channel_rel'] as $field) {
            $field =(int) $field;
            $program=Program::getProgramDetailsByID($field);
            echo $name = $i.') '.$program['program_title'].'<br/>';
			$i++;
			}
			?>	
						</td>
					</tr>
					<?php }?>
				</table>
			</div>
			<div class="col-md-6">
				<?php 
					if(isset($feed['program_cover_media']) && $feed['program_cover_media']){
						if(isset($media) && $media['type'] == "image") {?>
							<img src="{{URL::to('/cp/dams/show-media/'.$feed['program_cover_media'])}}" width="100%">
						<?php 
						}
						elseif(isset($media) && $media['type'] == "video" && isset($kaltura) && isset($media['kaltura_details']['id'])){ 
							if(isset($media['kaltura_details']['rootEntryId']) && $media['kaltura_details']['id'] == 1) // These two lines are a fix for a bug in which mongo was returning entry_id as 1. Added by cerlin.
								$media['kaltura_details']['id'] = $media['kaltura_details']['rootEntryId'];
							echo '<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="330" width="400" rel="media:audio" resource="'.$kaltura.$media['kaltura_details']['id'] .'" data="'.$kaltura.$media['kaltura_details']['id'] .'">
							    <param name="allowFullScreen" value="true" />
							    <param name="allowNetworking" value="all" />
							    <param name="allowScriptAccess" value="always" />
							    <param name="bgcolor" value="#000000" />
							    <param name="flashVars" value="" />
							    <param name="movie" value="'.$kaltura.$media['kaltura_details']['id'].'/>
							    <span property="dc:description" content=""></span>
							    <span property="media:title" content="Kaltura Video"></span>
							    <span property="media:type" content="application/x-shockwave-flash"></span> 
							</object>';
						}
						elseif(isset($media['akamai_details'])){
							if(isset($media['akamai_details']['delivery_html5_url'])){ ?>
								<script src="{{URL::to('admin/js/jwplayer/jwplayer.js')}}"></script>
								<script type="text/javascript">jwplayer.key="{{config('app.jwplayer.key')}}";</script>
                                    <div class="wrapper-video1">
                                        <div class="h_iframe1">
	                                        <script type='text/javascript'>     
	                                        var isFlashInstalled = (function(){
	                                        var b=new function(){var n=this;n.c=!1;var a="ShockwaveFlash.ShockwaveFlash",r=[{name:a+".7",version:function(n){return e(n)}},{name:a+".6",version:function(n){var a="6,0,21";try{n.AllowScriptAccess="always",a=e(n)}catch(r){}return a}},{name:a,version:function(n){return e(n)}}],e=function(n){var a=-1;try{a=n.GetVariable("$version")}catch(r){}return a},i=function(n){var a=-1;try{a=new ActiveXObject(n)}catch(r){a={activeXError:!0}}return a};n.b=function(){if(navigator.plugins&&navigator.plugins.length>0){var a="application/x-shockwave-flash",e=navigator.mimeTypes;e&&e[a]&&e[a].enabledPlugin&&e[a].enabledPlugin.description&&(n.c=!0)}else if(-1==navigator.appVersion.indexOf("Mac")&&window.execScript)for(var t=-1,c=0;c<r.length&&-1==t;c++){var o=i(r[c].name);o.activeXError||(n.c=!0)}}()};  
	                                        return b.c;
	                                            })();
	                                        if(isFlashInstalled){
	                                             $('.wrapper-video1').append("<div id='akamai_player'></div>");
	                                                  jwplayer("akamai_player").setup({
								        playlist: [{
								        	sources : [{
							                    file:'{{$media['akamai_details']['delivery_html5_url']}}',
							                    provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.7/AkamaiAdvancedJWStreamProvider.swf',
							                    type: 'hls'
							                    <?php
							                    if(file_exists(config('app.dams_video_thumb_path').$media['unique_name'].".png")) { ?>
								                    ,image: "{{URL::to('/media_image/'.$media['_id']."?raw=yes")}}" 
						                <?php   } ?>

						                		<?php if(isset($media['srt_location']) && file_exists($media['srt_location'])){ ?>
							                		,tracks: [{
											            file: "{{URL::to('/cp/dams/video-srt/'.$media['_id'])}}", 
											            label: "English",
											            kind: "captions",
											            "default": true 
											        }]
										        <?php } ?>
										    }]
								        }],
								        primary: "html5",
								        androidhls: "true",
								        fallback: "true",
								    });
								    jwplayer().onError(function(evt) {
								        console.log(evt.message);
								    });
	                                        }else{
	                                                $('.wrapper-video1').append("<span>{{ trans('admin/program.errormsg_for_installing_flash_player') }} <a id='link1' href='{{ URL::to(config('app.download_flash_player')) }}' >{{ trans('admin/program.click_here') }}</a></span>");
                                                        $("#link1").click(function() {
                                                        $("#link1").attr('target', '_blank');
                                                    return true;
                                                    });

	                                            }
                                    </script>
									</div>
								</div>
								
					<?php	}
							elseif(isset($media['akamai_details']['stream_success_flash'])){ ?>
								<script src="{{URL::to('admin/js/jwplayer/jwplayer.js')}}"></script>
								<script type="text/javascript">jwplayer.key="{{config('app.jwplayer.key')}}";</script>
								<div id="akamai_player"></div>
								<script type="text/javascript">
								    jwplayer("akamai_player").setup({
								        playlist: [{
						                    file:'{{$media['akamai_details']['stream_success_flash']}}',
						                    provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.7/AkamaiAdvancedJWStreamProvider.swf',
						                    type: 'hls'
						                    <?php
						                    if(file_exists(config('app.dams_video_thumb_path').$media['unique_name'].".png")) { ?>
							                    ,image: "{{URL::to('/media_image/'.$media['_id']."?raw=yes")}}" 
					                <?php   } ?>

					                		<?php if(isset($media['srt_location']) && file_exists($media['srt_location'])){ ?>
						                		,tracks: [{
										            file: "{{URL::to('/cp/dams/video-srt/'.$media['_id'])}}", 
										            label: "English",
										            kind: "captions",
										            "default": true 
										        }]
									        <?php } ?>
								        }],
										primary: "flash"
								    });
								    jwplayer().onError(function(evt) {
								        console.log(evt.message);
								    });
								</script>
					<?php	}
							elseif(!isset($media['akamai_details']['code']) || $media['akamai_details']['code'] != 200){ 
								echo "Error in syncing the file. Please contact";
							}
						?>
				<?php
						}
						else{
							echo "File is being proccessed please wait.";	
						}
					}
				?>
			</div>
		</div>
	<?php endif; ?>
</div>