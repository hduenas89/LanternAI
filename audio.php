<?php
	// Display error information
	ini_set('display_errors', 'on');
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	// Instantiate the DBT SDK
	require_once('includes/config.php');
	require_once('includes/Dbt.php');
	$dbt = new Dbt($apiKey, null, null, 'json');

	// Get the root location of audio files
	$root = $dbt->getAudioLocation('http');
	$root = json_decode($root, true);
	$rootPath = 'http://' . $root[0]['server'].$root[0]['root_path'].'/';

	// Get a list of all languages for which one or more audio volumes are available
	$languages = $dbt->getLibraryVolumeLanguageFamily(null, null, 'audio');
	$languages = json_decode($languages, true);
?>
<html>
	<head>
		<title>DBT Audio Sample</title>
		<!-- These next two lines are required to allow the browser to properly display non-Latin language names -->
		<meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script type="text/javascript" src="/js/jQuery.jPlayer.2.4.0/jquery.jplayer.min.js"></script>
		<link href="/js/blue.monday/jplayer.blue.monday.css" rel="stylesheet" type="text/css" />
		<link href="/styles.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">
		//<![CDATA[
		var rootUrl = "<?php echo $rootPath; ?>";
		var dbtKey = "<?php echo $apiKey; ?>";
		/* Normally, you would want to hide the key within an API proxy file. It is shown here for simplicity. */

		$(document).ready(function(){

			// Set up jPlayer
			$("#jquery_jplayer_1").jPlayer({
				ready: function () {
				},
				swfPath: "/js",
				supplied: "mp3", 
				smoothPlayBar: true,
				keyEnabled: true,
				fullScreen: false,
				fullWindow: false
			});

			// When a user selects a language, we need to pull a list of volumes for that language
			$("#languages").on('change', function(){

				// Remove the current options and display a message
				$("#volumes option").remove();
				$("#chapters").hide();
				var languageCode = $("#languages").val();
				if (languageCode === '') { // No language is selected.
					$("#volumes").append("<option value=''>First, Select a Language</option>");
					return false;
				}

				$("#volumes").append("<option value=''>Loading Volumes</option>");

				//retrieve the volumes from DBT
				$.ajax({
					async: true,
					url: 'http://dbt.io/library/volume',
					type:'GET',
					data:'v=2&key=' + dbtKey + '&media=audio&delivery=web&language_family_code=' + escape(languageCode),
					success:function(data){
						if (data) {
							$("#volumes option").remove();
							$("#volumes").append("<option value=''>Select One</option>");
							for (var i = 0 ; i < data.length ; ++i) {
								var volume = data[i];
								var volumeName = volume.volume_name + ' ' + volume.collection_name + ' ' + 
									volume.media_type;
								$("#volumes").append("<option value='" + volume.dam_id + "'>" + volumeName + 
									"</option>");
							}
						}
					}
				});
			});

			// When a user selects a volume, we need to pull a list of books for that volume.
			$("#volumes").on('change', function(){

				// Remove the current options and display a message
				$("#books option").remove();
				$("#chapters").hide();
				var damId = $("#volumes").val();
				if (damId === '') { // No language is selected.
					$("#books").append("<option value=''>First, Select a Volume</option>");
					return false;
				}

				$("#books").append("<option value=''>Loading Books</option>");

				//retrieve the books from DBT
				$.ajax({
					async: true,
					url: 'http://dbt.io/library/book',
					type:'GET',
					data:'v=2&key=' + dbtKey + '&dam_id=' + escape(damId),
					success:function(data){
						if (data) {
							$("#books option").remove();
							$("#books").append("<option value=''>Select One</option>");
							for (var i = 0 ; i < data.length ; ++i) {
								var book = data[i];
								$("#books").append("<option value='" + book.book_id + "'>" + 
									book.book_name + "</option>");
							}
						}
					}
				});
			});

			// When a user selects a book, pull a list of chapters for that book
			$("#books").on('change', function(){

				// Remove the current options and display a message
				$("#chapters span").html('');
				var damId = $("#volumes").val();
				var bookId = $("#books").val();
				if (damId === '') { // No volume is selected.
					$("#chapters").hide();
					return false;
				}
				$("#chapters").show();
				if (bookId == '') { // No book is selected.
					$("#chapters span").text("Please select a book first.");
					return false;
				}

				$("#chapters span").text("Loading chapters");

				/* Retrieve the chapters from DBT. Instead of using the /library/chapter call, we will use the
				/audio/path call, so that we can retrieve a list of chapters and audio file locations at the
				same time. */
				$.ajax({
					async: true,
					url: 'http://dbt.io/audio/path',
					type:'GET',
					data:'v=2&key=' + dbtKey + '&dam_id=' + escape(damId) + '&book_id=' + escape(bookId),
					success:function(data){
						if (data) {
							$("#chapters span").html('');
							for (var i = 0 ; i < data.length ; ++i) {
								var chapter = data[i];
								$("#chapters span").append("<a href='#' onclick='loadChapter(\"" + 
									chapter.path + "\");return false;'>" + chapter.chapter_id + "</a>");
							}
						}
					}
				});
			});

			//initialize each control
			$("#languages, #volumes, #books").change();
			
		});
	
		function loadChapter(path) {
			// Set the audio source.
			$("#jquery_jplayer_1").jPlayer('setMedia', {
				mp3: rootUrl + path
			});

			//automatically start playing
			$("#jquery_jplayer_1").jPlayer('play');
		}
		//]]>
		</script>

	</head>
	<body>
		<h1>Audio Sample</h1>

		<p>This sample shows how to retrieve a list of available audio volumes for a given language, and how to 
			retrieve the location of a specific audio file. 
			<a href="http://www.jplayer.org/" target="_blank">JPlayer</a> is used as the audio player.</p>
		<p>Before attempting to run this example, <strong>be sure to place your DBT key in the configuration 
			file </strong> (includes/config.php).</p>

		<!-- Begin Selection controls -->
		<h3>Step 1: Select a Language</h3>
		<select name="languages" id="languages">
			<option value="">Select One</option>
			<?php
			foreach ($languages as $language) {
				$languageName = $language['language_family_name'];
				// If the language name is localized, display the English name in parenthsis
				if ($language['language_family_name'] != $language['language_family_english']) {
					$languageName .= ' ('.$language['language_family_english'].')';
				}
				?><option value="<?php echo $language['language_family_code'] ?>"><?php echo $languageName
				?></option><?php
			}
		?></select>

		<h3>Step 2: Select a Volume</h3>
		<select name="volumes" id="volumes">
			<option value="">First, Select a Language</option>
		</select>

		<h3>Step 3: Select a Book and Chapter</h3>
		<select name="books" id="books">
			<option value=""></option>
		</select>

		<div id="chapters">
			<label>Select a chapter: </label>
			<span></span>
		</div>
		<div class="clearfix"></div>
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
								<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">
									max volume</a></li>
							</ul>
							<div class="jp-volume-bar">
								<div class="jp-volume-bar-value"></div>
							</div>
							<ul class="jp-toggles">
								<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
								<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">
									repeat off</a></li>
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
					To play the media you will need to either update your browser to a recent version or update 
					your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>
		<!-- End jPlayer player -->

	</body>
</html>