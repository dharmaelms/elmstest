<div class="row">
	<?php if(isset($asset) && !empty($asset)): ?>
		<div class="col-md-12">
				<?php switch($asset['type']){ 
						case "video" : { 
							if($asset['asset_type'] == "file"){
								if(isset($kaltura) && isset($asset['kaltura_details']['id'])){
									if(isset($asset['kaltura_details']['rootEntryId']) && $asset['kaltura_details']['id'] == 1) // These two lines are a fix for a bug in which mongo was returning entry_id as 1. Added by cerlin.
										$asset['kaltura_details']['id'] = $asset['kaltura_details']['rootEntryId'];
								?>
									<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="225" width="400" rel="media:audio" resource="<?php echo $kaltura ?><?php echo $asset['kaltura_details']['id'] ?>" data="<?php echo $kaltura ?><?php echo $asset['kaltura_details']['id'] ?>">
									    <param name="allowFullScreen" value="true" />
									    <param 	name="allowNetworking" value="all" />
									    <param name="allowScriptAccess" value="always" />
									    <param name="bgcolor" value="#000000" />
									    <param name="flashVars" value="" />
									    <param name="movie" value="<?php echo $kaltura ?><?php echo $asset['kaltura_details']['id'] ?>"/>
									    <span property="dc:description" content=""></span>
									    <span property="media:title" content="Kaltura Video"></span>
									    <span property="media:type" content="application/x-shockwave-flash"></span> 
									</object>
								<?php
								}
								elseif(isset($asset['akamai_details'])){
									if(isset($asset['akamai_details']['delivery_html5_url'])){ 
										$for_id = rand(1000,1000000);
										?>
										<script src="{{URL::to('/admin/js/jwplayer7/jwplayer.js')}}"></script>
    									<script type="text/javascript">
                                            jwplayer.key = '{{ config('app.jwplayer.key') }}';
                                        </script>
										<style>
											#akamai_player{{$for_id}}_wrapper{
												width:100% !important;
											}
										</style>

										 <div class="wrapper-video1">
                                        <div class="h_iframe1">
	                                        <script type='text/javascript'>     
	                                        var isFlashInstalled = (function(){
	                                        var b=new function(){var n=this;n.c=!1;var a="ShockwaveFlash.ShockwaveFlash",r=[{name:a+".7",version:function(n){return e(n)}},{name:a+".6",version:function(n){var a="6,0,21";try{n.AllowScriptAccess="always",a=e(n)}catch(r){}return a}},{name:a,version:function(n){return e(n)}}],e=function(n){var a=-1;try{a=n.GetVariable("$version")}catch(r){}return a},i=function(n){var a=-1;try{a=new ActiveXObject(n)}catch(r){a={activeXError:!0}}return a};n.b=function(){if(navigator.plugins&&navigator.plugins.length>0){var a="application/x-shockwave-flash",e=navigator.mimeTypes;e&&e[a]&&e[a].enabledPlugin&&e[a].enabledPlugin.description&&(n.c=!0)}else if(-1==navigator.appVersion.indexOf("Mac")&&window.execScript)for(var t=-1,c=0;c<r.length&&-1==t;c++){var o=i(r[c].name);o.activeXError||(n.c=!0)}}()};  
	                                        return b.c;
	                                            })();
	                                        if(isFlashInstalled){
	                                             $('.wrapper-video1').append("<div id='akamai_player{{$for_id}}'></div>");
										    jwplayer("akamai_player{{$for_id}}").setup({
										        playlist:[{
										        	sources:[
										        		{
                                                            file:'{{$asset['akamai_details']['delivery_html5_url']}}?{{$token}}',
                                                            'default': 'true'
                                                        },
                                                        {
                                                            file:'{{$asset['akamai_details']['delivery_flash_url']}}?{{$token}}',
                                                            provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.8/AkamaiAdvancedJWStreamProvider.swf'
                                                        }
										        	]
									                @if(file_exists(config('app.dams_video_thumb_path').$asset['unique_name'].".png"))
									                	,image: "{{URL::to('/media_image/'.$asset['_id']."?compress=1")}}"
									                @endif
									                @if(isset($asset['srt_location']) && file_exists($asset['srt_location']))
									                	,tracks: [{
												            file: "{{URL::to('/cp/dams/video-srt/'.$asset['_id'])}}", 
												            label: "English",
												            kind: "captions",
												            'default': 'true'
													    }]
									                @endif
										        }],
												primary: 'html5',
                                                androidhls: 'true',
                                                fallback: 'true',
                                                width: '100%',
                                                aspectratio: '16:9'
										    });
										    jwplayer().onError(function(evt) {
										        console.log(evt.message);
										    });
	                                           
	                                        }else{
	                                                $('.wrapper-video1').append("<span>{{ trans('admin/program.errormsg_for_installing_flash_player') }} <a id='link' href='{{ URL::to(config('app.download_flash_player')) }}' >{{ trans('admin/program.click_here') }}</a></span>");
	                                                $("#link").click(function() {
                                                    $("#link").attr('target', '_blank');
                                                    return true;
                                                    });
	                                            }
										</script>
							<?php	}
									elseif(isset($asset['akamai_details']['stream_success_html5'])){ 
											$for_id = rand(1000,1000000);
										?>
										<script src="{{URL::to('/admin/js/jwplayer7/jwplayer.js')}}"></script>
                                        <script type="text/javascript">
                                            jwplayer.key = '{{ config('app.jwplayer.key') }}';
                                        </script>
										<div id="akamai_player{{$for_id}}" class="sty_width"></div>
										<style>
											#akamai_player{{$for_id}}_wrapper{
												width:100% !important;
											}
										</style>
										<script type="text/javascript">
										    jwplayer("akamai_player{{$for_id}}").setup({
										    	playlist: 
										        [{
										        	sources:[
										        		{
	                                                        file: '{{$asset['akamai_details']['stream_success_html5']}}?{{$token}}',
	                                                        'default': 'true'
	                                                    },
	                                                    {
	                                                        file:'{{$asset['akamai_details']['stream_success_flash']}}?{{$token}}',
	                                                        provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.8/AkamaiAdvancedJWStreamProvider.swf'
	                                                    }
										        	]
									                @if(file_exists(config('app.dams_video_thumb_path').$asset['unique_name'].".png"))
									                	,image: "{{URL::to('/media_image/'.$asset['_id']."?compress=1")}}"
									                @endif
									                @if(isset($asset['srt_location']) && file_exists($asset['srt_location']))
									                	,tracks: [{
												            file: "{{URL::to('/cp/dams/video-srt/'.$asset['_id'])}}", 
												            label: "English",
												            kind: "captions",
												            'default': 'true'
													    }]
									                @endif
										        }],
												primary: 'html5',
                                                androidhls: 'true',
                                                fallback: 'true',
                                                width: '100%',
                                                aspectratio: '16:9'
										    });
										    jwplayer().onError(function(evt) {
										        console.log(evt.message);
										    });
										</script>
							<?php	}
									elseif(!isset($asset['akamai_details']['code']) || $asset['akamai_details']['code'] != 200){ 
										echo "Error in syncing the file. Please contact";
									}
									else{
										echo "File is being proccessed please wait.";	
									}
									//elseif(isset($asset['akamai_details']['code']) && $asset['akamai_details']['code'] == 200 && isset($asset['akamai_details']['stream_success_flash'])){}
								}
								else{
									echo "File is not synced with Video Server";
								}
							}
							else{ ?>
								<h3>{{ trans('admin/announcement.no_preview_found') }}</h3>
								<h5>{{ trans('admin/announcement.click_the_link_to_visit_that_link') }}</h5><br />
								<a href="{{$asset['url']}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/announcement.go_to') }}</button></a>
								<?php 
							}

							break;
						}
						case "image" :{
							if($asset['asset_type'] == "file"){
								?>
									<img class="ann_img_sty img-responsive" src="{!! URL::to('/media_image/'.$asset['_id']) !!}" style="max-height:400px;" /> 
								<?php
							}
							else{ ?>
									<h3>{{ trans('admin/announcement.no_preview_found') }}</h3>
									<h5>{{ trans('admin/announcement.click_the_link_to_visit_that_link') }}</h5><br />
									<a href="{{$asset['url']}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/announcement.go_to') }}</button></a>
								<?php 
							}
							break;
						}
						case "document" :{
							if($asset['asset_type'] == "file"){
								?>
									<h3>{{ trans('admin/announcement.no_preview_found') }}</h3>
									<h5>{{ trans('admin/announcement.click_here_to_download_doc') }}</h5><br />
									<a href="{{URL::to('/media_image/'.$asset['_id'])}}"><button class="btn btn-primary">{{ trans('admin/announcement.download') }}</button></a>
								<?php
							}
							else{ ?>
									<h3>{{ trans('admin/announcement.no_preview_found') }}</h3>
									<h5>{{ trans('admin/announcement.click_the_link_to_visit_that_link') }}</h5><br />
									<a href="{{$asset['url']}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/announcement.go_to') }}</button></a>
								<?php 
							}
							break;
						}
						case "audio" :{
							if($asset['asset_type'] == "file"){
								?>
									<h3>{{ trans('admin/announcement.no_preview_found') }}</h3>
									<h5>{{ trans('admin/announcement.click_the_link_to_download_audio') }}</h5><br />
									<a href="{{URL::to('/media_image/'.$asset['_id'])}}"><button class="btn btn-primary">{{ trans('admin/announcement.download') }}</button></a>
								<?php
							}
							else{ ?>
									<h3>{{ trans('admin/announcement.no_preview_found') }}</h3>
									<h5>{{ trans('admin/announcement.click_the_link_to_visit_that_link') }}</h5><br />
									<a href="{{$asset['url']}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/announcement.go_to') }}</button></a>
								<?php 
							}
							break;
						}
					}
				?>
			</div>
	<?php endif; ?>
</div>