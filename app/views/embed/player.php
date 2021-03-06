<div class="heading-container clearfix">
<?php if ($hasVideo && $showHeading): ?>
	<div class="logo-container">
		<a href="<?=e($hyperlink);?>" target="_blank"><img class="img-responsive" src="<?=asset("assets/img/logo.png");?>"/></a>
	</div>
	<div class="title-container">
		<h1 class="episode-title"><a href="<?=e($hyperlink);?>" target="_blank"><?=e($episodeTitle);?></a></h1>
	</div>
<?php endif; ?>
</div>
<?php if ($hasVideo): ?>
<div class="player-container-component-container" data-site-uri="<?=e($hyperlink);?>" data-disable-redirect="<?=$disableRedirect?"1":"0"?>" data-found-media-item="1" data-info-uri="<?=e($playerInfoUri);?>" data-recommendations-uri="<?=e($recommendationsUri);?>" data-register-watching-uri="<?=e($registerWatchingUri);?>" data-login-required-msg="<?=e($loginRequiredMsg);?>" data-enable-admin-override="<?=$adminOverrideEnabled?"1":"0"?>" data-register-like-uri="<?=e($registerLikeUri);?>" data-ignore-external-stream-url="<?=$ignoreExternalStreamUrl?"1":"0"?>" data-hide-bottom-bar="<?=$hideBottomBar?"1":"0"?>" data-auto-play-vod="<?=$autoPlayVod?"1":"0"?>" data-auto-play-stream="<?=$autoPlayStream?"1":"0"?>" data-vod-play-start-time="<?=$vodPlayStartTime?>" data-disable-full-screen="<?=$disableFullScreen?"1":"0"?>" data-show-title-in-player="<?=$showTitleInPlayer?"1":"0"?>" data-disable-player-controls="<?=$disablePlayerControls?"1":"0"?>" data-enable-smart-auto-play="<?=$enableSmartAutoPlay?"1":"0"?>" data-initial-vod-quality-id="<?=$initialVodQualityId?>" data-initial-stream-quality-id="<?=$initialStreamQualityId?>">
<?php else: ?>
<div class="player-container-component-container" data-site-uri="<?=e($hyperlink);?>" data-disable-redirect="<?=$disableRedirect?"1":"0"?>" data-found-media-item="0">
<?php endif; ?>
	<div class="msg-container embed-responsive">
		<div class="embed-responsive-item">
			<?php if ($hasVideo): ?>
			<div class="msg msg-loading delay-show">Loading<br /><img src="<?=asset("assets/img/loading.gif");?>"></div>
			<?php else: ?>
			<div class="msg msg-unavailable">Sorry this content is currently unavailable.<br /><a href="<?=e($hyperlink);?>" target="_blank">Click here to go to the LA1:TV website.</a></div>
			<?php endif; ?>
		</div>
	</div>
</div>