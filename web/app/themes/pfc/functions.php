<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/
require_once "include/gutenberg.php";
if (!file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

add_filter('big_image_size_threshold', '__return_false');

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

try {
    \Roots\bootloader();
} catch (Throwable $e) {
    wp_die(
        __('You need to install Acorn to use this theme.', 'sage'),
        '',
        [
            'link_url' => 'https://docs.roots.io/acorn/2.x/installation/',
            'link_text' => __('Acorn Docs: Installation', 'sage'),
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters'])
    ->each(function ($file) {
        if (!locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
            /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });

/*
|--------------------------------------------------------------------------
| Enable Sage Theme Support
|--------------------------------------------------------------------------
|
| Once our theme files are registered and available for use, we are almost
| ready to boot our application. But first, we need to signal to Acorn
| that we will need to initialize the necessary service providers built in
| for Sage when booting.
|
*/
function fromRGB($R, $G, $B)
{

    $R = dechex($R);
    if (strlen($R) < 2)
        $R = '0' . $R;

    $G = dechex($G);
    if (strlen($G) < 2)
        $G = '0' . $G;

    $B = dechex($B);
    if (strlen($B) < 2)
        $B = '0' . $B;

    return '#' . $R . $G . $B;
}

add_theme_support('sage');
add_action('after_setup_theme', function () {
    $colors = get_field("colors", "option");
    foreach ($colors as &$color) {
        $color["color"] = fromRGB($color["main_color"]["red"], $color["main_color"]["green"], $color["main_color"]["blue"]);

        unset($color["main_color"]);
    }

    add_theme_support('editor-color-palette', $colors);
});

function get_page_color($id)
{
    $area = wp_get_post_terms($id, "area");
    if (isset($area) && isset($area[0])) {
        return get_field("color", $area[0]);
    }
}

add_filter('acf/load_field/name=color', function ($field) {
    // reset choices
    $field['choices'] = array();
    // if has rows
    if (have_rows('colors', 'option')) {
        // while has rows
        while (have_rows('colors', 'option')) {

            // instantiate row
            the_row();

            // vars
            $value = get_sub_field('slug');
            $label = get_sub_field('name');

            // append to choices
            $field['choices'][$value] = $label;
        }

    }

    // return the field
    return $field;
});


add_action("wp_head", "add_colors");
add_action("admin_head", "add_colors");
function add_colors()
{
    ?><style>
    :root {
    <?php
              $colors = get_field("colors","option");
              foreach($colors as $color){
                print "\t\t--color-".$color["slug"]."-main: rgb(".$color["main_color"]["red"].",".$color["main_color"]["green"].",".$color["main_color"]["blue"].");\n";
              }
    ?>
    }
    <?php
    foreach($colors as $color){
      print ".bg-".$color["slug"]."{background: rgb(".$color["main_color"]["red"].",".$color["main_color"]["green"].",".$color["main_color"]["blue"].")}\n";
    }
    ?></style><?php
}
