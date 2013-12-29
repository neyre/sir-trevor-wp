<?php
// If Caption
if($block['source'] == 'youtube'):
?>
	
	<p><iframe src="//www.youtube-nocookie.com/embed/<?php echo $data['remote_id']; ?>" frameborder="0" allowfullscreen=""></iframe></p>

<?php
// If No Caption
elseif($block['source'] == 'vimeo'):
?>

	<p><iframe src="http://player.vimeo.com/video/<?php echo $data['remote_id']; ?>?title=0&byline=0" frameborder="0" allowfullscreen=""></iframe></p>

<?php endif; ?>