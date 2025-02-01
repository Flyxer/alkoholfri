<?php
if (isset($attributes["images"]) && $attributes["images"]) :
	$random = rand(0, count($attributes["images"]) - 1);

	if (isset($attributes["maskID"]) && $attributes["maskID"] > 1) {
		$mask = "style='mask-image: url(".wp_get_attachment_url($attributes["maskID"]).")'";
	}



	?>

	<figure <?= $mask; ?> class="swap-image <?php if(isset($mask)) print "mask"?>">
		<img alt="" srcset="<?= wp_get_attachment_image_srcset($attributes["images"][$random]["mediaID"]); ?>"
			 src="<?= wp_get_attachment_image_src($attributes["images"][$random]["mediaID"]) ?>">
	</figure>
<?php
endif;
