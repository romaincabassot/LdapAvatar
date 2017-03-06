<?php
	
	auth_reauthenticate( );
	access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
	layout_page_header ( plugin_lang_get( 'title' ) );
	layout_page_begin ('manage_overview_page.php');
	print_manage_menu( 'manage_plugin_page.php' );
	

?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">
<form action="<?php echo plugin_page ( 'ldapavatar_config_update' ); ?>" method="post">
<?php echo form_security_field ( ' plugin_RequiredFields_update' ); ?>
<?php
	if ( $_GET [ 'updated' ] == 1 ) {
                echo '<div class="space-10"></div><div align="center"><span class="dependency_met">' . plugin_lang_get ( 'updated' ) . date( config_get( 'normal_date_format' ), time() ) . '</span></div><div class="space-10"></div>';
        }

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
<div class="widget-toolbox padding-8 clearfix">
        <input class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo plugin_lang_get ( 'save' ); ?>" type="submit">
</div>

	<table class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<td><?php echo plugin_lang_get ('project'); ?></td>
			<td><?php echo plugin_lang_get ('required_fields'); ?></td>
		</thead>
		<tbody>
<?php
	$project_list = '';
	$t_manage_project_threshold = config_get( 'manage_project_threshold' );
	$t_projects = user_get_accessible_projects( auth_get_current_user_id(), true );
	$t_full_projects = array();
	foreach ( $t_projects as $t_project_id ) {
		$t_full_projects[] = project_get_row( $t_project_id );
	}
	$f_sort	= gpc_get_string( 'sort', 'name' );
	$f_dir	= gpc_get_string( 'dir', 'ASC' );
	$t_projects = multi_sort( $t_full_projects, $f_sort, $t_direction );
	$t_stack 	= array( $t_projects );
	
	while ( 0 < count( $t_stack ) ) {
		$t_projects   = array_shift( $t_stack );

		if ( 0 == count( $t_projects ) ) {
			continue;
		}

		$t_project = array_shift( $t_projects );
		$t_project_id = $t_project['id'];
		$t_level      = count( $t_stack );
		
		$project_list .= $t_project_id . ',';
		$required_fields = get_required_fields ( $t_project_id );

		# only print row if user has project management privileges
		if (access_has_project_level( $t_manage_project_threshold, $t_project_id, auth_get_current_user_id() ) ) {

?>
<tr <?php echo helper_alternate_class() ?>>
	<td>
		<?php echo str_repeat( "&raquo; ", $t_level ) . string_display( $t_project['name'] ) ?>
	</td>
	<td>
<?php
foreach ( plugin_config_get ('available_fields') as $field ) {
		$checked = '';
		if ( in_array ( $field, $required_fields)) {
			$checked = ' checked';
		}
?>
		 <input type="checkbox" name="<?php echo $field; ?>_<?php echo $t_project_id; ?>" value="<?php echo $field; ?>" <?php echo $checked; ?>> <?php echo $field; ?>
<?php
	}
?>
	</td>
</tr>
<?php
		}
		$t_subprojects = project_hierarchy_get_subprojects( $t_project_id, true );

		if ( 0 < count( $t_projects ) || 0 < count( $t_subprojects ) ) {
			array_unshift( $t_stack, $t_projects );
		}

		if ( 0 < count( $t_subprojects ) ) {
            $t_full_projects = array();
		    foreach ( $t_subprojects as $t_project_id ) {
                $t_full_projects[] = project_get_row( $t_project_id );
            }
			$t_subprojects = multi_sort( $t_full_projects, $f_sort, $t_direction );
			array_unshift( $t_stack, $t_subprojects );
		}
	}
?>
</tbody>
</table>
<div class="widget-toolbox padding-8 clearfix">
        <input class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo plugin_lang_get ( 'save' ); ?>" type="submit">
</div>
<input type="hidden" name="project_list" value="<?php echo $project_list; ?>" />
</div>
</form>
</div>
</div>


<?php
	layout_page_end();
