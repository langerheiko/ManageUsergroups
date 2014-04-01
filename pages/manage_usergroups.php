<?php
require_once 'functions.php';

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'plugin_title' ) );

print_manage_menu();

?>
<br/>
<form action="<?php echo plugin_page( 'manage_usergroups_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_manage_usergroups_edit' ) ?>
<table align="center" class="width75" cellspacing="1">

<tr <?php /*echo helper_alternate_class( )*/?>>
	<td class="form-title">
		<?php echo plugin_lang_get('form_title') ?>
	</td>
	<td class="center">&nbsp;</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get('form_setting') ?>
	</td>
	<td class="category">
		<?php echo plugin_lang_get('form_value') ?>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="left">
		<?php echo plugin_lang_get('form_groupname_prefix') ?>
	</td>
	<td class="left">
		<input type='text' name="group_prefix" size="30" value="<?php echo plugin_config_get('group_prefix', '') ?>" />
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="left">
	<?php echo plugin_lang_get('form_assign_users_for_groups') ?><br/>
	</td>
	<td class="left">
		<input type="checkbox" name="assign_to_groups" value="1" 
		<?php 
			if( plugin_config_get('assign_to_groups', '') == 1 ) {
				echo 'checked="checked"'; 
                        }
		?> />
		<!-- <span style="color:red;">don't use - function is under construction</span> -->
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="left">
	<?php echo plugin_lang_get('form_assign_group_threshold') ?><br/>
	</td>
	<td class="left">
		<select name="assign_group_threshold"> 
		<?php 
			print_project_access_levels_option_list( plugin_config_get('assign_group_threshold', '') ); 
		?>
		</select>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="left">
	<?php echo plugin_lang_get('form_nested_groups') ?><br/>
	</td>
	<td class="left">
		<input type="checkbox" name="nested_groups" value="1" 
		<?php 
			if( plugin_config_get('nested_groups', '') == 1 ) {
				echo 'checked="checked"';
                        }
		?> />
	</td>
</tr>

<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" />
	</td>
</tr>

</table>
</form>
<br /><br />
<table align="center" class="width75" cellspacing="1">

<tr <?php /*echo helper_alternate_class( )*/?>>
	<td class="form-title">
		<?php echo plugin_lang_get('form_assign_users_to_usergroups') ?>
	</td>
	<td class="center">&nbsp;</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get('form_select_a_group') ?>
	</td>
	<td class="category">
		<?php echo plugin_lang_get('form_assign_users') ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class( )?>>
	<td class="left">
		<select id="select_usergroup" name="usergroups[]" size="10">
			<?php
			print_usergroups_option_list(); 
			?>
		</select>
	</td>
	<td class="left">
		<?php echo plugin_lang_get('form_click_user_name_select') ?><br />
		<select id="users" class="multiselect" name="users[]" multiple="multiple" size="10">
			<?php
			//print_users_in_group_option_list();
			?>
		</select>
	</td>
</tr>

</table>

<script type="text/javascript">
$(document).ready(function() {
	function deselect(id) {
		$('#users').multiSelect('deselect',id);
	}
	function select(id) {
		$('#users').multiSelect('select',id);
	}
	
	$('#select_usergroup').change(function(){
			//console.log($(this).val());
			var group_user_id = $(this).val();
			var user = 0;
			var token = $('[name=plugin_manage_usergroups_edit_token]').val();
			$.post(
				"plugins/ManageUsergroups/pages/ajax.php", 
				{user_id: user,
				 group_user_id: group_user_id,
				 group_user_action: 'change_usergroup',
				 plugin_manage_usergroups_edit_token: token
				}
			).done(function(data) {
				//console.log("Data Loaded: " + data);
				$('#users').html(data);
				$('#users').multiSelect('refresh');
			});
	});
	
	var str_all_users = <?php echo "'".plugin_lang_get('form_click_user_name_select')."'" ?>;
	$('#users').multiSelect({
			selectableHeader: "<div class='custom-header'>"+<?php echo "'".plugin_lang_get('form_header_all_users')."'" ?>+"</div>",
			selectionHeader: "<div class='custom-header'>"+<?php echo "'".plugin_lang_get('form_header_users_in_group')."'" ?>+"</div>",
			afterSelect: function(values) {
				var group_user_id = $('#select_usergroup').val();
				var user = values[0];
				var token = $('[name=plugin_manage_usergroups_edit_token]').val();
				
				if(group_user_id < 1) {alert('Select a group first!');deselect(user);return;}		
				$.post(
					"plugins/ManageUsergroups/pages/ajax.php", 
					{user_id: user,
					 group_user_id: group_user_id,
					 group_user_action: 'select',
					 plugin_manage_usergroups_edit_token: token
					}
				).done(function(data) {
					console.log("Data Loaded: " + data);
				});
			},
			afterDeselect: function(values) {
				var group_user_id = $('#select_usergroup').val();
				var user = values[0];
				var token = $('[name=plugin_manage_usergroups_edit_token]').val();
				
				if(group_user_id < 1) {alert('Select a group first!');/*select(user);*/return;}		
				$.post(
					"plugins/ManageUsergroups/pages/ajax.php", 
					{user_id: user,
					 group_user_id: group_user_id,
					 group_user_action: 'deselect',
					 plugin_manage_usergroups_edit_token: token
					}
				).done(function(data) {
					console.log("Data Loaded: " + data);
				});
			},
	});
	
	
});
</script>


<?php
html_page_bottom( __FILE__ );
?>
