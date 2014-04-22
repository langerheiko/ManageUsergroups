<?php

require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );

Class ManageUsergroupsPlugin extends MantisPlugin {

	private $plugin_path;
		
	function register() {
		$this->name		= plugin_lang_get('plugin_title');//'ManageUsergroups';
		$this->description 	= plugin_lang_get('plugin_desc');
		//$this->page		= 'config';

		$this->version		= '0.1';
		$this->requires		= array('MantisCore' => '1.2.14');
		
		$this->author		= 'eCola GmbH, Heiko Schneider-Lange';
		$this->contact		= 'hsl@ecola.com';
		$this->url		= 'http://www.lebensmittel.de';
                $this->page             = 'manage_usergroups';
	}

	function config() {
		return array(
			'group_prefix' => '_grp_',
			'assign_to_groups' => 0,
			'assign_group_threshold' => 90,
			'nested_groups' => 0,
			);
	}
	
	function init() {
		$this->plugin_path = 'plugins' . DIRECTORY_SEPARATOR . plugin_get_current() . DIRECTORY_SEPARATOR;
		
		// plugin events - view the README.txt file to know where these
                // events have to be called
		event_declare('EVENT_GROUP_ACCESS_HAS_BUG_LEVEL', EVENT_TYPE_CHAIN);
		plugin_event_hook('EVENT_GROUP_ACCESS_HAS_BUG_LEVEL', 'group_access_has_bug_level');
		event_declare('EVENT_GROUP_PROJECT_GET_ALL_USER_ROWS', EVENT_TYPE_CHAIN);
		plugin_event_hook('EVENT_GROUP_PROJECT_GET_ALL_USER_ROWS', 'group_project_get_all_user_rows');
	}
	
	function hooks() {
		return array(
				'EVENT_MENU_MANAGE'	=> 'manage_usergroups_menu',
				'EVENT_LAYOUT_RESOURCES' => 'my_resources',
                                'EVENT_NOTIFY_USER_INCLUDE' => 'notify_group_users',
			);
	}
	
	function manage_usergroups_menu( )
	{
		return array( '<a href="' . plugin_page( 'manage_usergroups' ) . '">' . plugin_lang_get( 'manage_groups' )  . '</a>', );
	}

	

	function schema() {
		return array(
				array( 'CreateTableSQL', array( plugin_table( 'users' ), "
						id				I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
						group_user_id			I		NOTNULL,
						user				I		NOTNULL
						" ) ),
			);
	}
	
	function my_resources () {
		$res = '';
		$res .= '<script src="'.$this->plugin_path.'resources/jquery-1.9.1.min.js"></script>';
		$res .= '<script src="'.$this->plugin_path.'resources/multi-select/js/jquery.multi-select.js"></script>';
		$res .= '<link href="'.$this->plugin_path.'resources/multi-select/css/multi-select.css" media="screen" rel="stylesheet" type="text/css" />';
		return $res;
	}
	
	/**
	* Checks whether the users access level or the access level from
	* a group a user is in is higher. Return the user id with higher
	* access level.
	* @param object $p_event
	* @param array $p_chained_param array(array(user_project_level, user_id, project_id))
	* @return int user id
	*/
	function group_access_has_bug_level($p_event, $p_chained_param) {
		$t_user_project_level = $p_chained_param[0];
		$t_user_id = $p_chained_param[1];
		$t_project_id = $p_chained_param[2];
		
		$t_group_user_id = 0;
		$t_group_access_level = 0;
		
		$t_users = plugin_table( 'users' );
		$query = "SELECT group_user_id FROM $t_users WHERE user=". db_param();
		$result = db_query_bound( $query, Array( (int)$t_user_id ) );
		$count = db_num_rows( $result );
		for( $i = 0;$i < $count;$i++ ) {
			$row = db_fetch_array( $result );
			$t_level = access_get_project_level( $t_project_id, $row['group_user_id'] );
			
			if($t_level >= $t_group_access_level) {
				$t_group_access_level = $t_level;
				$t_group_user_id = $row['group_user_id'];
			}
		}
		
		if($t_user_project_level < $t_group_access_level) {
			return $t_group_user_id;
		} else {
			return $t_user_id;
		}
		
	}
	
	function group_project_get_all_user_rows($p_event, $p_chained_param) {
		//prepare $p_chained_param
		$t_users = array();
		foreach($p_chained_param as $t_user) {
			$t_users[$t_user['id']] = $t_user;
		}
		$p_chained_param = $t_users;
		//prepare $p_chained_param end
		
		$t_users = array();
		foreach($p_chained_param as $key => $t_user) {
			if( strpos( $t_user['username'], plugin_config_get('group_prefix') ) !== FALSE) {
			//username is a group
				$t_table_users = plugin_table( 'users' );
				$t_user_table = db_get_table( 'mantis_user_table' );
				$query = "SELECT u.id, u.username, u.realname FROM $t_table_users AS ug JOIN $t_user_table AS u ON (u.id=ug.user) WHERE group_user_id=". db_param();
				$result = db_query_bound( $query, Array( (int)$t_user['id'] ) );
				$count = db_num_rows( $result );
				for( $i = 0;$i < $count;$i++ ) {
					$row = db_fetch_array( $result );
					//echo '<pre>'.print_r($row['username'], 1).'</pre>';
					
					//if user has its own access level
					if(array_key_exists($row['id'], $p_chained_param)) {
						if($p_chained_param[$row['id']]['access_level'] < $t_user['access_level']) {
						//if user has lower level, adjust to group level
							$t_users[$row['id']] = $p_chained_param[$row['id']];
							$t_users[$row['id']]['access_level'] = $t_user['access_level'];
							continue;
						} else {
						//if user has higher level, do nothing
							continue;
						}
					}
					
					if( plugin_config_get('nested_groups') === 1 ) {
						if( strpos( $row['username'], plugin_config_get('group_prefix') ) !== FALSE) {
						//username is a group
							$t_group[$row['id']] = array(
								'id' => $row['id']
								, 'username' => $row['username']
								, 'realname' => $row['realname']
								, 'access_level' => $t_user['access_level']);
							//echo '<pre>'.print_r($t_group, 1).'</pre>';
							$t_nested_group = $this->group_project_get_all_user_rows($p_event, $t_group);
							$t_users = array_merge($t_users, $t_nested_group);
							continue;
						}
					}
					
					$t_users[$row['id']] = array(
						'id' => $row['id']
						, 'username' => $row['username']
						, 'realname' => $row['realname']
						, 'access_level' => $t_user['access_level']);
				}
				if( plugin_config_get('assign_to_groups', '') == 1  
                                        && plugin_config_get('assign_group_threshold','') <= user_get_access_level( auth_get_current_user_id(), helper_get_current_project() ) ) 
					$t_users[$key] = $t_user;
			} else {
			//username is not a group
				$t_users[$key] = $t_user;
			}
		}
		return $t_users;
	}
	
	
	/**
         * hook function for system event EVENT_NOTIFY_USER_INCLUDE
         * @param string $p_event
         * @param int $p_param contains the bug_id
         * @return array
         */
        function notify_group_users ($p_event, $p_param) {
            $t_bug_id = $p_param;
            $t_bug = bug_get($t_bug_id);
            
            //collect all user ids that can hold groups
            $t_users = array() + bug_get_monitors($t_bug_id);
            $t_users[] = $t_bug->reporter_id;
            $t_users[] = $t_bug->handler_id;
            
            //exclude the native users
            $t_all_groups = $this->get_all_groups(TRUE);
            $t_notify_groups = array_merge(array_intersect($t_users, $t_all_groups));
            
            //get the users from the group(s)
            $t_group_users = array();
            for( $i=0; $i < count($t_notify_groups); $i++) {
                $t_group_users = array_merge( $t_group_users, $this->get_all_users_from_group($t_notify_groups[$i]) );
            }
            
            return $t_group_users;
        }
        
        
        /**
         * Returns an array of all groups, that have been defined in the system.
         * @param boolean $id_only If set TRUE the returned array contains id only.
         * Otherwise you will get an associative array with id, username, realname
         * @return array
         */
        function get_all_groups ( $id_only = FALSE ) {
            $t_user_table = db_get_table( 'mantis_user_table' );
            $query = "SELECT u.id, u.username, u.realname FROM $t_user_table AS u WHERE u.username LIKE ". db_param();
            $result = db_query_bound( $query, Array( plugin_config_get('group_prefix', '').'%' ) );
            $count = db_num_rows( $result );
            $t_groups = array();
            for( $i = 0;$i < $count;$i++ ) {
                $row = db_fetch_array( $result );
                if($id_only) {
                    $t_groups[] = $row['id'];
                } else {
                    $t_groups[$row['id']] = array(
                        'id' => $row['id']
                        , 'username' => $row['username']
                        , 'realname' => $row['realname']
                    );
                }
            }
            return $t_groups;
        }
        
        
        /**
         * Returns an array of all users from the group user id param. Calls itself,
         * if it detects a nested group.
         * @param int $p_group_user_id user_id of a group
         * @return array Array of users in the group
         */
        function get_all_users_from_group ( $p_group_user_id ) {
            $t_table_users = plugin_table( 'users' );
            $t_all_groups = $this->get_all_groups(TRUE);
            $query = "SELECT user FROM $t_table_users WHERE group_user_id=". db_param();
            $result = db_query_bound( $query, Array( $p_group_user_id ) );
            $count = db_num_rows( $result );
            $t_users = array();
            for( $i = 0;$i < $count;$i++ ) {
                $row = db_fetch_array($result);
                if(in_array($row['user'], $t_all_groups)) {
                    $t_users = $t_users + $this->get_all_users_from_group($row['user']);
                } else {
                    $t_users[] = $row['user'];
                }
            }
            return $t_users;
        }
}
