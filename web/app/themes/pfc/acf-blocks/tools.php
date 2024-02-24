<?php
    $tools = get_field("fields");
    $arrows = get_field("arrows") ?: false;
?>
<div class="block-tools">
    <div class="container grid gap-20 grid-cols-1 md:grid-cols-<?php print count($tools);?> count-<?php print count($tools);?> alignwide">
        <?php
            foreach($tools as $key => $tool){
                $color = "";
                print "<div class='tool'>";
                if(isset($tool["link"]) && is_object($tool["link"])){
                    $color = get_page_color($tool["link"]->ID);
                    print "<a href='".(isset($tool["link"]) ? get_permalink($tool["link"]->ID) : "#")."' class='py-5 tool_link flex flex-col items-center rounded-pfc border-2 ".$color.($arrows && $key != count($tools)-1 ? " arrow-after" : "")."' style='--color: var(--color-".$color."-main);'>";
                    if(isset($tool["icon"]) && $tool["icon"]){
                        if(strpos($tool["icon"]["mime_type"],"svg")){
                            $file = get_attached_file($tool["icon"]["ID"]);
                            if(file_exists($file))
                                print file_get_contents($file);
                        }
                        else{
                            print "<figure><img alt='' src='".$tool["icon"]["sizes"]["thumbnail"]."'></figure>";
                        }
                    }
                    if(isset($tool["link"]->post_title)){
                        $title = isset($tool["title_overwrite"]) && $tool["title_overwrite"] ? $tool["title_overwrite"] : $tool["link"]->post_title;
                        print "<span class='link_title'>".$title."</span>";
                    }
                    print "</a>";
                }
                if(isset($tool["text"]) && $tool["text"]){
                    print "<div class='text mt-5 mb-5'>".$tool["text"]."</div>";
                }
                print "</div>";
            }
        ?>
    </div>
</div>
