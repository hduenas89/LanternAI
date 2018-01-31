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
		<title>DBT Sample Code</title>
		<!-- These next two lines are required to allow the browser to properly display non-Latin language names -->
		<meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script type="text/javascript" src="/js/jQuery.jPlayer.2.4.0/jquery.jplayer.min.js"></script>
		<link href="/js/blue.monday/jplayer.blue.monday.css" rel="stylesheet" type="text/css" />
		<link href="/styles.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<h1>DBT Sample Code</h1>

		<p>The following samples are designed to help you get started using DBT to serve audio, video, and text.</p>
		<p>Before attempting to run these examples, <strong>be sure to place your DBT key in the configuration 
			file </strong> (includes/config.php).</p>
		<ul>
			<li><a href="/audio.php">Audio Sample</a></li>
			<li><a href="/video.php">Video Sample</a></li>
                        <li><a href="/video_door.php">Video Sample (DOOR International)</a></li>
			<li><a href="/text.php">Text Sample</a></li>
		</ul>


	</body>
</html>