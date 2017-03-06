<?php
auth_reauthenticate ();
access_ensure_global_level ( config_get ( 'manage_plugin_threshold' ) );
layout_page_header ( plugin_lang_get ( 'title' ) );
layout_page_begin ( 'manage_overview_page.php' );
print_manage_menu ( 'manage_plugin_page.php' );

?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="form-container">
		<form
			action="<?php echo plugin_page ( 'LdapAvatarConfig_update' ); ?>"
			method="post">
<?php echo form_security_field ( ' plugin_LdapAvatarConfig_update' ); ?>
<?php
if (isset ( $_GET ['updated'] )) {
	$error_occurred = $_GET ['updated'] != '1';
	$msg_class = $error_occurred ? 'dependency_unmet' : 'dependency_met';
	$msg = $error_occurred ? $_GET ['msg'] : plugin_lang_get ( 'updated' ) . date ( config_get ( 'normal_date_format' ), time () );
	echo '<div class="space-10"></div><div align="center"><span class="' . $msg_class . '">' . $msg . '</span></div><div class="space-10"></div>';
}

$Parsedown = new Parsedown ();
$readme = implode ( '', file ( __DIR__ . '/../README.md' ) );

?>
<div class="widget-box widget-color-blue2">
				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter">
						<i class="ace-icon fa fa-sliders"></i><?php echo plugin_lang_get( 'title') ?>
		</h4>
				</div>


				<div class="widget-body">
					<div class="widget-main no-padding">

						<div class="table-responsive">

							<table class="table table-bordered table-condensed table-striped">
								<tbody>
									<tr>
										<td colspan="2"><article><?php echo $Parsedown->text($readme) ?></article>
										</td>
									</tr>
									<tr>
										<th class="category width-40"><?php echo plugin_lang_get( 'avatar_storage_path_title') ?><br>
											<span class="small"><?php echo plugin_lang_get( 'avatar_storage_path_details') ?></span>
										</th>
										<td><input type="text" size="100" class="ace"
											name="avatar_storage_path"
											value="<?php echo plugin_config_get('avatar_storage_path') ?>">
										</td>

									</tr>

									<tr>
										<th class="category width-40"><?php echo plugin_lang_get( 'ldap_avatar_field_title') ?><br />
											<span class="small"><?php echo plugin_lang_get( 'ldap_avatar_field_details') ?></span>
										</th>
										<td><input type="text" size="100" class="ace"
											name="ldap_avatar_field"
											value="<?php echo plugin_config_get('ldap_avatar_field')?>">
										</td>
									</tr>

									<tr>
										<th class="category width-40"><?php echo plugin_lang_get( 'ldap_last_modified_field_title') ?><br>
											<span class="small"><?php echo plugin_lang_get( 'ldap_last_modified_field_details') ?></span>
										</th>
										<td><input type="text" size="100" class="ace"
											name="ldap_last_modified_field"
											value="<?php echo plugin_config_get('ldap_last_modified_field')?>"></td>
									</tr>

									<tr>
										<th class="category width-40"><?php echo plugin_lang_get( 'avatar_max_width_title') ?> <br>
											<span class="small"><?php echo plugin_lang_get( 'avatar_max_width_details') ?></span>
										</th>
										<td><input type="text" size="100" class="ace"
											name="avatar_max_width"
											value="<?php echo plugin_config_get('avatar_max_width')?>"></td>

									</tr>
									<tr>
										<th class="category width-40"><?php echo plugin_lang_get( 'avatar_max_width_title') ?> <br>
											<span class="small"> <?php echo plugin_lang_get( 'avatar_max_width_details') ?> </span>
										</th>
										<td><input type="text" size="100" class="ace"
											name="avatar_max_height"
											value="<?php echo plugin_config_get('avatar_max_height')?>"></td>

									</tr>
								</tbody>
							</table>

							<div class="widget-toolbox padding-8 clearfix">
								<input class="btn btn-primary btn-sm btn-white btn-round"
									value="<?php echo plugin_lang_get ( 'save' ); ?>" type="submit">
							</div>
						</div>
		
		</form>
	</div>
</div>


<?php
layout_page_end ();
