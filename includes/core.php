<?php
/**
 * Core file of the plugin
 *
 * It contains the main class and all the functions
 *
 * @category Plugin
 * @package  WP_Extra_Settings
 * @author   Mirko Grewing <mirko.grewing@gmail.com>
 * @license  GNU General Public License v2.0
 * @link     http://www.mirkogrewing.it/wp-extra-settings
 */    

/**
 * Main class of the plugin
 *
 * @category Plugin
 * @package  WP_Extra_Settings
 * @author   Mirko Grewing <mirko.grewing@gmail.com>
 * @license  GNU General Public License v2.0
 * @link     http://www.mirkogrewing.it/wp-extra-settings
 */    
class MG_WPES_Settings
{
    /* Declares database keys of our tabs/settings */
    private $_general_settings_key = 'mg_general_settings';
    private $_security_settings_key = 'mg_security_settings';
    private $_dashboard_settings_key = 'mg_dashboard_settings';
    private $_authoring_settings_key = 'mg_authoring_settings';
    private $_plugin_options_key = 'mg_wpes_options';
    private $_plugin_settings_tabs = array();
    
    /**
     * Declares database keys of our tabs/settings
     *
     * @return array
     * @since version 1.0
     */
    function __construct()
    {
        add_action('init', array(&$this, 'loadSettings'));
        add_action('admin_init', array(&$this, 'registerGeneralSettings'));
        add_action('admin_init', array(&$this, 'registerSecuritySettings'));
        add_action('admin_init', array(&$this, 'registerDashboardSettings'));
        add_action('admin_init', array(&$this, 'registerAuthoringSettings'));
        add_action('admin_menu', array(&$this, 'addAdminMenus'));
        add_action('init', array($this, 'pluginInit'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueueScripts'));
    }
    
    /**
     * Check whether we are in the plugin page
     *
     * @return boolean
     * @since version 1.0
     */
    function isPluginPage()
    {
        $current_screen = get_current_screen();
        if ($current_screen->id == 'settings_page_mg_wpes_options') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Enqueue additional CSS and JS
     *
     * @return null
     * @since version 1.0
     */
    function enqueueScripts()
    {
        if (self::isPluginPage()) {
            wp_register_script('wpes-icheck', MG_WPES_URL . 'includes/js/jquery.icheck.js', array( 'jquery' ));
            wp_enqueue_script('wpes-icheck');
            wp_enqueue_script('icheck-loader', MG_WPES_URL . 'includes/js/icheck.loader.js', array( 'jquery' ));
            wp_enqueue_style('wpes-css', MG_WPES_URL . 'includes/css/style.css', array(), MG_WPES_VER);
            wp_enqueue_style('wpes-square-blue-css', MG_WPES_URL . 'includes/css/square/blue.css', array(), MG_WPES_VER);
        }
    }
    /**
     * Load existing settings into arrays
     *
     * @return array
     * @since version 1.0
     */
    function loadSettings()
    {
        $this->general_settings = (array) get_option($this->_general_settings_key);
        $this->security_settings = (array) get_option($this->_security_settings_key);
        $this->dashboard_settings = (array) get_option($this->_dashboard_settings_key);
        $this->authoring_settings = (array) get_option($this->_authoring_settings_key);
        
        /* Merge with default values to fill in the gaps */
        $this->general_settings = array_merge(array('wpes_enabled' 	=> '0'), $this->general_settings);
        $this->security_settings = array_merge(
            array(
                'hide_wp_header_meta'		=> '0',
                'failed_login_message'		=> '0',
                'forbid_file_editing'		=> '0',
                'disable_wlw_link'			=> '0',
                'disable_rsd_services'		=> '0',
            ),
            $this->security_settings
        );
        $this->dashboard_settings = array_merge(
            array(
                'remove_msg_footer'			=> '0',
                'disable_version_footer' 	=> '0',
                'disable_upgrade_notice'	=> '0',
                'disable_admin_bar'			=> '0',
                'hide_wp_logo'				=> '0',
                'hide_meta_boxes'			=> '0',
                'show_admin_msg'            => array(
                    'sam_enabled'           => '0',
                    'sam_type'              => 'update',
                    'sam_message'           => 'Write here your message',
                ),
                'hide_help_tab'             => '0',
            ),
            $this->dashboard_settings
        );
        $this->authoring_settings = array_merge(
            array(
                'own_posts_only'			=> '0',
                'allow_media_upload'		=> '0',
                'notify_role_change'		=> array(
                    'nrc_enabled'			=> '0',
                    'nrc_sender'			=> get_option('blogname'),
                    'nrc_senderemail'		=> get_option('admin_email'),
                    'nrc_subject'			=> 'Role change notification',
                    'nrc_body'				=> '%s, your role has been just changed to %s!'
                ),
                'disable_media_comments'	=> '0',
                'show_ping_track'           => '0',
                'show_att_count'            => '0',
                'show_feat_img'             => '0',
            ),
            $this->authoring_settings
        );
    }
    
    /**
     * Register the common sidebar
     *
     * @return array
     * @since version 1.1
     */
    private function _settingsPageSidebar()
    {
        ?>
        <div class="inner-sidebar">
            <div class="postbox">
                <h3><span>Need Help?</span></h3>
                <div class="inside">
                    <img src="<?php echo site_url(); ?>/wp-content/plugins/wp-extra-settings/includes/img/mglogo_big.png" alt="by Mirko Grewing">
                    <p>Having a bad time using this plugin? You can always <a href="www.mirkogrewing.it" target="new">request for support</a>! Through the same link you can also request for <strong>new features</strong>.</p>
                    <p>Please consider also proposing new options and features.</p>
                </div>
            </div>
        </div> <!-- .inner-sidebar -->
		<?php
    }

    /**
     * Register the General tab with its settings
     *
     * @return null
     * @since version 1.0
     */
    function registerGeneralSettings()
    {
        $this->_plugin_settings_tabs[$this->_general_settings_key] = 'General';
        register_setting($this->_general_settings_key, $this->_general_settings_key);
        add_settings_section('section_general', '<span class="wpes title">General Settings</span>', array(&$this, 'sectionGeneralDesc'), $this->_general_settings_key);
        add_settings_field('wpes_enabled', '<a class="tooltip wpes" href="#">Enable WP-Extra-Settings<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Enable WP-Extra-Settings</em>Enable or temporary disable WP-Extra-Settings without activating/deactivating the plugin.</span></a>', array(&$this, 'fieldWpesEnabled'), $this->_general_settings_key, 'section_general');
    }

    /**
     * Register the Security tab with its settings
     *
     * @return null
     * @since version 1.0
     */
    function registerSecuritySettings()
    {
        $this->_plugin_settings_tabs[$this->_security_settings_key] = 'Security';
        register_setting($this->_security_settings_key, $this->_security_settings_key);
        add_settings_section('section_wpconfig', '<span class="wpes title">Security Settings</span>', array(&$this, 'sectionWpconfigDesc'), $this->_security_settings_key);
        add_settings_field('hide_wp_header_meta', '<a class="tooltip wpes" href="#">Hide WP Version in Meta<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Hide WP Version in Meta</em>Remove full WP Version from header as it could be used by remote attacker to use well-known or 0-day exploits based on the version.</span></a>', array(&$this, 'fieldHideWpHeaderMeta'), $this->_security_settings_key, 'section_wpconfig');
        add_settings_field('failed_login_message', '<a class="tooltip wpes" href="#">Hide Login Error<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Hide Login Error</em>Generalize the error message on failed login so WP won\'t disclose whether the user missed the username, the password or both.</span></a>', array( &$this, 'fieldFailedLoginMessage'), $this->_security_settings_key, 'section_wpconfig');
        add_settings_field('forbid_file_editing', '<a class="tooltip wpes" href="#">Forbid File Editing<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Forbid File Editing</em>Disable WP embedded file editor to reduce damages that remote attackers could do should they gain access to the admin.</span></a>', array(&$this, 'fieldForbidFileEditing'), $this->_security_settings_key, 'section_wpconfig');
        add_settings_field('disable_wlw_link', '<a class="tooltip wpes" href="#">Disable Live Writer Link<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Disable Live Writer Link</em>Unless you are using Windows Live Writer you can enable this option and stop disclosing you are using WordPress.</span></a>', array(&$this, 'fieldDisableWlwLink'), $this->_security_settings_key, 'section_wpconfig');
        add_settings_field('disable_rsd_services', '<a class="tooltip wpes" href="#">Disable RSD services<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Disable Really Simple Discovery services</em>This is not really a security issue as you may want to receive pingbacks, but you are also disclosing you use WordPress.</span></a>', array(&$this, 'fieldDisableRsdServices'), $this->_security_settings_key, 'section_wpconfig');
    }

    /**
     * Register the Dashboard tab with its settings
     *
     * @return null
     * @since version 1.0
     */
    function registerDashboardSettings()
    {
        $this->_plugin_settings_tabs[$this->_dashboard_settings_key] = 'Dashboard';
        register_setting($this->_dashboard_settings_key, $this->_dashboard_settings_key);
        add_settings_section('section_dashboard', '<span class="wpes title">Dashboard Settings</span>', array(&$this, 'sectionDashboardDesc'), $this->_dashboard_settings_key);
        add_settings_field('remove_msg_footer', '<a class="tooltip wpes" href="#">Remove Message from Footer<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Remove Message from Footer</em>Remove the WordPress message from the footer.</span></a>', array(&$this, 'fieldRemoveMsgFooter'), $this->_dashboard_settings_key, 'section_dashboard');
        add_settings_field('disable_version_footer', '<a class="tooltip wpes" href="#">Disable Footer WP Version<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Disable WP Version</em>Hide WP version from the footer of the Dashboard for non-administrator users.</span></a>', array(&$this, 'fieldDisableVersionFooter'), $this->_dashboard_settings_key, 'section_dashboard');
        add_settings_field('disable_upgrade_notice', '<a class="tooltip wpes" href="#">Disable Upgrade Notice<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Disable Upgrade Message</em>Disable the upgrade message from the Dashboard</span></a>', array(&$this, 'fieldDisableUpgradeNotice'), $this->_dashboard_settings_key, 'section_dashboard');
        add_settings_field('disable_admin_bar', '<a class="tooltip wpes" href="#">Disable Admin Bar<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Disable Admin Bar</em>Hide the admin bar for all users except administrators.</span></a>', array(&$this, 'fieldDisableAdminBar'), $this->_dashboard_settings_key, 'section_dashboard');
        add_settings_field('hide_wp_logo', '<a class="tooltip wpes" href="#">Hide WP Logo<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Hide WP Logo</em>Hide WP logo from the admin bar.</span></a>', array(&$this, 'fieldHideWpLogo'), $this->_dashboard_settings_key, 'section_dashboard');
        add_settings_field('hide_meta_boxes', '<a class="tooltip wpes" href="#">Hide Meta Boxes<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Hide Meta Boxes</em>Hide the meta boxes from the Dashboard homepage.</span></a>', array(&$this, 'fieldHideMetaBoxes'), $this->_dashboard_settings_key, 'section_dashboard');
        add_settings_field('show_admin_msg', '<a class="tooltip wpes" href="#">Show a message in the Dashboard<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Show a message in the Dashboard.</em>This will give you the chance to show a boxed notification message in the Dashboard.</span></a>', array(&$this, 'fieldShowAdminMsg'), $this->_dashboard_settings_key, 'section_dashboard');
        add_settings_field('hide_help_tab', '<a class="tooltip wpes" href="#">Hide the Help from the Dashboard<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Hide the Help Tab from the Dashboard.</em>Enable if you want to hide the Help tab from the top right corner of the Dashboard.</span></a>', array(&$this, 'fieldHideHelpTab'), $this->_dashboard_settings_key, 'section_dashboard');
    }

    /**
     * Register the Authoring tab with its settings
     *
     * @return null
     * @since version 1.0
     */
    function registerAuthoringSettings()
    {
        $this->_plugin_settings_tabs[$this->_authoring_settings_key] = 'Authoring';
        register_setting($this->_authoring_settings_key, $this->_authoring_settings_key);
        add_settings_section('section_authoring', '<span class="wpes title">Authoring Settings</span>', array(&$this, 'sectionAuthoringDesc'), $this->_authoring_settings_key);
        add_settings_field('own_posts_only', '<a class="tooltip wpes" href="#">Own Posts Only<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Show Own Posts Only</em>If enabled contributors will not be able to see posts other than their own.</span></a>', array(&$this, 'fieldOwnPostsOnly'), $this->_authoring_settings_key, 'section_authoring');
        add_settings_field('allow_media_upload', '<a class="tooltip wpes" href="#">Allow Media Upload<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Allow Media Upload</em>If enabled contributors will be able to upload media files.</span></a>', array(&$this, 'fieldAllowMediaUpload'), $this->_authoring_settings_key, 'section_authoring');
        add_settings_field('notify_role_change', '<a class="tooltip wpes" href="#">Notify Role Changes<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Notify Role Changes to Users</em>If enabled users will be notified when their role is modified by any admin.</span></a>', array(&$this, 'fieldNotifyRoleChange'), $this->_authoring_settings_key, 'section_authoring');
        add_settings_field('disable_media_comments', '<a class="tooltip wpes" href="#">Disable Comments on Media<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Disable Comments in Attachment Pages</em>If checked visitors won\'t be allowed to add comments in attachment pages.</span></a>', array(&$this, 'fieldDisableMediaComments'), $this->_authoring_settings_key, 'section_authoring');
        add_settings_field('show_ping_track', '<a class="tooltip wpes" href="#">Show Ping/Trackback Count<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Show Pingback and Trackback Count</em>Show the number of pingbacks and trackback per post in the post list.</span></a>', array(&$this, 'fieldShowPingTrack'), $this->_authoring_settings_key, 'section_authoring');
        add_settings_field('show_att_count', '<a class="tooltip wpes" href="#">Show Attachments Count<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Show Attachments Count</em>Show the number of attachments per post in the post list.</span></a>', array(&$this, 'fieldShowAttCount'), $this->_authoring_settings_key, 'section_authoring');
        add_settings_field('show_feat_img', '<a class="tooltip wpes" href="#">Show Featured Image Thumbnail<span class="custom help"><img src="'. site_url() .'/wp-content/plugins/wp-extra-settings/includes/img/Help.png" alt="Help" height="48" width="48" /><em>Show Featured Image Thumbnail</em>Show the featured image thumbnail in post list view.</span></a>', array(&$this, 'fieldShowFeatImg'), $this->_authoring_settings_key, 'section_authoring');
    }

    /**
     * Provide a description for the General tab
     *
     * @return array
     * @since version 1.0
     */
    function sectionGeneralDesc()
    {
        echo '<span class="wpes">General settings and information about WP-Extra-Settings.</span><br /><br />';
    }

    /**
     * Provide a description for the Security tab
     *
     * @return array
     * @since version 1.0
     */
    function sectionWpconfigDesc()
    {
        echo '<span class="wpes">Improve the security of your WordPress removing information and features that could be used by remote attackers.</span><br /><br />';
    }

    /**
     * Provide a description for the Dashboard tab
     *
     * @return array
     * @since version 1.0
     */
    function sectionDashboardDesc()
    {
        echo '<span class="wpes">Hide and remove WP related information from the Dashboard.</span><br /><br />';
    }

    /**
     * Provide a description for the Authoring tab
     *
     * @return array
     * @since version 1.0
     */
    function sectionAuthoringDesc()
    {
        echo '<span class="wpes">Improve WordPress as a professional authoring tool.</span><br /><br />';
    }

    /**
     * General Options Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldWpesEnabled()
    {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" id="<?php echo $this->_general_settings_key; ?>[wpes_enabled]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_general_settings_key; ?>[wpes_enabled]" <?php if ($this->general_settings['wpes_enabled'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_general_settings_key; ?>[wpes_enabled]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Security Option hide_wp_header_meta Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldHideWpHeaderMeta()
    {
        ?>
        <div class="onoffswitch">
            <input id="<?php echo $this->_general_settings_key; ?>[hide_wp_header_meta]" class="onoffswitch-checkbox" type="checkbox" value ="1" name="<?php echo $this->_security_settings_key; ?>[hide_wp_header_meta]" <?php if ($this->security_settings['hide_wp_header_meta'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_general_settings_key; ?>[hide_wp_header_meta]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Security Option failed_login_message Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldFailedLoginMessage()
    {
        ?>
        <div class="onoffswitch">
            <input id="<?php echo $this->_security_settings_key; ?>[failed_login_message]" class="onoffswitch-checkbox" type="checkbox" value ="1" name="<?php echo $this->_security_settings_key; ?>[failed_login_message]" <?php if ($this->security_settings['failed_login_message'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_security_settings_key; ?>[failed_login_message]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Security Option forbid_file_editing Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldForbidFileEditing()
    {
        ?>
        <div class="onoffswitch">
            <input id="<?php echo $this->_security_settings_key; ?>[forbid_file_editing]" class="onoffswitch-checkbox" type="checkbox" value ="1" name="<?php echo $this->_security_settings_key; ?>[forbid_file_editing]" <?php if ($this->security_settings['forbid_file_editing'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_security_settings_key; ?>[forbid_file_editing]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Security Option disable_wlw_link Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldDisableWlwLink()
    {
        ?>
        <div class="onoffswitch">
            <input id="<?php echo $this->_security_settings_key; ?>[disable_wlw_link]" class="onoffswitch-checkbox" type="checkbox" value ="1" name="<?php echo $this->_security_settings_key; ?>[disable_wlw_link]" <?php if ($this->security_settings['disable_wlw_link'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_security_settings_key; ?>[disable_wlw_link]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Security Option disable_rsd_services Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldDisableRsdServices()
    {
        ?>
        <div class="onoffswitch">
            <input id="<?php echo $this->_security_settings_key; ?>[disable_rsd_services]" class="onoffswitch-checkbox" type="checkbox" value ="1" name="<?php echo $this->_security_settings_key; ?>[disable_rsd_services]" <?php if ($this->security_settings['disable_rsd_services'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_security_settings_key; ?>[disable_rsd_services]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Dashboard Option remove_msg_footer Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldRemoveMsgFooter()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_dashboard_settings_key; ?>[remove_msg_footer]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_dashboard_settings_key; ?>[remove_msg_footer]" <?php if ($this->dashboard_settings['remove_msg_footer'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_dashboard_settings_key; ?>[remove_msg_footer]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Dashboard Option disable_version_footer Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldDisableVersionFooter()
    {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" id="<?php echo $this->_dashboard_settings_key; ?>[disable_version_footer]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_dashboard_settings_key; ?>[disable_version_footer]" <?php if ($this->dashboard_settings['disable_version_footer'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_dashboard_settings_key; ?>[disable_version_footer]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Dashboard Option disable_upgrade_notice Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldDisableUpgradeNotice()
    {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" id="<?php echo $this->_dashboard_settings_key; ?>[disable_upgrade_notice]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_dashboard_settings_key; ?>[disable_upgrade_notice]" <?php if ($this->dashboard_settings['disable_upgrade_notice'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_dashboard_settings_key; ?>[disable_upgrade_notice]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Dashboard Option disable_admin_bar Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldDisableAdminBar()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_dashboard_settings_key; ?>[disable_admin_bar]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_dashboard_settings_key; ?>[disable_admin_bar]" <?php if ($this->dashboard_settings['disable_admin_bar'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_dashboard_settings_key; ?>[disable_admin_bar]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Dashboard Option hide_wp_logo Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldHideWpLogo()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_dashboard_settings_key; ?>[hide_wp_logo]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_dashboard_settings_key; ?>[hide_wp_logo]" <?php if ($this->dashboard_settings['hide_wp_logo'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_dashboard_settings_key; ?>[hide_wp_logo]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Dashboard Option hide_meta_boxes Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldHideMetaBoxes()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_dashboard_settings_key; ?>[hide_meta_boxes]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_dashboard_settings_key; ?>[hide_meta_boxes]" <?php if ($this->dashboard_settings['hide_meta_boxes'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_dashboard_settings_key; ?>[hide_meta_boxes]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Dashboard Option hide_meta_boxes Callback
     *
     * @return string
     * @since version 1.2
     */
    function fieldShowAdminMsg()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_dashboard_settings_key; ?>[show_admin_msg][sam_enabled]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_dashboard_settings_key; ?>[show_admin_msg][sam_enabled]" <?php if ($this->dashboard_settings['show_admin_msg']['sam_enabled'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_dashboard_settings_key; ?>[show_admin_msg][sam_enabled]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <br>
        <div class="radiobuttons">
            <input type="radio" id="radio1" name="<?php echo $this->_dashboard_settings_key; ?>[show_admin_msg][sam_type]" value="updated" <?php if ($this->dashboard_settings['show_admin_msg']['sam_type'] == 'updated') : echo " checked=checked"; endif; ?>>
            <label for="radio1">Green</label>
            <input type="radio" id="radio2" name="<?php echo $this->_dashboard_settings_key; ?>[show_admin_msg][sam_type]" value="error" <?php if ($this->dashboard_settings['show_admin_msg']['sam_type'] == 'error') : echo " checked=checked"; endif; ?>>
            <label for="radio2">Red</label> 
        </div>
        <div class="text-field">
            <lable class="wp-pro-form-label" for="<?php echo $this->_dashboard_settings_key; ?>[show_admin_msg][sam_message]">Message: </lable>
            <textarea id="<?php echo $this->_dashboard_settings_key; ?>[show_admin_msg][sam_message]" class="wp-pro-form" rows="8" name="<?php echo $this->_dashboard_settings_key; ?>[show_admin_msg][sam_message]" /><?php if ($this->dashboard_settings['show_admin_msg']['sam_message'] == '') : echo "Type your message here!"; else: echo($this->dashboard_settings['show_admin_msg']['sam_message']); endif; ?></textarea>
            <span class="comment"></span>
        </div>
        <?php
    }

    /**
     * Dashboard Option hideHelp_tab Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldHideHelpTab()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_dashboard_settings_key; ?>[hide_help_tab]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_dashboard_settings_key; ?>[hide_help_tab]" <?php if ($this->dashboard_settings['hide_help_tab'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_dashboard_settings_key; ?>[hide_help_tab]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Authoring Option own_posts_only Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldOwnPostsOnly()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_authoring_settings_key; ?>[own_posts_only]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_authoring_settings_key; ?>[own_posts_only]" <?php if ($this->authoring_settings['own_posts_only'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_authoring_settings_key; ?>[own_posts_only]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Authoring Option allow_media_upload Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldAllowMediaUpload()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_authoring_settings_key; ?>[allow_media_upload]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_authoring_settings_key; ?>[allow_media_upload]" <?php if ($this->authoring_settings['allow_media_upload'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_authoring_settings_key; ?>[allow_media_upload]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Authoring Option notify_role_change Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldNotifyRoleChange()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_enabled]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_enabled]" <?php if ($this->authoring_settings['notify_role_change']['nrc_enabled'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_enabled]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <br>
        <div class="text-field">
            <lable class="wp-pro-form-label" for="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_sender]">Sender Name: </lable>
            <input type="text" id="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_sender]" class="wp-pro-form" name="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_sender]" value="<?php if ($this->authoring_settings['notify_role_change']['nrc_sender'] == '') : echo get_option('blogname'); else: echo ($this->authoring_settings['notify_role_change']['nrc_sender']); endif; ?>" />
        </div>
        <div class="text-field">
            <lable class="wp-pro-form-label" for="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_senderemail]">Sender Email: </lable>
            <input type="text" id="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_senderemail]" class="wp-pro-form" name="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_senderemail]" value="<?php if ($this->authoring_settings['notify_role_change']['nrc_senderemail'] == '') : echo get_option('admin_email'); else: echo ($this->authoring_settings['notify_role_change']['nrc_senderemail']); endif; ?>" />
        </div>
        <div class="text-field">
            <lable class="wp-pro-form-label" for="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_subject]">Subject: </lable>
            <input type="text" id="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_subject]" class="wp-pro-form" name="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_subject]" value="<?php if ($this->authoring_settings['notify_role_change']['nrc_subject'] == '') : echo "Role change notification"; else: echo ($this->authoring_settings['notify_role_change']['nrc_subject']); endif; ?>" />
        </div>
        <div class="text-field">
            <lable class="wp-pro-form-label" for="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_body]">Message: </lable>
            <textarea id="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_body]" class="wp-pro-form" rows="8" name="<?php echo $this->_authoring_settings_key; ?>[notify_role_change][nrc_body]" /><?php if ($this->authoring_settings['notify_role_change']['nrc_body'] == '') : echo "Dear %s,\r\nYour role has been just changed to %s!\r\nRegards"; else: echo($this->authoring_settings['notify_role_change']['nrc_body']); endif; ?></textarea>
            <span class="comment">Please consider that the first %s will print the user name, the second %s will print the assigned role. HTML is <u>not</u> supported.</span>
        </div>
        <?php
    }

    /**
     * Authoring Option disable_media_comments Callback
     *
     * @return string
     * @since version 1.1
     */
    function fieldDisableMediaComments()
    {
        ?>
        <div class="onoffswitch">
            <input type="checkbox" id="<?php echo $this->_authoring_settings_key; ?>[disable_media_comments]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_authoring_settings_key; ?>[disable_media_comments]" <?php if ($this->authoring_settings['disable_media_comments'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_authoring_settings_key; ?>[disable_media_comments]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Authoring Option show_ping_track Callback
     *
     * @return string
     * @since version 1.2
     */
    function fieldShowPingTrack()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_authoring_settings_key; ?>[show_ping_track]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_authoring_settings_key; ?>[show_ping_track]" <?php if ($this->authoring_settings['show_ping_track'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_authoring_settings_key; ?>[show_ping_track]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Authoring Option show_att_count Callback
     *
     * @return string
     * @since version 1.2
     */
    function fieldShowAttCount()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_authoring_settings_key; ?>[show_att_count]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_authoring_settings_key; ?>[show_att_count]" <?php if ($this->authoring_settings['show_att_count'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_authoring_settings_key; ?>[show_att_count]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Authoring Option show_feat_img Callback
     *
     * @return string
     * @since version 1.0
     */
    function fieldShowFeatImg()
    {
        ?>
        <div class="onoffswitch">	
            <input type="checkbox" id="<?php echo $this->_authoring_settings_key; ?>[show_feat_img]" class="onoffswitch-checkbox" value ="1" name="<?php echo $this->_authoring_settings_key; ?>[show_feat_img]" <?php if ($this->authoring_settings['show_feat_img'] == '1') : echo " checked=checked"; endif; ?> />
            <label class="onoffswitch-label" for="<?php echo $this->_authoring_settings_key; ?>[show_feat_img]">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
            </label>
        </div>
        <?php
    }

    /**
     * Add Menu Page in Settings block
     *
     * @return null
     * @since version 1.0
     */
    function addAdminMenus()
    {
        add_options_page('WP-Extra-Settings', 'WP-Extra-Settings', 'manage_options', $this->_plugin_options_key, array( &$this, 'pluginOptionsPage' ));
        add_action('load-options-general.php?page=mg_wpes_options', 'admin_add_help_tab');
    }

    /**
     * Organize the options in tabs
     *
     * @return null
     * @since version 1.0
     */
    function pluginOptionsPage()
    {
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->_general_settings_key;
        ?>
        <div class="wrap">
        <?php $this->pluginOptionsTabs(); ?>
            <div class="metabox-holder has-right-sidebar">
            <?php $this->_settingsPageSidebar(); ?>
            </div>
            <div id="post-body" style="float: left">
                <form method="post" action="options.php">
                    <?php wp_nonce_field('update-options'); ?>
                    <?php settings_fields($tab); ?>
                    <?php do_settings_sections($tab); ?>
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render the tabs
     *
     * @return null
     * @since version 1.0
     */
    function pluginOptionsTabs()
    {
        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->_general_settings_key;
        screen_icon();
        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $this->_plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->_plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
        }
        echo '</h2>';
    }

    /**
     * Run all the actions associated to the enabled options
     *
     * @return null
     */
    function pluginInit()
    {
        if ($this->general_settings['wpes_enabled'] == '1') {
            /* Hide the WP version from the header */
            if ($this->security_settings['hide_wp_header_meta'] == '1') {
                /**
                 * Return an empty string for the the meta generator
                 *
                 * @return string
                 */
                function removeVersion()
                {
                    return '';
                }
                add_filter('the_generator', 'removeVersion');
            }

            /* Generalize the error message upon login failed */
            if ($this->security_settings['failed_login_message'] == '1') {
                /**
                 * Change the message shown upon failed login
                 *
                 * @return string
                 */
                function wrongLogin()
                {
                    return 'Wrong username or password.';
                }	
                add_filter('login_errors', 'wrongLogin');
            }

            /* Forbid file editing in the dashboard */
            if ($this->security_settings['forbid_file_editing'] == '1')
                define('DISALLOW_FILE_EDIT', true);
            
            /* Remove Windows Live Writer link from header */
            if ($this->security_settings['disable_wlw_link'] == '1')
                remove_action('wp_head', 'wlwmanifest_link');

            /* Remove Really Simple Discovery link from header */
            if ($this->security_settings['disable_rsd_services'] == '1')
                remove_action('wp_head', 'rsd_link');

            /* Remove the whole footer for non administrator users */
            if ($this->dashboard_settings['remove_msg_footer'] == '1') {
                /**
                 * Return an empty string for the whole footer                
                 *
                 * @return string
                 */
                function removeFooterAdmin()
                {
                    if (!current_user_can('update_core')) {
                        echo '';
                    }
                }
                add_filter('admin_footer_text', 'removeFooterAdmin');
            }

            /* Remove WP Version from the footer of the Dashboard for not-administrator users */
            if ($this->dashboard_settings['disable_version_footer'] == '1') {
                /**
                 * Return either the version or an empty string
                 *
                 * @param string $upgrade The version of WordPress
                 *
                 * @return string
                 */
                function noVersionFooter($upgrade)
                {
                    if (!current_user_can('update_core')) {
                        echo '';
                    } else {
                        return $upgrade;
                    }
                }
                add_filter('update_footer', 'noVersionFooter', 100);
            }

            /* Remove the upgrade message from the Dashboard */
            if ($this->dashboard_settings['disable_upgrade_notice'] == '1') {
                /**
                 * Disable nag
                 *
                 * @return bool
                 */
                function disableNag()
                {
                    remove_action('admin_notices', 'update_nag', 3);
                }
                add_action('admin_menu', 'disableNag');
            }

            /* Remove the admin bar from the Dashboard for non-administrators */
            if ($this->dashboard_settings['disable_admin_bar'] == '1' && !current_user_can('administrator') && !is_admin()) {
                add_filter('show_admin_bar', '__return_false');
            }

            /* Hide WP Logo from the Admin Bar */
            if ($this->dashboard_settings['hide_wp_logo'] == '1') {
                /**
                 * Hide WP Logo From the Admin Bar
                 *
                 * @return string
                 */
                function hideLogo()
                {
                    echo '
                        <style type="text/css">
                            #wp-admin-bar-wp-logo { display:none !important; }
                        </style>
                    ';
                }
                add_action('admin_head', 'hideLogo');
            }

            /* Hide Meta Boxes from the Dashboard homepage */
            if ($this->dashboard_settings['hide_meta_boxes'] == '1') {
                /**
                 * Prevent users from seeing other users' posts
                 *
                 * @return array
                 */
                function removeDashboardWidgets()
                {
                    global $wp_meta_boxes;
                    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
                    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
                    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
                    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
                    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
                    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
                    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
                    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
                }
                add_action('wp_dashboard_setup', 'removeDashboardWidgets');
            }
            
            /* Hide Help Tab from the Dashboard */
            if ($this->dashboard_settings['hide_help_tab'] == '1') {
                /**
                 * Add a style to hide the Help Tab
                 *
                 * @return string
                 */
                function hideHelp()
                {
                    echo '
                        <style type="text/css">
                        #contextual-help-link-wrap {display:none!important;}
                        </style>
                    ';
                }
                add_action('admin_head', 'hideHelp');
            }

            /* Disallow Contributors to see other users' posts */
            if ($this->authoring_settings['own_posts_only'] == '1') {
                /**
                 * Prevent users from seeing other users' posts
                 *
                 * @param string $wp_query Either empty or the current user id
                 *
                 * @return string
                 */
                function querySetOnlyAuthor($wp_query)
                {
                    global $current_user;
                    if (is_admin() && !current_user_can('edit_others_posts')) {
                        $wp_query->set('author', $current_user->ID);
                        add_filter('views_edit-post', 'fixPostCounts');
                        add_filter('views_upload', 'fixMediaCounts');
                    }
                }
                add_action('pre_get_posts', 'querySetOnlyAuthor');

                /**
                 * Fix the post counter for the Contributor to see just the total of his own posts
                 *
                 * @param array $views The view of the post list
                 *
                 * @return array
                 */
                function fixPostCounts($views)
                {
                    global $current_user, $wp_query;
                    unset($views['mine']);
                    $types = array(
                        array( 'status' =>  null ),
                        array( 'status' => 'publish' ),
                        array( 'status' => 'draft' ),
                        array( 'status' => 'pending' ),
                        array( 'status' => 'trash' )
                    );
                    foreach ($types as $type) {
                        $query = array(
                            'author'      => $current_user->ID,
                            'post_type'   => 'post',
                            'post_status' => $type['status']
                        );
                        $result = new WP_Query($query);
                        if ($type['status'] == null) : $class = ($wp_query->query_vars['post_status'] == null) ? ' class="current"' : '';
                            $views['all'] = sprintf(__('<a href="%s"'. $class .'>All <span class="count">(%d)</span></a>', 'all'), admin_url('edit.php?post_type=post'), $result->found_posts);
                        elseif ($type['status'] == 'publish') : $class = ($wp_query->query_vars['post_status'] == 'publish') ? ' class="current"' : '';
                            $views['publish'] = sprintf(__('<a href="%s"'. $class .'>Published <span class="count">(%d)</span></a>', 'publish'), admin_url('edit.php?post_status=publish&post_type=post'), $result->found_posts);
                        elseif ($type['status'] == 'draft') : $class = ($wp_query->query_vars['post_status'] == 'draft') ? ' class="current"' : '';
                            $views['draft'] = sprintf(__('<a href="%s"'. $class .'>Draft'. ((sizeof($result->posts) > 1) ? "s" : "") .' <span class="count">(%d)</span></a>', 'draft'), admin_url('edit.php?post_status=draft&post_type=post'), $result->found_posts);
                        elseif ($type['status'] == 'pending') : $class = ($wp_query->query_vars['post_status'] == 'pending') ? ' class="current"' : '';
                            $views['pending'] = sprintf(__('<a href="%s"'. $class .'>Pending <span class="count">(%d)</span></a>', 'pending'), admin_url('edit.php?post_status=pending&post_type=post'), $result->found_posts);
                        elseif ($type['status'] == 'trash') : $class = ($wp_query->query_vars['post_status'] == 'trash') ? ' class="current"' : '';
                            $views['trash'] = sprintf(__('<a href="%s"'. $class .'>Trash <span class="count">(%d)</span></a>', 'trash'), admin_url('edit.php?post_status=trash&post_type=post'), $result->found_posts);
                        endif;
                    }
                    return $views;
                }

                /**
                 * Fix the media counter for the Contributor to see just the total of his own media files
                 *
                 * @param array $views The view of the post list
                 *
                 * @return array
                 */
                function fixMediaCounts($views)
                {
                    global $wpdb, $current_user, $post_mime_types, $avail_post_mime_types;
                    $views = array();
                    $count = $wpdb->get_results(
                        "SELECT post_mime_type, COUNT( * ) AS num_posts FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author = $current_user->ID AND post_status != 'trash' GROUP BY post_mime_type", ARRAY_A
                    );
                    foreach ($count as $row)
                        $_num_posts[$row['post_mime_type']] = $row['num_posts'];
                    $_total_posts = array_sum($_num_posts);
                    $detached = isset( $_REQUEST['detached'] ) || isset( $_REQUEST['find_detached'] );
                    if (!isset( $total_orphans ))
                        $total_orphans = $wpdb->get_var(
                            "SELECT COUNT( * ) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author = $current_user->ID AND post_status != 'trash' AND post_parent < 1"
                        );
                    $matches = wp_match_mime_types(array_keys($post_mime_types), array_keys($_num_posts));
                    foreach ($matches as $type => $reals)
                        foreach ($reals as $real)
                            $num_posts[$type] = ( isset( $num_posts[$type] ) ) ? $num_posts[$type] + $_num_posts[$real] : $_num_posts[$real];
                    $class = ( empty($_GET['post_mime_type']) && !$detached && !isset($_GET['status']) ) ? ' class="current"' : '';
                    $views['all'] = "<a href='upload.php'$class>" . sprintf(__('All <span class="count">(%s)</span>', 'uploaded files'), number_format_i18n($_total_posts)) . '</a>';
                    foreach ($post_mime_types as $mime_type => $label) {
                        $class = '';
                        if (!wp_match_mime_types($mime_type, $avail_post_mime_types))
                            continue;
                        if (!empty($_GET['post_mime_type']) && wp_match_mime_types($mime_type, $_GET['post_mime_type']))
                            $class = ' class="current"';
                        if (!empty( $num_posts[$mime_type]))
                            $views[$mime_type] = "<a href='upload.php?post_mime_type=$mime_type'$class>" . sprintf(translate_nooped_plural($label[2], $num_posts[$mime_type]), $num_posts[$mime_type]) . '</a>';
                    }
                        $views['detached'] = '<a href="upload.php?detached=1"' . ($detached ? ' class="current"' : '') . '>' . sprintf(__('Unattached <span class="count">(%s)</span>', 'detached files'), $total_orphans) . '</a>';
                        return $views;
                }
            }

            /* Allow Contributors to Upload Media */
            if ($this->authoring_settings['allow_media_upload'] == '1') {
                if (current_user_can('contributor') && !current_user_can('upload_files'))
                    add_action('admin_init', 'allowContributorUploads');
                /**
                 * Add capacity upload_files to contributors 
                 *
                 * @return string
                 */
                function allowContributorUploads()
                {
                    $contributor = get_role('contributor');
                    $contributor->add_cap('upload_files');
                }
            }
            
            /* Notify Role Change to Users */
            if ($this->authoring_settings['notify_role_change']['nrc_enabled'] == '1') {
                /**
                 * Send an email to a user whose role has been just changed 
                 *
                 * @param int    $user_id  The user ID
                 * @param string $new_role The new role
                 *
                 * @return array
                 */
                function userRoleUpdate($user_id, $new_role)
                {
                    $options = get_option('mg_authoring_settings');
                    $site_url = get_bloginfo('wpurl');
                    $user_info = get_userdata($user_id);
                    $to = $user_info->user_email;
                    $subject = $options['notify_role_change']['nrc_subject']; 
                    $message = sprintf($options['notify_role_change']['nrc_body'], $user_info->display_name, $new_role);
                    $headers = 'From: '. $options['notify_role_change']['nrc_sender'] .' <'. $options['notify_role_change']['nrc_senderemail'] .'>' . "\r\n";
                    wp_mail($to, $subject, $message, $headers);
                }
                add_action('set_user_role', 'userRoleUpdate', 10, 2);
            }

            /* Disable Comments in Attachment Pages */
            if ($this->authoring_settings['disable_media_comments'] == '1') {
                /**
                 * Disable comments for post type attachment 
                 *
                 * @param bool $open    Set whether comments are open or not
                 * @param int  $post_id The post id
                 *
                 * @return boolean
                 */
                function filterMediaCommentStatus($open, $post_id)
                {
                    $post = get_post($post_id);
                    if ($post->post_type == 'attachment') {
                        return false;
                    }
                    return $open;
                }
                add_filter('comments_open', 'filterMediaCommentStatus', 10, 2);
            }

            /* Show custom notification message in the dashboard */
            if ($this->dashboard_settings['show_admin_msg']['sam_enabled'] == '1') {
                ?><div class="<?php echo $this->dashboard_settings['show_admin_msg']['sam_type']; ?>">
    			         <p><?php echo $this->dashboard_settings['show_admin_msg']['sam_message']; ?></p>
                </div><?php
            }

            /* Show Count of Pingbacks and Trackbacks in the post list */
            if ($this->authoring_settings['show_ping_track'] == '1') {
                /**
                 * Count the comments
                 *
                 * @param string $type What has to be counted 
                 *
                 * @return string
                 */
                function commentCount($type = 'comments')
                {
                    if ($type == 'trackbacks') :
                        $typeSql = 'comment_type = "trackback"';
                        $oneText = 'One: trackback';
                        $moreText = '%: trackbacks';
                        $noneText = 'No: trackbacks';
                    elseif ($type == 'pingbacks') :
                        $typeSql = 'comment_type = "pingback"';
                        $oneText = 'One: pingback';
                        $moreText = '%: pingbacks';
                        $noneText = 'No: pingbacks';
                    endif;
                    global $wpdb;
                    $result = $wpdb->get_var(
                        'SELECT COUNT(comment_ID) FROM '.$wpdb->comments.' WHERE '.$typeSql.' AND comment_approved="1" AND comment_post_ID= '.get_the_ID()
                    );
                    if ($result == 0) :
                        echo str_replace('%', $result, $noneText);
                    elseif ($result == 1) :
                        echo str_replace('%', $result, $oneText);
                    elseif ($result > 1) :
                        echo str_replace('%', $result, $moreText);
                    endif;
                }
                add_filter('manage_posts_columns', 'postsColumnsCounts', 1);
                add_action('manage_posts_custom_column', 'postsCustomColumnsCounts', 1, 2);
                /**
                 * Add the column
                 *
                 * @param string $defaults Default count 
                 *
                 * @return string
                 */
                function postsColumnsCounts($defaults)
                {
                    $defaults['wps_post_counts'] = __('Counts');
                    return $defaults;
                }
                /**
                 * Fill the counters for every post
                 *
                 * @param string $column_name Name of the column
                 * @param int    $id          ID of the post 
                 *
                 * @return string
                 */
                function postsCustomColumnsCounts($column_name, $id)
                {
                    if ($column_name === 'wps_post_counts') {
                        commentCount('trackbacks'); echo "<br>
                        ";
                        commentCount('pingbacks');
                    }
                }
            }

            /* Show number of attachments per post in the post list view */
            if ($this->authoring_settings['show_att_count'] == '1') {
                add_filter('manage_posts_columns', 'postsColumnsAttachmentCount', 5);
                add_action('manage_posts_custom_column', 'postsCustomColumnsAttachmentCount', 5, 2);
                /**
                 * Add a column for the extra field in post list
                 *
                 * @param string $defaults Name of the column
                 *
                 * @return string
                 */
                function postsColumnsAttachmentCount($defaults)
                {
                    $defaults['wps_post_attachments'] = __('Media');
                    return $defaults;
                }
                /**
                 * Fill the counter for every post
                 *
                 * @param string $column_name Name of the column
                 * @param int    $id          ID of the post
                 *
                 * @return string
                 */
                function postsCustomColumnsAttachmentCount($column_name, $id)
                {
                    if ($column_name === 'wps_post_attachments') {
                        $attachments = get_children(array('post_parent'=>$id));
                        $count = count($attachments);
                        if ($count !=0) {
                            echo $count;
                        }
                    }
                }
            }

            /* Show thumbnail of featured image in post list view */
            if ($this->authoring_settings['show_feat_img'] == '1') {
                add_image_size('admin-list-thumb', 100, 70, true);
                add_filter('manage_posts_columns', 'addPostThumbnailColumn', 5);
                /**
                 * Add a column for the extra field in post list
                 *
                 * @param string $cols Name of the column
                 *
                 * @return string
                 */
                function addPostThumbnailColumn($cols)
                {
                    $cols['wpes_post_thumb'] = __('Featured');
                    return $cols;
                }
                add_action('manage_posts_custom_column', 'displayPostThumbnailColumn', 5, 2);
                /**
                 * Fill the counter for ever post
                 *
                 * @param string $col Name of the column
                 * @param int    $id  Post id
                 *
                 * @return string
                 */
                function displayPostThumbnailColumn($col, $id)
                {
                    switch($col){
                    case 'wpes_post_thumb':
                        if (function_exists('the_post_thumbnail'))
                            echo the_post_thumbnail('admin-list-thumb');
                        else
                            echo 'Not supported in theme';
                        break;
                    }
                }
            }
        }
    }
}

/* Initialize the plugin */
add_action('plugins_loaded', create_function('', '$mg_wpes_settings = new MG_WPES_Settings;'));

?>