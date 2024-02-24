<?php
if (class_exists("WP_Block_Parser") && !is_admin()) {
    add_filter("block_parser_class", function () {
        return "WP_Block_Parser_Custom";
    });
    class WP_Block_Parser_custom extends WP_Block_Parser
    {
        public function parse($document)
        {
            $blocks = parent::parse($document);

            $blockName = "pfc/text";
            $resulting_blocks = [];
            $n = 0;

            $text_blocks = [
                "core/quote",
                "core/image",
                "core/embed",
                "core-embed/youtube",
                "core-embed/vimeo",
                "core/more",
                "core/spacer",
                "core/table",
                "core/gallery",
                "gutena/accordion",
                "core/heading",
            ];

            $always = [
                "core/paragraph",
                "core/list",
            ];

            foreach ($blocks as $item) {
                if (
                    in_array($item["blockName"], $always) || (
                        in_array($item["blockName"], $text_blocks) &&
                        !(isset($item["attrs"]) &&
                            isset($item["attrs"]["align"]) &&
                            ($item["attrs"]["align"] == "center" || $item["attrs"]["align"] == "wide" || $item["attrs"]["align"] == "full"))
                    )) {
                    if (isset($resulting_blocks[$n]) && $resulting_blocks[$n]["blockName"] == $blockName) {
                        $resulting_blocks[$n]["innerContent"][] = $item;
                        $resulting_blocks[$n]["innerBlocks"][] = $item;
                    } else {
                        $resulting_blocks[++$n] = [
                            "blockName" => $blockName,
                            "innerContent" => [$item],
                            "innerBlocks" => [$item]
                        ];
                    }
                } elseif (in_array($item["blockName"], ["acf/accordion"])) {
                    if (isset($resulting_blocks[$n]) && $resulting_blocks[$n]["blockName"] == "accordion/list") {
                        $resulting_blocks[$n]["innerContent"][] = $item;
                        $resulting_blocks[$n]["innerBlocks"][] = $item;
                    } else {
                        $resulting_blocks[++$n] = [
                            "blockName" => "accordion/list",
                            "innerContent" => [$item],
                            "innerBlocks" => [$item]
                        ];
                    }
                } elseif (in_array($item["blockName"], ["core/columns"]) && count($item["innerBlocks"]) == 1) {
                    $columns_blocks = [];
                    $m = 0;
                    foreach ($item["innerBlocks"][0]["innerBlocks"] as $i){
                        if (
                            in_array($i["blockName"], $always) || (
                                in_array($i["blockName"], $text_blocks) &&
                                !(isset($i["attrs"]) &&
                                    isset($i["attrs"]["align"]) &&
                                    ($i["attrs"]["align"] == "center" || $i["attrs"]["align"] == "wide" || $i["attrs"]["align"] == "full"))
                            )) {
                            if (isset($columns_blocks[$m]) && $columns_blocks[$m]["blockName"] == $blockName) {
                                $columns_blocks[$m]["innerContent"][] = $i;
                                $columns_blocks[$m]["innerBlocks"][] = $i;
                            } else {
                                $columns_blocks[++$m] = [
                                    "blockName" => $blockName,
                                    "innerContent" => [$i],
                                    "innerBlocks" => [$i]
                                ];
                            }
                        }
                        elseif($i["blockName"]) {
                            $columns_blocks[++$m] = $i;
                        }
                    }

                    $item["innerBlocks"][0]["innerContent"] = array_values($columns_blocks);
                    $item["innerBlocks"][0]["innerBlocks"] = array_values($columns_blocks);
                    $resulting_blocks[++$n] = $item;
                }
                elseif ($item["blockName"]) {
                    $resulting_blocks[++$n] = $item;
                }
            }
            return $resulting_blocks;
        }
    }
}

add_action('init', function () {
    if (! function_exists('register_block_type')) {
        // Gutenberg is not active.
        return;
    }

    register_block_type(
        'pfc/text',
        array(
            'render_callback' => function ($atts, $content) {
                return "<section class='text_content'>".$content."</section>";
            },
            'attributes' => array(
                'innerContent' => array(
                    'type' => 'array'
                )
            )
        )
    );

    register_block_type(
        'pfc/quote-slider',
        array(
            'render_callback' => "pfc_quote_slider",
            'innerContent' => array(
                'type' => 'array'
            ),
            'icon' => 'quote',
            'name' => 'pfc/quote-slider',
            "title" => "Quote Slider",
        )
    );
    function pfc_quote_slider($atts, $content) {
        return "<section class='quote-slider'>".$content."</section>";
    }

    acf_register_block([
        "title" => "Tools",
        "name" => "acf/tools",
        'post_types' => array('post', 'page','project'),
        'render_template' => 'acf-blocks/tools.php',
        'mode' => 'auto',
    ]);

    acf_register_block([
        "title" => "CTA",
        "name" => "acf/cta",
        'post_types' => array('post', 'page','project'),
        'render_template' => 'acf-blocks/cta.php',
        'mode' => 'auto',
    ]);

    acf_register_block([
        "title" => "Banner",
        "name" => "acf/banner",
        'post_types' => array('post', 'page','project'),
        'render_template' => 'acf-blocks/banner.php',
        'mode' => 'auto',
    ]);
});

// add copyright tag to images
add_filter('render_block', function ($block_html, $block) {
    if ($block["blockName"] == "core/image") {
        if (isset($block["attrs"]["id"])) {
            $image_id = $block["attrs"]["id"];

            $meta = wp_get_attachment_metadata($image_id);
            if (isset($meta["parent_image"])) {
                $image_copyright = get_field("copyright", $meta["parent_image"]["attachment_id"]);
            } else {
                $image_copyright = get_field("copyright", $image_id);
            }

            if ($image_copyright) {
                //$image_id = $block["attrs"]["id"];
                $image = get_post($image_id);
                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                $image_title = get_the_title($image_id);
                $image_align = isset($block["attrs"]["align"]) ? $block["attrs"]["align"]: "";
                $image_size = isset($block["attrs"]["sizeSlug"]) ? $block["attrs"]["sizeSlug"]: "medium";
                $image_dims = wp_get_attachment_image_src($image_id, $image_size);
                $image_caption = $image->post_excerpt;
                ob_start(); ?>
                <div class="wp-block-image">
                    <figure class="align<?php print $image_align; ?> size-<?php print $image_size?>">
                        <div class="image_box">
                            <img width="<?= $image_dims[1]; ?>" height="<?= $image_dims[2]; ?>" srcset="<?php print wp_get_attachment_image_srcset($image_id); ?>" srcset="<?php print wp_get_attachment_image_srcset($image_id, $image_size); ?>" src="<?php print wp_get_attachment_image_url($image_id, $image_size); ?>" title="<?php print $image_title?>" alt="<?php print $image_alt; ?>">
                            <span class="copy"><?php print $image_copyright; ?></span>
                        </div>
                        <?php if ($image_caption) {
                            print "<figcaption>".$image_caption."</figcaption>";
                        } ?>
                    </figure>
                </div>
                <?php


                return ob_get_clean();//str_replace("</figure>","<span class='copy'>".$image_copyright."</span></figure>",$block_html);
            }
        }
    }
    elseif ($block["blockName"] == "core/media-text") {
        if (isset($block["attrs"]["mediaId"])) {
            $image_id = $block["attrs"]["mediaId"] ? $block["attrs"]["mediaId"] : $block["attrs"]["id"];

            $meta = wp_get_attachment_metadata($image_id);
            if (isset($meta["parent_image"])) {
                $image_copyright = get_field("copyright", $meta["parent_image"]["attachment_id"]);
            } else {
                $image_copyright = get_field("copyright", $image_id);
            }

            if ($image_copyright) {
                //$image_id = $block["attrs"]["id"];
                $image = get_post($image_id);
                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                $image_title = get_the_title($image_id);
                $image_align = isset($block["attrs"]["align"]) ? $block["attrs"]["align"]: "";
                $image_size = isset($block["attrs"]["sizeSlug"]) ? $block["attrs"]["sizeSlug"]: "medium";
                $image_caption = $image->post_excerpt;
                ob_start(); ?>
                <div class="wp-block-media-text alignwide is-stacked-on-mobile">
                    <figure class="wp-block-image align<?php print $image_align; ?> size-<?php print $image_size?>">
                        <div class="image_box">
                            <img srcset="<?php print wp_get_attachment_image_srcset($image_id); ?>" src="<?php print wp_get_attachment_image_url($image_id, $image_size); ?>" title="<?php print $image_title?>" alt="<?php print $image_alt; ?>">
                            <span class="copy"><?php print $image_copyright; ?></span>
                        </div>
                        <?php if ($image_caption) {
                            print "<figcaption>".$image_caption."</figcaption>";
                        } ?>
                    </figure>
                    <div class="wp-block-media-text__content">
                        <?php
                        foreach ($block["innerBlocks"] as $block) {
                            print render_block($block);
                        } ?>
                    </div>
                </div>
                <?php


                return ob_get_clean();//str_replace("</figure>","<span class='copy'>".$image_copyright."</span></figure>",$block_html);
            }
        }
    }
    elseif ($block["blockName"] == "core/gallery") {
        $gallery_id = uniqid("gallery-");


        $images = isset($block["attrs"]["ids"]) ? $block["attrs"]["ids"] : [];
        if (count($block["innerBlocks"])) {
            foreach ($block["innerBlocks"] as $image) {
                $images[] = $image["attrs"]["id"];
            }
        }


        $align = isset($block["attrs"]["align"]) ? $block["attrs"]["align"] : "";
        //if(count($images) > 1) {
        ob_start();

        print "<div class='image_gallery align" . $align . "' uk-lightbox>";
        $added_img = false;
        $added_video = false;
        $buttons = [];
        $links = [];

        $not_empty = 0;

        foreach ($images as $key => $image) {
            $caption = wp_get_attachment_caption($image);
            $meta = wp_get_attachment_metadata($image);
            if (isset($meta["parent_image"])) {
                $copy = get_field("copyright", $meta["parent_image"]["attachment_id"]);
            } else {
                $copy = get_field("copyright", $image);
            }

            $alt = get_post_meta($image, '_wp_attachment_image_alt', true);
            $attachment = wp_get_attachment_url($image);


            if ($attachment) {
                $not_empty = 1;
                if (substr($attachment, -3, 3) == "mp4") {
                    $link = $attachment;
                    if (!$added_video) {
                        $buttons[] = "<a href='" . $link . "' class='btn btn-primary inv btn-md thickbox' rel='" . $gallery_id . "' data-caption='" . $caption . " &copy; ". $copy."' alt='".$alt."'><span>" . __("Play video", "helgeland") . "</span></a>";
                        $added_video = true;
                    } else {
                        $links[] = $link;
                    }
                } else {
                    $link = wp_get_attachment_image_url($image, "full");

                    if (!$added_img) {
                        print "<img src='" . wp_get_attachment_image_url($image, "large") . "'/>";
                        print "<span class='copy'>".$copy . "</span>";
                        $buttons[] = "<a href='" . $link . "' class='btn btn-primary inv btn-lg thickbox' rel='" . $gallery_id . "' data-caption='" . $caption . " &copy; ".$copy."' alt='".$alt."'><span>" . __("Open gallery", "helgeland") . "</span></a>";
                        $added_img = true;
                    } else {
                        $links[] = ["url" => $link, "caption" => $caption,"copy" => $copy];
                    }
                }
            }
        }
        print "<div class='buttons'>";
        foreach ($buttons as $k => $button) {
            print $button;
            if (!$k) {
                foreach ($links as $item) {
                    print "<a href='" . $item["url"] . "' class='thickbox' rel='" . $gallery_id . "' data-caption='" . $item["caption"] . " &copy; ".$item["copy"]."' alt='".$alt."'></a>";
                }
            }
        }
        print "</div>";
        print "</div>";
        $content = ob_get_clean();

        if ($not_empty) {
            return $content;
        } else {
            return "";
        }
        //}
    }
    elseif ($block["blockName"] == "core/columns" && count($block["innerBlocks"]) == 1 ){
        ob_start();
        print "<div class='wp-block-content".(isset($block["attrs"]["backgroundColor"]) ? " has-".str_replace("_","-",$block["attrs"]["backgroundColor"])."-background-color" : "")."'><div class='container".(isset($block["attrs"]["align"]) ? " align".$block["attrs"]["align"] : "")."'>";
        foreach($block["innerBlocks"][0]["innerBlocks"] as $item)
            print render_block($item);
        print "</div></div>";
        return ob_get_clean();
    }

    return $block_html;
}, 10, 2);
