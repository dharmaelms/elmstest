<?php
use App\Model\SiteSetting;
$enabled = SiteSetting::module('Socialite','enabled');
$facebook = SiteSetting::module('Socialite','facebook');
$google = SiteSetting::module('Socialite','google');
?>

@if($enabled === 'on')
	@if($facebook === 'on')
	    <a class="btn btn-primary" href="{{ route('social.login', ['facebook']) }}">Facebook</a>
	@endif

	@if($google === 'on')
	    <a class="btn btn-primary" href="{{ route('social.login', ['google']) }}">Google</a>
	@endif
@endif
