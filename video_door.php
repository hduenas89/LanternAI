<?php
	// Display error information
	ini_set('display_errors', 'on');
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	// Instantiate the DBT SDK
	require_once('includes/config.php');
	require_once('includes/Dbt.php');
	$dbt = new Dbt($apiKey, null, null, 'array');

	// Get the root location of video files
	$rootPath = 'http://video.dbt.io/';
		
	// Get a list of DOOR International volumes
	// The 7th parameter of getLibraryVolume is the version code
	// The version code for DOOR International is "SLS" (Sign Language Stories)
	$volumes = $dbt->getLibraryVolume(null, null, null, null, null, null, 'SLS');
	
	// Get the DAM ID to use from the request.
	$damId = $_REQUEST['damId'];
	if (empty($damId)) {
		// Default to: English
		$damId = 'ASESLSS2DV';
	}

	// Get a list of ASL videos for the New Testament.
	// The DAM ID for New Testament ASL Video is ASESLVN2DV
	$videos = $dbt->getVideoPath($damId);
?>
<html>
	<head>
		<title>DBT DOOR Video Sample</title>
		<!-- These next two lines are required to allow the browser to properly display non-Latin language names -->
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script type="text/javascript" src="/js/jQuery.jPlayer.2.4.0/jquery.jplayer.min.js"></script>
		<link href="/js/blue.monday/jplayer.blue.monday.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">
		//<![CDATA[
		var rootUrl = "<?php echo $rootPath; ?>";

		$(document).ready(function(){

			// Set up jPlayer
			$("#jquery_jplayer_1").jPlayer({
				ready: function () {
				},
				swfPath: "/js/jQuery.jPlayer.2.4.0",
				supplied: "m4v", /* Note that mp4 files are considered m4v by jPlayer */
				size: {
					width: "640px",
					height: "360px",
					cssClass: "jp-video-360p"
				},
				smoothPlayBar: true,
				keyEnabled: true
			});

			// Handle when a user selects a video
			$(".loadVideo").bind('click', function(){
				// Get the URL, which is stored as an attribute on the button
				var url = this.attributes.videopath.value;
				if(url === ''){
					return false;
				}
				url = rootUrl + url;

				// Set the video player to use the selected URL
				$("#jquery_jplayer_1").jPlayer('setMedia', {
					m4v: url
				});

				// Automatically start playing
				$("#jquery_jplayer_1").jPlayer('play');


				// Set the title in the player
				$('.jp-title ul li').html($("#videos option:selected").text());
								
								// Scroll down to the video player, just in case it isn't within the current viewport
								window.scroll(0, $('#jquery_jplayer_1').position().top);
			});
						
			$('#languageSelector').bind('change', function() {
				// Automatically submit the form when the language is changed
				this.form.submit();
			});
			
		});
		//]]>
		</script>

	</head>
	<body>
		<h1>DOOR Video Sample</h1>

		<p>This sample shows how to retrieve a list of the available DOOR video stories for a selected DAM ID, and how to retrieve the 
			location of the relevant video files. There are four video types for each story: Story, Topic, Intro, and More Info. <a href="http://www.jplayer.org/" target="_blank">JPlayer</a>
			is used as the video player.</p>
		<p>Before attempting to run this example, <strong>be sure to place your DBT key in the configuration file </strong>
			(includes/config.php).</p>

		<!-- Begin Selection controls -->
		<h3>Select a Language</h3>
		<form>
			<select id="languageSelector" name="damId">
				<?php
				// Populate the dropdown with a list of all DOOR languages available
				foreach ($volumes as $currentVolume) {
				?>
				<option
					value="<?php echo $currentVolume['dam_id'];?>"
					<?php if ($currentVolume['dam_id'] == $damId) {?> selected="selected"<?php } ?>>
				<?php
				echo $currentVolume['language_name'];
				?>
				</option>
				<?php
				}
				?>
			</select>
		</form>
		<h3>Select a Video</h3>
				<table>
					<?php 
					foreach ($videos as $video) {
						// Populate an array of URLs, keyed by type
						$videoUrls = array('Story' => $video['path']);
						// Intro, Topic, and More Info videos are found within related_videos
						foreach ($video['related_videos'] as $relatedVideo) {
							$videoUrls[$relatedVideo['video_type']] = $relatedVideo['path'];
						}
						?>
						<tr>
							<!-- Base URL for thumbnail images: http://cloud.faithcomesbyhearing.com/segment-art/700X510/ -->
							<td><img height="100" width="140" src="http://cloud.faithcomesbyhearing.com/segment-art/700X510/<?php echo $video['thumbnail_image'];?>" /></td>
							<td>
								<?php
								// Show buttons in order, if they exist: Topic, Intro, Story, and More Info
								if (isset($videoUrls['Topic'])) {
									?>
									<button class="loadVideo" videopath="<?php echo $videoUrls['Topic']; ?>">Topic</button>
									<?php
								}
								if (isset($videoUrls['Intro'])) {
									?>
									<button class="loadVideo" videopath="<?php echo $videoUrls['Intro']; ?>">Intro</button>
									<?php
								}
								if (isset($videoUrls['Story'])) {
									?>
									<button class="loadVideo" videopath="<?php echo $videoUrls['Story']; ?>">Story</button>
									<?php
								}
								if (isset($videoUrls['More Info'])) {
									?>
									<button class="loadVideo" videopath="<?php echo $videoUrls['More Info']; ?>">More Info</button>
									<?php
								}
								?>
							</td>
						</tr>
						<?php
						}
					?>
				</table>
		<!-- End Selection controls -->

		<hr />

		<!-- Begin jPlayer player -->
		<div id="jp_container_1" class="jp-video jp-video-360p">
			<div class="jp-type-single">
				<div id="jquery_jplayer_1" class="jp-jplayer"></div>
				<div class="jp-gui">
					<div class="jp-video-play">
						<a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
					</div>
					<div class="jp-interface">
						<div class="jp-progress">
							<div class="jp-seek-bar">
								<div class="jp-play-bar"></div>
							</div>
						</div>
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
						<div class="jp-controls-holder">
							<ul class="jp-controls">
								<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
								<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
								<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
								<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
								<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
								<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
							</ul>
							<div class="jp-volume-bar">
								<div class="jp-volume-bar-value"></div>
							</div>
							<ul class="jp-toggles">
								<li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>
								<li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>
								<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
								<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
							</ul>
						</div>
						<div class="jp-title">
							<ul>
								<li></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>
		<!-- End jPlayer player -->

	</body>
</html>