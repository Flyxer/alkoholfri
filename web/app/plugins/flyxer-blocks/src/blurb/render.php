<span class="blurb_text">
<?php

global $post;
$blurb = get_post_meta($post->ID,"_flyxer_post_blurb","single");
print $blurb;
?>
</span>
