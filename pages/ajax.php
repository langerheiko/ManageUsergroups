<?php
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ). DIRECTORY_SEPARATOR . 'core.php';
require_once 'database_api.php';
require_once 'plugin_api.php';
require_once 'lang_api.php';
require_once 'functions.php';

//select the used plugin, because this file will be started standalone
plugin_push_current( 'ManageUsergroups' );

form_security_validate('plugin_manage_usergroups_edit');
$f_user_id		= gpc_get_int( 'user_id' );
$f_group_user_id	= gpc_get_int( 'group_user_id' );
$f_action		= gpc_get_string( 'group_user_action' );

$t_table_users = plugin_table( 'users' );//'mantis_plugin_UserGroups_users_table';//plugin_table( 'users' );
	
if($f_action == 'select') {
	$query = "INSERT INTO $t_table_users (group_user_id, user) VALUES (".db_param().",".db_param().")";
	$result = db_query_bound( $query, array( (int)$f_group_user_id, (int)$f_user_id ) );
} elseif($f_action == 'deselect') {
	$query = "DELETE FROM $t_table_users WHERE group_user_id=".db_param()." AND user=".db_param();
	$result = db_query_bound( $query, array( (int)$f_group_user_id, (int)$f_user_id ) );
} elseif($f_action == 'change_usergroup' && $f_user_id == 0) {
	print_users_in_group_option_list($f_group_user_id);
}
