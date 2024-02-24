<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <?php
      $colors = get_field("colors","option");
      if(is_single() || is_page()){
        global $post;
        $area = wp_get_post_terms($post->ID,"area");
        if(isset($area) && isset($area[0])){
          $color = get_field("color",$area[0]);
        }
        else{
          $color = get_field("color","option");
        }
      }
      else{
        $color = get_field("color","option");
      }

      if(!$color)
        $color = "black";

      foreach($colors as $item){
        if($item["slug"] == $color)
          $colorcode = $item["main_color"];
      }
      print "<style>:root {--page-color: rgb(".$colorcode["red"].",".$colorcode["green"].",".$colorcode["blue"].")}</style>";
    ?>
  </head>

  <body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <?php do_action('get_header'); ?>

    <div id="app">
      <?php echo view(app('sage.view'), app('sage.data'))->render(); ?>
    </div>

    <?php do_action('get_footer'); ?>
    <?php wp_footer(); ?>
  </body>
</html>
