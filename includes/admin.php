<?php

/**
 * Configuration file for the administration area
 *
 * Add a direct link to the settings page from the plugins list.
 *
 * @category Plugin
 * @package  WP_Extra_Settings
 * @author   Mirko Grewing <mirko.grewing@gmail.com>
 * @license  GNU General Public License v2.0
 * @link     http://www.mirkogrewing.it/wp-extra-settings
 */    

/**
 * Adds shortcut to settings in plugin management area 
 *
 * @param string $actions The link
 * @param string $file    The path
 *
 * @since 1.0
 * @return string
 */
function mgWpesSettingsLink($actions, $file)
{
    if(false !== strpos($file, 'wp-extra-settings'))
        $actions['settings'] = '<a href="options-general.php?page=mg_wpes_options">' . _e('Settings', 'wp-extra-options') . '</a>';
    return $actions; 
}

add_filter('plugin_action_links', 'mgWpesSettingsLink', 2, 2);
?>