<div class="row">
    <?php if (isset($media) && !empty($media)) : ?>
        <div class="col-md-12">
            <?php switch ($media->type) {
                case "video" : {
                    ?>
                    @include('media.displayvideo', ['media' => $media])
                    <?php
                        break;
                }
                case "image" :{
                    if ($media->asset_type == "file") {
                        ?>
                            <img class="ann_img_sty img-responsive" src="{!! URL::to('/media_image/'.$media->_id) !!}" style="max-height:400px;" alt="Announcement Image" /> 
                        <?php
                    } else { ?>
                                <h3>No Preview found</h3>
                                <h5>Click the below link to visit that link</h5><br />
                                <a href="{{$media->url}}" target="_blank"><button class="btn btn-primary">Go to</button></a>
                            <?php
                    }
                        break;
                }
                case "document" :{
                    if ($media->asset_type == "file") {
                        ?>
                            <h3>No Preview found</h3>
                            <h5>Click the below link to download the document</h5><br />
                            <a href="{{URL::to('/media_image/'.$media->_id)}}"><button class="btn btn-primary">Download</button></a>
                        <?php
                    } else { ?>
                                <h3>No Preview found</h3>
                                <h5>Click the below link to visit that link</h5><br />
                                <a href="{{$media->url}}" target="_blank"><button class="btn btn-primary">Go to</button></a>
                            <?php
                    }
                        break;
                }
                case "audio" :{
                    ?>
                        @include('media.displayaudio', ['media' => $media])
                    <?php
                    break;
                }
			}
        ?>
    	</div>
    <?php endif; ?>
</div>