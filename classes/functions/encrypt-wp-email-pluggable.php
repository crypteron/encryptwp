<?php

/**
 * Overrides core get_user_by pluggable with version that treats encrypted email differently.
 * NOTE: this file is only loaded if email encryption is turned on and get_uesr_by is not already defined.
 *
 * @since 2.8.0
 * @since 4.4.0 Added 'ID' as an alias of 'id' for the `$field` parameter.
 *
 * @param string     $field The field to retrieve the user with. id | ID | slug | email | login.
 * @param int|string $value A value for $field. A user ID, slug, email address, or login name.
 * @return WP_User|false WP_User object on success, false on failure.
 */
function get_user_by( $field, $value ) {
	if($field == 'email'){
		// TODO: search by user meta query once user meta query has been overloaded to handle search prefixes
	}
	$userdata = WP_User::get_data_by( $field, $value );

	if ( !$userdata )
		return false;

	$user = new WP_User;
	$user->init( $userdata );

	return $user;
}