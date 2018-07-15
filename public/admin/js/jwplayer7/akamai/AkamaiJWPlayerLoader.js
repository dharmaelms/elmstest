function AkamaiJWPlugin(jwPlayer)
{
	var VERSION = "1.0.1";
	var akaPlugin;
	var isPlayStarted = false;
	var pluginObj = this;
	var isSessionInitiated = false;
	var isFQ = false;var isMP = false;var isTQ = false;
    this.loadMediaAnalytics = function()
    {
        try
        {
			createLibraryInstance();
			
			jwPlayer().onBeforePlay(function(e){
				if(!isSessionInitiated){
					setCustomData();
					akaPlugin.handleSessionInit();//Must be called only when initiating a new play.
					isSessionInitiated = true;
					isPlayStarted = false;
				}
            });
			
            jwPlayer().onPlay(function(){
				if(!isPlayStarted){
					pluginObj.setBitrateIndex(jwPlayer().getCurrentQuality());
				}
				isPlayStarted = true;
				akaPlugin.handlePlaying();
            });
            jwPlayer().onPause(function(){
                akaPlugin.handlePause();
            });
            jwPlayer().onBuffer(function(){
				akaPlugin.handleBufferStart();
            });
            jwPlayer().onComplete(function(){
				akaPlugin.handlePlayEnd("JWPlayer.Complete");
				isSessionInitiated = false;
				isPlayStarted = false;
            });
            jwPlayer().onError(function(e){
                akaPlugin.handleError("JWPlayer.Error:"+e.message);
				isSessionInitiated = false;
				isPlayStarted = false;
            });
			jwPlayer().onSetupError(function(e){
                akaPlugin.handleError("JWPlayer.SetupError:"+e.message);
				isSessionInitiated = false;
				isPlayStarted = false;
            });
			
			jwPlayer().onQualityChange(function(e){
				pluginObj.setBitrateIndex(e.currentQuality);
            });
			

			jwPlayer().onAdImpression(function(e){//JWPlayer provides only one event when Ad starts.
				isFQ = false;isMP = false;isTQ = false;
				akaPlugin.handleAdLoaded({adTitle:e.tag});//Need to send more Ad related custom dimensions.
				akaPlugin.handleAdStarted();
            });

			jwPlayer().onAdTime(function(e){
				try{
					if(e.duration > 0){
						var adPlayPercent = e.position / e.duration;
						if(!isFQ && adPlayPercent >= 0.25 && adPlayPercent < 0.5){
							akaPlugin.handleAdFirstQuartile();
							isFQ = true;
						}else if(!isMP && adPlayPercent >= 0.5 && adPlayPercent < 0.75){
							akaPlugin.handleAdMidPoint();
							isMP = true;
						}else if(!isTQ && adPlayPercent >= 0.75){
							akaPlugin.handleAdThirdQuartile();
							isTQ = true;
						}
					}
	    		}catch(e){}
			});
			
			jwPlayer().onAdComplete(function(e){
				akaPlugin.handleAdComplete();
            });
			jwPlayer().onAdError(function(e){
				akaPlugin.handleAdError();
            });
        }
        catch(e){
        }
    }
	
	this.setData = function(name, value){
		if(akaPlugin){
			akaPlugin.setData(name, value);
		}
	}
	
	this.setBitrateIndex = function(bitrateIndex){
		//console.log("setBitrateIndex:"+bitrateIndex);
		try{
		var qualityObj = jwplayer().getQualityLevels()[bitrateIndex];
		var bitrate = parseInt(qualityObj.bitrate);
		if(bitrate < 50000){
			bitrate = bitrate*1000;//Converting kbps to bps
		}
		if(isNaN(bitrate) || !(bitrate>0)){
			if(qualityObj.label && qualityObj.label.toLowerCase().indexOf("kbps") > 0){
				bitrate = parseInt(qualityObj.label)*1000;
			}
		}
		if(bitrate > 0){
			this.setBitrate(bitrate);
		}
		}catch(e){}
	}
	
	//Set bitrate in bps
	this.setBitrate = function(bitrate){
		if(akaPlugin){
			//console.log("setBitrate:"+bitrate);
			akaPlugin.handleBitRateSwitch(bitrate);
		}
	}
	
	function createLibraryInstance(){
		var akaPluginCallBack = {};
		akaPluginCallBack["streamHeadPosition"] = getStreamHeadPosition;
		akaPluginCallBack["streamLength"] = getStreamLength;
		akaPluginCallBack["streamURL"] = getStreamURL;
		akaPlugin = new AkaHTML5MediaAnalytics(akaPluginCallBack);
		akaPlugin.setData("std:playerType", jwPlayer().getRenderingMode()+"-html5");//Setting playerType for debugging purposes.
	}
    
    function getStreamHeadPosition()
    {
        return jwPlayer().getPosition();
    } 
	function getStreamLength(){
		return jwPlayer().getDuration();
	}
	function getStreamURL(){
		var itemIndex = jwPlayer().getPlaylistIndex();
		var item = jwPlayer().getPlaylistItem(itemIndex);
		return item.file;
	}
	function setCustomData(){
		try{
			if(jwPlayer().getPlaylist() && jwPlayer().getPlaylistIndex() > -1){
				var playItem = jwPlayer().getPlaylist()[jwPlayer().getPlaylistIndex()];
				akaPlugin.setData("title", playItem.title);
				var sources = playItem.sources;
				if(sources && sources.length > -1){
					akaPlugin.setData("std:format", sources[0].type);
				}
			}
		}catch(e){
			console.log(e);
		}
	}
	this.loadMediaAnalytics();
}
