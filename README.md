********************************************************************************
* Introduction                                                                 *
********************************************************************************

This plugin can be used to create groups of users in your mantis bugtracker. You
may use nested groups and you can decide if groups can handle a bug. 
Notifications will be send to all group members.

This plugin is build upon version 1.2.14 of mantis and should be installed as
any other plugin. No Mantis tables are being altered.

The plugin creates one table that holds the groups with their members. To use
this plugin you must edit some core functions!


********************************************************************************
* Installation                                                                 *
********************************************************************************

Like any other plugin.
After copying to your webserver :
- Start mantis as an administrator
- Select manage
- Select manage Plugins
- Select Install behind ManageUsergroups
- Once installed, click on the plugin-name for further configuration.
- Don't forget to edit the following core functions!


To use this plugin you will have to put some event signals in your mantis core 
code:

1)  in core/access_api.php:
    - Find the function access_get_project_level
    - Find the line:
        $t_global_access_level = access_get_global_level( $p_user_id );
    - Above this line enter the following event signal:
        $p_user_id = event_signal('EVENT_GROUP_ACCESS_HAS_BUG_LEVEL', array(array(access_get_global_level( $p_user_id ), $p_user_id, $p_project_id)));

2) in core/access_api.php:
    - Find the function access_has_bug_level
    - Find the lines:
        # check limit_Reporter (Issue #4769)
	# reporters can view just issues they reported
	$t_limit_reporters = config_get( 'limit_reporters', null, $p_user_id, $t_project_id );
    - Above these lines enter the following event signal:
        $p_user_id = event_signal('EVENT_GROUP_ACCESS_HAS_BUG_LEVEL', array(array(access_get_project_level( $t_project_id, $p_user_id ), $p_user_id, $t_project_id)));

3) in core/user_api.php:
    - Find the function user_get_access_level
    - Find the line:
        $t_project_access_level = project_get_local_user_access_level( $p_project_id, $p_user_id );
    - Above this line enter the following event signal:
        $p_user_id = event_signal('EVENT_GROUP_ACCESS_HAS_BUG_LEVEL', array(array(access_get_global_level( $p_user_id ), $p_user_id, $p_project_id)));

4) in core/print_api.php:
    - Find the function print_user_option_list
    - Find the lines:
        $t_display = array();
	$t_sort = array();
	$t_show_realname = ( ON == config_get( 'show_realname' ) );
    - Above these lines enter the following event signal:
        $t_users = event_signal('EVENT_GROUP_PROJECT_GET_ALL_USER_ROWS', array($t_users));


********************************************************************************
* Configuration options (with default values)                                  *
********************************************************************************

// You need to define a group prefix; every user you want to use as a group must
// start its username with this prefix
group_prefix                = '_grp_'

// Do you allow that groups can handle a bug?
assign_to_groups            = 0

// If you set assign_to_groups = 1, you can decide what access level is allowed
// to use this functionality
assign_group_threshold      = 90

// Do you allow to use groups inside of other groups?
nested_groups               = 0


********************************************************************************
* License                                                                      *
********************************************************************************

This plugin is distributed under the same conditions as Mantis itself (GPL).


********************************************************************************
* Bug reporting                                                                *
********************************************************************************

This code is available on github: 
https://github.com/langerheiko/ManageUsergroups

Log new issues against the ManageUsergroups plugin on:
https://github.com/langerheiko/ManageUsergroups/issues


********************************************************************************
* Developer                                                                    *
********************************************************************************

Heiko Schneider-Lange, working for eCola GmbH in Hannover, Germany
hsl@ecola.com
http://www.lebensmittel.de
