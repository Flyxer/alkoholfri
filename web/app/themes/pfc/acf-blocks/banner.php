<?php
    $title = get_field("headline");
    $img = get_field("image");
    $text = get_field("text");
    global $post;

    if(!$title)
        $title = get_the_title($post->ID);

    if(!$img && has_post_thumbnail($post->ID)){
        $img = acf_get_attachment(get_post_thumbnail_id($post->ID));
    }
?>
<div class="block_banner">
    <div class="container alignwide lg:rounded-pfc overflow-hidden">
        <div class="banner_text px-10 py-5 flex flex-col justify-between">
            <h1><?php print $title;?></h1>
            <div class="intro">
                <?php print wpautop($text);?>
            </div>
        </div>
        <?php if(isset($img["sizes"])) : ?>
        <figure>
            <img src="<?php print $img["sizes"]["large"]; ?>" alt="<?= $img["alt"];?>"/>
        </figure>
        <?php endif;?>

    </div>
</div>
