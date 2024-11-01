<?php
/**
 * Plugin Name: WP-Extra-Settings    
 * Plugin URI: http://www.mirkogrewing.it/wp-extra-settings
 * Description: WP Extra Settings gives you control over many different options that are manageable without accessing the code otherwise. This plugin will thus enable you to control almost every aspect of WordPress making a more professional tool out of it.
 * Author: Mirko Grewing    
 * Version: 1.3
 * Author URI: http://www.mirkogrewing.it
 * Donate link: http://cl.ly/2C2W181j1G2g
 *
 * @category Plugin
 * @package  WP_Extra_Settings
 * @author   Mirko Grewing <mirko.grewing@gmail.com>
 * @license  GNU General Public License v2.0
 * @link     http://www.mirkogrewing.it/wp-extra-settings
 *
 */

define('MG_WPES_DIR', plugin_dir_path(__FILE__));
define('MG_WPES_URL', plugin_dir_url(__FILE__));
define('MG_WPES_VER', '1.3');

/**
 * Load the required files
 * 
 * @return null
 */
function mgWpesLoad()
{
	load_plugin_textdomain('wp-extra-options', false, dirname(plugin_basename(__FILE__)) . '/lang/');	
    if(is_admin())
        require_once(MG_WPES_DIR.'includes/admin.php');
    require_once(MG_WPES_DIR.'includes/core.php');
}

mgWpesLoad();

?>