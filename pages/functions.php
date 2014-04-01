<?php

require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ). DIRECTORY_SEPARATOR . 'core.php';
require_once 'database_api.php';
require_once 'plugin_api.php';
require_once 'lang_api.php';
require_once 'user_api.php';


function print_usergroups_option_list() {
	$t_user_table = db_get_table( 'mantis_user_table' );
	$query = "SELECT u.id, u.username, u.realname FROM $t_user_table AS u WHERE u.username LIKE '".plugin_config_get('group_prefix') ."%' ORDER BY u.username ASC";
	$result = db_query( $query );
	$count = db_num_rows( $result );
	for( $i = 0;$i < $count;$i++ ) {
		$row = db_fetch_array( $result );
		echo '<option value="'.$row['id'].'" ';
		echo '>' . $row['username'] . '</option>';
	}	
}

function print_users_in_group_option_list($usergroup_id) {
	if( plugin_config_get('assign_to_groups', '') == 1  && plugin_config_get('assign_group_threshold','') <= user_get_access_level( auth_get_current_user_id() ) ) {
		$show_groups = 1;
	} else {
		$show_groups = 0;
	}
	
	$t_table_users = plugin_table( 'users' );
	$t_user_table = db_get_table( 'mantis_user_table' );
	$query = "SELECT * FROM (";
	$query.= "    SELECT u.id, u.username, u.realname, ug.group_user_id";
	$query.= "    FROM $t_user_table AS u";
	$query.= "        LEFT JOIN $t_table_users AS ug ON (u.id=ug.user)";
	//if( plugin_config_get('assign_to_groups', '') == 0  || plugin_config_get('assign_group_threshold','') > user_get_access_level( auth_get_current_user_id() ) )
	if( $show_groups == 0 ) {
		$query.= "    WHERE u.username NOT LIKE ".db_param();
        }
	$query.= ") AS t1 WHERE group_user_id=".db_param()." OR group_user_id IS NULL ORDER BY username ASC";
	if( $show_groups == 0 ) {
		$result = db_query_bound( $query, Array( plugin_config_get('group_prefix').'%', (int)$usergroup_id ) );
	} else {	
		$result = db_query_bound( $query, Array( (int)$usergroup_id ) );
	}
	$count = db_num_rows( $result );
	for( $i = 0;$i < $count;$i++ ) {
		$row = db_fetch_array( $result );
		if($row['id'] == $usergroup_id) {
                    continue; //usergroup must not be nested with itself
                }
		echo '<option value="'.$row['id'].'" ';
		if(!is_null($row['group_user_id'])) {
                    echo 'selected="selected"';
                } else {
                    echo '';
                }
		echo '>' . $row['username'] . '</option>';
	}
}

