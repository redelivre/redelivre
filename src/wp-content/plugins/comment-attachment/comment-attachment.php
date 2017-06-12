<?php
/*
    Plugin Name: Comment Attachment
    Plugin URI: http://latorante.name
    Description: Wordpress out-of-the-box comment attachment functionality. Offer your visitors the ability to attach images, or documents to their comments that automatically attach to your Wordpress media gallery. Make the attachments visible, downloadable as you wish.
    Author: latorante
    Author URI: http://latorante.name
    Author Email: martin@latorante.name
    Version: 1.5.5
    License: GPLv2
	Text Domain: comment-attachment
	Domain Path: /languages/
*/
/*
    Copyright 2013  Martin Picha  (email : martin@latorante.name)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * 1. If no Wordpress, go home
 */

if (!defined('ABSPATH')) { exit; }

/**
 * 2. Check minimum requirements (wp version, php version)
 * Reason behind this is, we just need PHP 5.3 at least,
 * and wordpress 3.3 or higher. We just can't run the show
 * on some outdated installation.
 */

require_once('check.php');
reqCheck::checkRequirements();

/**
 * 3. Go, and do Comment Attachment!
 */

require_once('comment-attachment-init.php');