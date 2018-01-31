<?php
	// Display error information
	ini_set('display_errors', 'on');
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	// Instantiate the DBT SDK
	require_once('includes/config.php');
	require_once('includes/Dbt.php');
	$dbt = new Dbt($apiKey, null, null, 'json');

	// Get a list of all languages for which one or more text volumes are available
	$languages = $dbt->getLibraryVolumeLanguageFamily(null, null, 'text');
	$languages = json_decode($languages, true);
?>
<html>
	<head>
		<title>DBT Text Sample</title>
		<!-- These next two lines are required to allow the browser to properly display non-Latin language names -->
		<meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<link href="/styles.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">
		//<![CDATA[
		var dbtKey = "<?php echo $apiKey; ?>";
		/* Normally, you would want to hide the key within an API proxy file. It is shown here for simplicity. */

		$(document).ready(function(){

			// When a user selects a language, we need to pull a list of volumes for that language
			$("#languages").on('change', function(){

				// Remove the current options and display a message
				$("#volumes option").remove();
				$("#chapterText").html('');
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
					data:'v=2&key=' + dbtKey + '&media=text&delivery=web&language_family_code=' + escape(languageCode),
					success:function(data){
						if (data) {
							$("#volumes option").remove();
							$("#volumes").append("<option value=''>Select One</option>");
							for (var i = 0 ; i < data.length ; ++i) {
								var volume = data[i];
								var volumeName = volume.volume_name + ' ' + volume.collection_name + ' ' + 
									volume.media_type;
								// Remember if this is a right-to-left language, such as Arabic
								var dir = (volume.right_to_left == 'true' ? 'rtl' : '');


								$("#volumes").append("<option value='" + volume.dam_id + "' data-dir='" + dir + 
									"'>" + volumeName + "</option>");
							}
						}
					}
				});
			});

			// When a user selects a volume, we need to pull a list of books for that volume.
			$("#volumes").on('change', function(){

				// Remove the current options and display a message
				$("#books option").remove();
				$("#chapterText").html('');
				$("#chapters").hide();
				var damId = $("#volumes").val();
				if (damId === '') { // No language is selected.
					$("#books").append("<option value=''>First, Select a Volume</option>");
					return false;
				}

				// Get the text direction stored in the option data
				var dir = $('#volumes option:selected').attr('data-dir');
				$("#chapterText").attr('dir', dir);

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
				$("#chapterText").html('');
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

				$.ajax({
					async: true,
					url: 'http://dbt.io/library/chapter',
					type:'GET',
					data:'v=2&key=' + dbtKey + '&dam_id=' + escape(damId) + '&book_id=' + escape(bookId),
					success:function(data){
						if (data) {
							$("#chapters span").html('');
							for (var i = 0 ; i < data.length ; ++i) {
								var chapter = data[i];
								$("#chapters span").append("<a href='#' onclick='loadChapter(\"" + 
									chapter.chapter_id + "\");return false;'>" + chapter.chapter_id + "</a>");
							}
						}
					}
				});
			});

			//initialize each control
			$("#languages, #volumes, #books").change();
			
		});
	
		function loadChapter(chapterId) {

			// Get the Bible location.
			var damId = $('#volumes').val();
			var bookId = $('#books').val();

			if ((damId === '') || (bookId === '')) {
				$("#chapterText").html('');
				return false;
			}

			// Load the verse text from DBT
			$("#chapterText").html('Loading...');
			$.ajax({
					async: true,
					url: 'http://dbt.io/text/verse',
					type:'GET',
					data:'v=2&key=' + dbtKey + '&dam_id=' + escape(damId) + '&book_id=' + escape(bookId) + 
						'&chapter_id=' + chapterId,
					success:function(data){
						if (data) {
							$("#chapterText").html('');
							for (var i = 0 ; i < data.length ; ++i) {
								var verse = data[i];
								$("#chapterText").append("<span class='verse'><span class='verse-number'>" + 
									verse.verse_id + "</span>" + verse.verse_text.trim() + "</span>");
							}
						}
					}
				});
		}
		//]]>
		</script>

	</head>
	<body>
		<h1>Text Sample</h1>

		<p>This sample shows how to retrieve a list of available text volumes for a given language, and how to 
			display the text for a chapter.</p>
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

		<div id="chapterText"></div>

	</body>
</html>