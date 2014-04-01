<?php

form_security_validate( 'plugin_manage_usergroups_edit' );

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );


$t_group_prefix = gpc_get_string( 'group_prefix', '' );
$t_assign_group_threshold = gpc_get_int( 'assign_group_threshold', '' );
if(isset($_POST['assign_to_groups'])) {
    $t_assign_to_groups = $_POST['assign_to_groups']; 
} else {
    $t_assign_to_groups = 0;
}
if(isset($_POST['nested_groups'])) {
    $t_nested_groups = $_POST['nested_groups']; 
} else {
    $t_nested_groups = 0;
}

if( plugin_config_get('group_prefix', '') != $t_group_prefix ) {
	plugin_config_set( 'group_prefix', $t_group_prefix );
}

if( plugin_config_get('assign_group_threshold', '') != $t_assign_group_threshold ) {
	plugin_config_set( 'assign_group_threshold', $t_assign_group_threshold );
}

if( plugin_config_get('assign_to_groups', '') != $t_assign_to_groups ) {
	plugin_config_set( 'assign_to_groups', $t_assign_to_groups );
}

if( plugin_config_get('nested_groups', '') != $t_nested_groups ) {
	plugin_config_set( 'nested_groups', $t_nested_groups );
}



form_security_purge( 'plugin_manage_usergroups_edit' );

print_successful_redirect( plugin_page( 'manage_usergroups', true ) );
