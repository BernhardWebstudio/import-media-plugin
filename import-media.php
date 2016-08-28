<?php

/**
 * Plugin Name: Import Media
 * Text Domain: bw-import-media
 * Domain Path: /languages
 * Description: Import linked media to your own media library
 * Version:     1.0
 * Author:      Tim Bernhard
 * Author URI:  http://genieblog.ch
 * License:     VVL
 * License URI: https://bullg.it/VVL/
 */
defined('ABSPATH') or die('No script kiddies please!');

require_once('import-media-options.php');
require_once('media-importer.php');

// plugin_activation_check() by Otto
function import_media_activation_check() {
    if (version_compare(PHP_VERSION, '5.0.0', '<')) {
        deactivate_plugins(basename(__FILE__)); // Deactivate myself
        wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.", 'bw-import-media');
    }
}

register_activation_hook(__FILE__, 'import_media_activation_check');

// Option page styles
function import_media_css() {
    wp_register_style('import-media-css', plugins_url('import-media-styles.css', __FILE__));
}

function add_import_media_styles() {
    wp_enqueue_style('import-media-css');
}

add_action('admin_init', 'import_media_css');

// Option page scripts
function import_media_scripts() {
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('import-media-tabs', plugins_url('js/tabs.js', __FILE__), array('jquery', 'jquery-ui-tabs'));
}

// set default options 
function import_media_set_defaults() {
    $options = import_media_get_options();
    add_option('import_media', $options, '', 'no');
}

register_activation_hook(__FILE__, 'import_media_set_defaults');

//register our settings
function register_import_media_settings() {
    register_setting('import_media', 'import_media', 'import_media_validate_options');
}

// when uninstalled, remove option
function import_media_remove_options() {
    delete_option('import_media');
}

register_uninstall_hook(__FILE__, 'import_media_remove_options');

// for testing only
// register_deactivation_hook( __FILE__, 'import_media_remove_options' );


function import_media_add_pages() {
// Add option page to admin menu
    $pg = add_options_page(__('Media Import', 'bw-import-media'), __('Media Import', 'bw-import-media'), 'manage_options', basename(__FILE__), 'import_media_options_page');

// Add styles and scripts
    add_action('admin_print_styles-' . $pg, 'add_import_media_styles');
    add_action('admin_print_scripts-' . $pg, 'import_media_scripts');

// register setting
    add_action('admin_init', 'register_import_media_settings');

// Help screen 
    $text = '<p>' . sprintf(__('This is a importer with lots of options. </p>'));
    $text .= '<p>' . __("You need to look through the tabs and save your settings before you run the importer. The Tab Tools contains links to some tools that are helpful after you've imported.", 'bw-import-media') . '</p>';

    $text .= '<h3>' . __('Tips', 'bw-import-media') . "</h3>
    <ol>
		<li>" . __("If there is already some content in this site, you should back up your database before you import. Just in case...", 'bw-import-media') . "</li>
    </ol>";

//	add_contextual_help( $pg, $text );
}

add_action('admin_menu', 'import_media_add_pages');

// Add link to options page from plugin list
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'import_media_plugin_actions');

function import_media_plugin_actions($links) {
    $new_links = array();
    $new_links[] = sprintf('<a href="options-general.php?page=import-media.php">%s</a>', __('Settings', 'import-media'));
    return array_merge($new_links, $links);
}
