<?php
/*
Plugin Name: 	textBlock

*/
if (class_exists("WP_Block_Parser") && !is_admin() && !wp_is_json_request()) {
    add_filter("block_parser_class", function () {
        return "WP_Block_Parser_Custom";
    });
    class WP_Block_Parser_custom extends WP_Block_Parser
    {
        public function parse($document)
        {
            $blocks = parent::parse($document);

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
                "acf/acf-tools"
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
