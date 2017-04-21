<?php
auth_reauthenticate ();
access_ensure_global_level ( config_get ( 'manage_plugin_threshold' ) );

// Retrieve user input
$avatar_storage_path = trim ( gpc_get_string ( 'avatar_storage_path' ) );
$ldap_avatar_field = trim ( strtolower ( gpc_get_string ( 'ldap_avatar_field' ) ) );
$ldap_last_modified_field = trim ( strtolower ( gpc_get_string ( 'ldap_last_modified_field' ) ) );
$avatar_max_width = trim ( gpc_get_int ( 'avatar_max_width' ) );
$avatar_max_height = trim ( gpc_get_int ( 'avatar_max_height' ) );

// Check user input
$error_count = 0;
$error_msg = '';
if (! file_exists ( $avatar_storage_path ) || ! is_writable ( $avatar_storage_path ) || ! is_dir ( $avatar_storage_path )) {
	$error_count ++;
	$error_msg .= 'Invalid avatar storage path: must be a writable directory. <br/>';
}
if (strlen ( $ldap_avatar_field ) < 1) {
	$error_count ++;
	$error_msg .= 'Invalid LDAP avatar attribute name. <br/>';
}
if (strlen ( $ldap_last_modified_field ) < 1) {
	$error_count ++;
	$error_msg .= 'Invalid LDAP last modified attribute name. <br/>';
}
if ($avatar_max_width < 0) {
	$error_count ++;
	$error_msg .= 'Invalid avatar maximum width. <br/>';
}
if ($avatar_max_height < 0) {
	$error_count ++;
	$error_msg .= 'Invalid avatar maximum height. <br/>';
}

if ($error_count > 0) {
	form_security_purge ( 'MantisLdapAvatarConfig_update' );
	print_successful_redirect ( plugin_page ( 'MantisLdapAvatarConfig.php', true ) . '&updated=0&msg=' . urlencode ( $error_msg ) );
} else {
	
	// Save
	plugin_config_set ( 'avatar_storage_path', $avatar_storage_path );
	plugin_config_set ( 'ldap_avatar_field', $ldap_avatar_field );
	plugin_config_set ( 'ldap_last_modified_field', $ldap_last_modified_field );
	plugin_config_set ( 'avatar_max_width', $avatar_max_width );
	plugin_config_set ( 'avatar_max_height', $avatar_max_height );
	
	form_security_purge ( 'MantisLdapAvatarConfig_update' );
	
	print_successful_redirect ( plugin_page ( 'MantisLdapAvatarConfig.php', true ) . '&updated=1' );
}
