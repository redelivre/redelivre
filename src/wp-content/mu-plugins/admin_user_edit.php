<?php
function rl_admin_users_caps( $caps, $cap, $user_id, $args ){

	foreach( $caps as $key => $capability ){

		if( $capability != 'do_not_allow' )
			continue;

		switch( $cap ) {
			case 'edit_user':
			case 'edit_users':
				$caps[$key] = 'edit_users';
				break;
			case 'delete_user':
			case 'delete_users':
				$caps[$key] = 'delete_users';
				break;
			case 'create_users':
				$caps[$key] = $cap;
				break;
		}
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'rl_admin_users_caps', 1, 4 );
remove_all_filters( 'enable_edit_any_user_configuration' );
add_filter( 'enable_edit_any_user_configuration', '__return_true');

/**
 * Checks that both the editing user and the user being edited are
 * members of the blog and prevents the super admin being edited.
 */
function rl_edit_permission_check() {
	global $current_user, $profileuser;

	$screen = get_current_screen();

	get_currentuserinfo();

	if( ! is_super_admin( $current_user->ID ) && in_array( $screen->base, array( 'user-edit', 'user-edit-network' ) ) ) // editing a user profile
	{
		if ( is_super_admin( $profileuser->ID ) ) // trying to edit a superadmin while less than a superadmin
		{
			wp_die( __( 'You do not have permission to edit this user.' ) );
		}
		elseif ( ! ( is_user_member_of_blog( $profileuser->ID, get_current_blog_id() ) && is_user_member_of_blog( $current_user->ID, get_current_blog_id() ) )) // editing user and edited user aren't members of the same blog
		{
			wp_die( __( 'You do not have permission to edit this user.' ) );
		}
		elseif (array_key_exists('email', $_POST) || array_key_exists('email', $_GET) && count(get_blogs_of_user($profileuser->ID)) > 1 ) // Avoid a blog admin to change e-mail from a user that was in more than one blog
		{
			wp_die( __( 'You do not have permission to edit this user password.' ) );
		}
	}
}
add_filter( 'admin_head', 'rl_edit_permission_check', 1, 4 );

function rl_show_password_fields_filter($show, $profileuser)
{
	if(is_super_admin()) return true;
	elseif(IS_PROFILE_PAGE) return true;
	elseif(count(get_blogs_of_user($profileuser->ID)) > 1) return true; // Allow admin change password from user only in his blog
	else return false;
}
add_filter( 'show_password_fields', 'rl_show_password_fields_filter', 1, 2 );

/**
 * Checks if a particular user has a role.
 * Returns true if a match was found.
 *
 * @param string $role Role name.
 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
 * @return bool
 */
function rl_check_user_role( $role, $user_id = null ) {

	if ( is_numeric( $user_id ) )
		$user = get_userdata( $user_id );
	else
		$user = wp_get_current_user();

	if ( empty( $user ) )
		return false;

	return in_array( $role, (array) $user->roles );
}

?>