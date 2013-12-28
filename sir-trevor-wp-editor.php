<html>
<head>
	<link rel='stylesheet' id='colors-css'  href='lib/sir-trevor.css' type='text/css' media='all' />
	<link rel='stylesheet' id='colors-css'  href='lib/sir-trevor-icons.css' type='text/css' media='all' />
	<link rel='stylesheet' id='colors-css'  href='sir-trevor-wp-editor.css' type='text/css' media='all' />

	<script type='text/javascript' src='//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js'></script>
	<script type='text/javascript' src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js'></script>
	<script type='text/javascript' src='lib/eventable.js'></script>
	<script type='text/javascript' src='lib/sir-trevor.js'></script>
	
	<?php
		$files = scandir('custom-blocks/');
		foreach($files as $file)
			if(strlen($file) > 2)
				echo "<script type='text/javascript' src='custom-blocks/$file'></script>";
	?>

	<script type='text/javascript' src='sir-trevor-wp-editor.js'></script>
</head>
<body>
	<form action='/'>
		<textarea class="wp-editor-area" name='text'></textarea>
		<input id='submit' type='submit'>
	</form>
</body>
</html>