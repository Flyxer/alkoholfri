<?php
global $post;

$icons = get_field("icons","options");
if($icons) :
?>
<div class="h4">
	Del innlegg:
</div>
<div class="post_share">
<?php
	foreach($icons as $icon){
		$url = str_replace(["PERMALINK", "POST_TITLE"], [urlencode(get_permalink()),urlencode($post->post_title)],$icon["url"]);
		print "<a href='".$url."' target='_open'>".$icon["svg"]."</a>";
	}
?>
</div>
<?php
	endif;

