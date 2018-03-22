<?php
/**
 * This file is part of the Comment Attachment plugin.
 *
 * Copyright (c) 2013 Martin Pícha (http://latorante.name)
 *
 * For the full copyright and license information, please view
 * the comment-attachment.php file in root directory of this plugin.
 */

if (!defined('ABSPATH')) { exit; }

class reqCheck
{
    /**
     * Let's check Wordpress version, and PHP version and tell those
     * guys whats needed to upgrade, if anything.
     *
     * @return bool
     */

    public static function checkRequirements()
    {
        global $wp_version;
        $wp_version_min  = '3.0';
        $php = '5.3';
        $recoverUrl = admin_url('plugins.php');
        $recoverLink = '<br /><br /><a href="'. $recoverUrl .'">Back to plugins.</a>';
        if (!version_compare($wp_version, $wp_version_min, '>=')){
            reqCheck::pluginDeactivate(
                'Sorry mate, this plugin requires at least WordPress varsion <strong>' . $wp_version_min . ' or higher.</strong> You are currently using <strong>' . $wp_version . '.</strong> Please upgrade your WordPress.' . $recoverLink
            );
        } elseif (!version_compare(PHP_VERSION, $php, '>=')){
            reqCheck::pluginDeactivate(
                'You need PHP version at least <strong>'. $php .'</strong> to run this plugin. You are currently using PHP version <strong>' . PHP_VERSION . '.</strong>' . $recoverLink
            );
        }
    }


    /**
     * Deactivates our plugin if anything goes wrong. Also, removes the
     * "Plugin activated" message, if we don't pass requriments check.
     */

    public static function pluginDeactivate($message)
    {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        deactivate_plugins('comment-attachment/comment-attachment.php');
        unset($_GET['activate']);
        wp_die($message);
    }
}