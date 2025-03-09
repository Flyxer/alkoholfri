<?php
/*
Plugin Name: 	textBlock

*/

if ( !function_exists( 'is_rest' ) ) {
    /**
     * Checks if the current request is a WP REST API request.
     *
     * Case #1: After WP_REST_Request initialisation
     * Case #2: Support "plain" permalink settings and check if `rest_route` starts with `/`
     * Case #3: It can happen that WP_Rewrite is not yet initialized,
     *          so do this (wp-settings.php)
     * Case #4: URL Path begins with wp-json/ (your REST prefix)
     *          Also supports WP installations in subfolders
     *
     * @returns boolean
     * @author matzeeable
     */
    function is_rest() {
        if (defined('REST_REQUEST') && REST_REQUEST // (#1)
            || isset($_GET['rest_route']) // (#2)
            && strpos( $_GET['rest_route'], '/', 0 ) === 0)
            return true;

        // (#3)
        global $wp_rewrite;
        if ($wp_rewrite === null) $wp_rewrite = new WP_Rewrite();

        // (#4)
        $rest_url = wp_parse_url( trailingslashit( rest_url( ) ) );
        $current_url = wp_parse_url( add_query_arg( array( ) ) );
        return strpos( $current_url['path'] ?? '/', $rest_url['path'], 0 ) === 0;
    }
}

add_action("wp_enqueue_scripts",function(){
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'style',  $plugin_url . "/css/style.css");
});

add_filter("the_excerpt" ,function($excerpt){
    global $post;
    return flyxer_get_ingress($post);
},10);

add_filter("excerpt_length",function ($length) {
    if(is_rest())
        return 50;
    else
        return $length;
});

function flyxer_get_ingress($post){
    $parsed_blocks = array_values(parse_blocks($post->post_content));
    if ($parsed_blocks) {
        foreach($parsed_blocks as $block){
            $length = apply_filters("excerpt_length",10);
            if ($block["blockName"] == "flyxer/text")
                foreach ($block["innerBlocks"] as $block) {
                    if ($block["attrs"]["fontSize"] == "medium") {
                        return wp_trim_words( strip_tags(render_block($block)),$length,"...");
                    }
                }
            else if($block["blockName"] == "core/paragraph"){
                return wp_trim_words( strip_tags(render_block($block)),$length,"...");
            }
        }
    }

    return $post->post_excerpt;
}

if (class_exists("WP_Block_Parser") && !is_admin() && !wp_is_json_request()) {
    add_filter("block_parser_class", function ($parser_class ) {
        return "WP_Block_Parser_Custom";
    });
    class WP_Block_Parser_custom extends WP_Block_Parser
    {
        public function parse($document)
        {
            $blocks = parent::parse($document);

            global $post;
            if(isset($post) && $document != $post->post_content)
                return $blocks;

            $blockName = "flyxer/text";
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
                "core/paragraph",
                "core/columns",
                "acf/acf-tools",
                "core/social-links"
            ];

            $always = [
                "core/list",
                "acf/google"
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
                                !(isset($i["attrs"]) && isset($i["attrs"]["align"]) && ($i["attrs"]["align"] == "center" || $i["attrs"]["align"] == "wide" || $i["attrs"]["align"] == "full")) &&
                                !(isset($i["attrs"]) && isset($i["attrs"]["className"]) && ($i["attrs"]["className"] == "is-style-large" || $i["attrs"]["className"] == "is-style-spotlight"))
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
    if (!function_exists('register_block_type')) {
        // Gutenberg is not active.
        return;
    }

    register_block_type(
        'flyxer/text',
        array(
            'render_callback' => function ($atts, $content) {
                return "<section class='text_content'>" . $content . "</section>";
            },
            'attributes' => array(
                'innerContent' => array(
                    'type' => 'array'
                )
            )
        )
    );
});