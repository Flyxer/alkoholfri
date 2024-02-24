<?php

    $title = get_field("title");
    $text = get_field("text");
    $link = get_field("link");
    $background = get_field("color");
?>
<section class="cta_block">
    <div class="container<?php print $block["align"] ? " align-".$block["align"] : ""; ?> <?php print $background ? " bg-".$background: "";?>">
        <div class="text-container">
            <span class="cta-title h3"><?php print $title;?></span>
            <div class="cta-text"><?php print apply_filters("content",$text);?></div>
        </div>

        <?php if(isset($link["url"])):?>
        <a href="<?php print $link["url"];?>" class="btn white"><?php print $link["title"]?></a>
        <?php endif; ?>
    </div>
</section>
