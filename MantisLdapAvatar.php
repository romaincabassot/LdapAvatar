<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */
class MantisLdapAvatarPlugin extends MantisPlugin {
	
	/**
	 * A method that populates the plugin information and minimum requirements.
	 *
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get ( 'title' );
		$this->description = plugin_lang_get ( 'description' );
		$this->page = 'MantisLdapAvatarConfig.php';
		
		$this->version = '1.0.1';
		$this->requires = array (
				'MantisCore' => '2.0.0' 
		);
		
		$this->author = 'Romain Cabassot';
		$this->contact = 'romain.cabassot@gmail.com';
		$this->url = 'https://github.com/romaincabassot/MantisLdapAvatar';
	}
	
	/**
	 * Default plugin configuration.
	 *
	 * @return array
	 */
	function config() {
		return array (
				'avatar_storage_path' => __DIR__ . '/files/',
				'ldap_avatar_field' => 'jpegphoto',
				'ldap_last_modified_field' => 'modifytimestamp',
				'avatar_max_width' => 80,
				'avatar_max_height' => 80 
		);
	}
	
	/**
	 * Register event hooks for plugin.
	 */
	function hooks() {
		return array (
				'EVENT_USER_AVATAR' => 'user_get_avatar',
				'EVENT_LAYOUT_CONTENT_BEGIN' => 'check_requirements' 
		);
	}
	/**
	 * Check the plugin requirements
	 */
	function check_requirements() {
		if (! extension_loaded ( 'ldap' )) {
			log_event ( LOG_LDAP, 'Error: LDAP extension missing in php' );
			trigger_error ( ERROR_LDAP_EXTENSION_NOT_LOADED, WARNING );
		}
		
		if (! extension_loaded ( 'gd' )) {
			trigger_error ( 'Error: GD extension missing in PHP.', WARNING );
		}
		
		$avatar_storage_path = plugin_config_get ( 'avatar_storage_path' );
		if (! file_exists ( $avatar_storage_path ) || ! is_writable ( $avatar_storage_path ) || ! is_dir ( $avatar_storage_path )) {
			trigger_error ( 'Invalid avatar storage path: ' . $avatar_storage_path . ' must be a writable directory.', WARNING );
		}
	}
	
	/**
	 * Return the user avatar
	 *
	 * @param string $p_event
	 *        	The name for the event.
	 * @param integer $p_user_id
	 *        	A valid user identifier.
	 *        	
	 * @return Avatar An instance of class Avatar or null.
	 */
	function user_get_avatar($p_event, $p_user_id, $p_size = 80) {
		$username = user_get_name ( $p_user_id );
		$last_modified = ldap_get_field_from_username ( $username, plugin_config_get ( 'ldap_last_modified_field' ) );
		
		// Check if the avatar is already in cache
		$avatar_url = $this->get_user_avatar_from_cache ( $username, $last_modified );
		if ($avatar_url === null) {
			$t_avatar = $this->download_user_avatar ( $username, $last_modified );
		} else {
			$t_avatar = new Avatar ();
			$t_avatar->image = $avatar_url;
		}
		
		return $t_avatar;
	}
	
	/**
	 * Retrieves the user avatar from LDAP, resize it if needed then store it on disk cache.
	 *
	 * @param string $p_user_name
	 *        	the username
	 * @param string $p_last_modified
	 *        	a string that tells when the user LDAP entry was last modified
	 *        	
	 * @return Avatar An instance of class Avatar or null.
	 */
	function download_user_avatar($p_user_name, $p_last_modified) {
		$t_avatar = null;
		$image = ldap_get_field_from_username ( $p_user_name, plugin_config_get ( 'ldap_avatar_field' ) );
		if ($image != null && $image != '') {
			list ( $width, $height ) = getimagesizefromstring ( $image );
			
			$avatar_path = $this->get_avatar_path ( $p_user_name, $p_last_modified );
			
			// Do we need to process image width?
			$widthConstraint = plugin_config_get ( 'avatar_max_width' );
			$heightConstraint = plugin_config_get ( 'avatar_max_height' );
			if ($width > $widthConstraint || $height > $heightConstraint) {
				
				$ratio = $width / $height;
				if ($ratio > 1) {
					$dstWidth = $widthConstraint;
					$dstHeight = $widthConstraint / $ratio;
				} else {
					$dstWidth = $heightConstraint * $ratio;
					$dstHeight = $heightConstraint;
				}
				
				$srcImg = imagecreatefromstring ( $image );
				$dstImg = imagecreatetruecolor ( $dstWidth, $dstHeight );
				imagecopyresampled ( $dstImg, $srcImg, 0, 0, 0, 0, $dstWidth, $dstHeight, $width, $height );
				imagedestroy ( $srcImg );
				imagejpeg ( $dstImg, $this->get_avatar_path ( $p_user_name, $p_last_modified ), 75 );
				imagedestroy ( $dstImg );
			} else {
				$srcImg = imagecreatefromstring ( $image );
				imagejpeg ( $srcImg, $this->get_avatar_path ( $p_user_name, $p_last_modified ), 75 );
				imagedestroy ( $srcImg );
			}
			$this->delete_old_avatar ( $p_user_name, $p_last_modified );
			$t_avatar = new Avatar ();
			$t_avatar->image = plugin_file ( basename ( $avatar_path ) );
		}
		
		return $t_avatar;
	}
	
	/**
	 * Delete old avatar files.
	 *
	 * @param string $p_user_name
	 *        	the username
	 * @param string $p_last_modified
	 *        	a string that tells when the user LDAP entry was last modified
	 */
	function delete_old_avatar($p_user_name, $p_last_modified) {
		$search = sprintf ( '%s/%s-_-_-*', plugin_config_get ( 'avatar_storage_path' ), $p_user_name );
		$avatar_path = $this->get_avatar_path ( $p_user_name, $p_last_modified );
		foreach ( glob ( $search ) as $filename ) {
			if ($filename != $avatar_path && ! unlink ( $filename )) {
				error_log ( "Unable to delete file $filename" );
			}
		}
	}
	
	/**
	 * Check if the user avatar can be retrieved from cache.
	 *
	 * @param string $p_user_name
	 *        	the username
	 * @param string $p_last_modified
	 *        	a string that tells when the user LDAP entry was last modified
	 * @return NULL|string The user avatar file URL or null.
	 */
	function get_user_avatar_from_cache($p_user_name, $p_last_modified) {
		$avatar_path = $this->get_avatar_path ( $p_user_name, $p_last_modified );
		if (file_exists ( $avatar_path )) {
			return plugin_file ( basename ( $avatar_path ) );
		}
		return null;
	}
	
	/**
	 * Constructs the user avatar file absolute path.
	 *
	 * @param string $p_user_name
	 *        	the username
	 * @param string $p_last_modified
	 *        	a string that tells when the user LDAP entry was last modified
	 * @return string The user avatar file absolute path
	 */
	function get_avatar_path($p_user_name, $p_last_modified) {
		return sprintf ( '%s/%s-_-_-%s.jpg', plugin_config_get ( 'avatar_storage_path' ), $p_user_name, preg_replace ( '/[^a-zA-Z0-9\.]/', '', $p_last_modified ) );
	}
}
