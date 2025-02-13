<?php
	$tags = $attributes["tags_selected"];
	$categories = $attributes["categories_selected"];
	$size = isset($attributes["size"]) ? $attributes["size"] : "small";
	$title = apply_filters("the_title",$attributes["title"]);

	$attrs = [
		"posts_per_page" => 3,
		"post_type" => ["post","page"],
		'tax_query' => []
	];
	if($categories){
		$attrs["tax_query"][] = [
			'taxonomy' => 'category',
			'field' => 'term_id',
			'terms' => $categories
		];
	}
	else if($tags){
		$attrs["tax_query"][] = [
			'taxonomy' => 'post_tag',
			'field' => 'term_id',
			'terms' => $tags
		];
	}

	$posts = get_posts($attrs);

	$excerpt_length = isset($attributes["excerpt_length"]) ? $attributes["excerpt_length"] : 15;

	add_filter("excerpt_length",function ($length) use ($excerpt_length) {return $excerpt_length;});

	?>
<div class="position-relative wp-block-post-card alignwide">
	<?php
		if($title != "")
			print "<h2>".$title."</h2>";
	?>
	<?php
		foreach($posts as $item){
			print "<a href='".get_permalink($item->ID)."' class='card ".$size."'>";
			if(has_post_thumbnail($item->ID)){
				print "<figure class='post_card_image'>";
				$thumbnail = get_post_thumbnail_id($item->ID);
				$img = wp_get_attachment_image_url($thumbnail,"large");
				print "<img alt='' src='".$img."'/>";
			}
			else{
				print "<figure class='post_card_image default'>";
				$img = plugins_url("flyxer-blocks/images/ellipse.png");
				print "<img alt='' src='".$img."' class='default'/>";
			}

			print "</figure>";
			print "<span class='text'>";
			print "<span class='headline'>";
			if($size == "tall")
				print '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-move-right"><path d="M18 8L22 12L18 16"/><path d="M2 12H22"/></svg>';
			print $item->post_title;
			print "</span>";
			if($size == "small"){
				print "<span class='excerpt'>";
				if(function_exists("flyxer_get_ingress"))
					print flyxer_get_ingress($item);
				else
					the_excerpt($item);
				print "</span>";
				print '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-move-right"><path d="M18 8L22 12L18 16"/><path d="M2 12H22"/></svg>';
			}
			print "</span>";
			print "</a>";
		}
	?>
</div>
