<?php
if(!class_exists("WbMassMessagingAdmin")){
	class WbMassMessagingAdmin{
		public function __construct()
		{
			add_action('admin_menu', array($this,'addAdminMenus'), 1);
		}
		public function addAdminMenus(){
			if(!$this->menuExists('Wb_mass_message')){
				add_menu_page(__('Wb Mass Message', WBCOM_MASS_MESSAGE_TEXT_DOMIAN), __('Wb Mass Message', WBCOM_MASS_MESSAGE_TEXT_DOMIAN), 'manage_options', 'Wb_mass_message', array($this,displayWbOptionsPage), 'dashicons-email-alt');
			}
			add_submenu_page('Wb_mass_message', __('Mass Messages Log', WBCOM_MASS_MESSAGE_TEXT_DOMIAN), __('Mass Messages Log', WBCOM_MASS_MESSAGE_TEXT_DOMIAN), 'manage_options', 'MassMessagesLog', array($this,'displayAdminMessagesLog'));
		}
		public function displayWbOptionsPage(){
    			if(!current_user_can('manage_options')){
      				wp_die(__('You do not have sufficient permissions to access this page.', WBCOM_MASS_MESSAGE_TEXT_DOMIAN));
    			}else{	
    				echo "<div class=\"wrap\">";
					echo "<h2>".__("Mass Messaging Options", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)."</h2>";
				if ( isset( $_POST['MassMessage_nonce_field'] ) || wp_verify_nonce( $_POST['MassMessage_nonce_field'], 'MassMessage_action' ) ) {
					do_action('wb_mass_message_for_buddypress_before_update_settings');
					if(isset($_POST['wb_MassMessageGroups'])){
               			update_site_option('MassMessageGroups',$_POST['wb_MassMessageGroups']);
                	}  
               		if(isset($_POST['wb_MassMessageAllGroups'])){
               			update_site_option('MassMessageAllGroups',$_POST['wb_MassMessageAllGroups']);
                	}  
                	if(isset($_POST['wb_MassMessageAllGroupsOverride'])){
               			update_site_option('MassMessageAllGroupsOverride',$_POST['wb_MassMessageAllGroupsOverride']);
                	}else{
                		update_site_option('MassMessageAllGroupsOverride','false');
                	}
                	if(isset($_POST['wb_MassMessageMembers'])){
                    		update_site_option('MassMessageMembers',$_POST['wb_MassMessageMembers']);
                	}   
                	if(isset($_POST['wb_MassMessageAllMembers'])){
                    		update_site_option('MassMessageAllMembers',$_POST['wb_MassMessageAllMembers']);
                	}
                	if(isset($_POST['wb_MassMessageAllMembersOverride'])){
               			update_site_option('MassMessageAllMembersOverride',$_POST['wb_MassMessageAllMembersOverride']);
                	}else{
                		update_site_option('MassMessageAllMembersOverride','false');
                	}
                	if(isset($_POST['wb_MassMessageBlogs'])){
                    		update_site_option('MassMessageBlogs',$_POST['wb_MassMessageBlogs']);
                	}   
                	if(isset($_POST['wb_MassMessageAllBlogs'])){
                    		update_site_option('MassMessageAllBlogs',$_POST['wb_MassMessageAllBlogs']);
                	}
                	if(isset($_POST['wb_MassMessageAllBlogsOverride'])){
               			update_site_option('MassMessageAllBlogsOverride',$_POST['wb_MassMessageAllBlogsOverride']);
                	}else{
                		update_site_option('MassMessageAllBlogsOverride','false');
                	}
					if(isset($_POST['wb_MassMessageMinimumType'])){
                    	update_site_option('MassMessageMinimumType',$_POST['wb_MassMessageMinimumType']);
                	}
                	do_action('wb_mass_message_for_buddypress_after_update_settings');
                	?>
					<div class="updated"><p><strong><?php echo __("Options Updated", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></strong></p></div>
					<?php
					}
					if(isset($_POST['reset_MassMessageOptions'])){
						do_action('wb_mass_message_for_buddypress_before_reset_settings');
						update_site_option('MassMessageGroups', 'true');
						update_site_option('MassMessageAllGroups', 'true');
						update_site_option('MassMessageAllGroupsOverride', 'false');
						update_site_option('MassMessageMembers', 'true');
						update_site_option('MassMessageAllMembers', 'true');
						update_site_option('MassMessageAllMembersOverride', 'true');
						update_site_option('MassMessageBlogs', 'true');
						update_site_option('MassMessageAllBlogs', 'true');
						update_site_option('MassMessageAllBlogsOverride', 'true');
						update_site_option('MassMessageMinimumType', 'administrator');
						do_action('wb_mass_message_for_buddypress_after_reset_settings');
						?>
						<div class="updated"><p><strong><?php echo __("Options Reset", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></strong></p></div>
						<?php
					}
					$oldMassMessageGroups = get_site_option('MassMessageGroups');
					$oldMassMessageAllGroups = get_site_option('MassMessageAllGroups');
					$oldMassMessageAllGroupsOverride = get_site_option('MassMessageAllGroupsOverride');
					$oldMassMessageMembers = get_site_option('MassMessageMembers');
					$oldMassMessageAllMembers = get_site_option('MassMessageAllMembers');
					$oldMassMessageAllMembersOverride = get_site_option('MassMessageAllMembersOverride');
					$oldMassMessageBlogs = get_site_option('MassMessageBlogs');
					$oldMassMessageAllBlogs = get_site_option('MassMessageAllBlogs');
					$oldMassMessageAllBlogsOverride = get_site_option('MassMessageAllBlogsOverride');
					$oldMassMessageMinimumType = get_site_option('MassMessageMinimumType');
					?>
					<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                    <?php wp_nonce_field( 'MassMessage_action', 'MassMessage_nonce_field' );?>
						<table class="form-table">
						<tr>
						<th scope="row"><?php echo __("Allow mass messaging to groups?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label title='Yes'><input type='radio' name='wb_MassMessageGroups' value='true' <?php if($oldMassMessageGroups == 'true'){echo "checked";} ?> /> <span><?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							<label title='No'><input type='radio' name='wb_MassMessageGroups' value='false' <?php if($oldMassMessageGroups == 'false'){echo "checked";} ?> /> <span><?php echo __("No", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><?php echo __("Allow mass messaging to select all groups?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label title='Yes'><input type='radio' name='wb_MassMessageAllGroups' value='true' <?php if($oldMassMessageAllGroups == 'true'){echo "checked";} ?> /> <span><?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							<label title='No'><input type='radio' name='wb_MassMessageAllGroups' value='false' <?php if($oldMassMessageAllGroups == 'false'){echo "checked";} ?> /> <span><?php echo __("No", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><?php echo __("Show ALL groups, not just those with membership?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label for='wb_MassMessageAllGroupsOverride'><input type='Checkbox' name='wb_MassMessageAllGroupsOverride' id='wb_MassMessageAllGroupsOverride' value='true' <?php if($oldMassMessageAllGroupsOverride == 'true'){echo "checked";} ?> /> <?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><?php echo __("Allow mass messaging to members?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label title='Yes'><input type='radio' name='wb_MassMessageMembers' value='true' <?php if($oldMassMessageMembers == 'true'){echo "checked";} ?> /> <span><?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							<label title='No'><input type='radio' name='wb_MassMessageMembers' value='false' <?php if($oldMassMessageMembers == 'false'){echo "checked";} ?> /> <span><?php echo __("No", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><?php echo __("Allow mass messaging to select all members?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label title='Yes'><input type='radio' name='wb_MassMessageAllMembers' value='true' <?php if($oldMassMessageAllMembers == 'true'){echo "checked";} ?> /> <span><?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							<label title='No'><input type='radio' name='wb_MassMessageAllMembers' value='false' <?php if($oldMassMessageAllMembers == 'false'){echo "checked";} ?> /> <span><?php echo __("No", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><?php echo __("Show ALL members, not just friends?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label for='wb_MassMessageAllMembersOverride'><input type='Checkbox' name='wb_MassMessageAllMembersOverride' id='wb_MassMessageAllMembersOverride' value='true' <?php if($oldMassMessageAllMembersOverride == 'true'){echo "checked";} ?> /> <?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><?php echo __("Allow mass messaging to blogs (requires Multisite)?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label title='Yes'><input type='radio' name='wb_MassMessageBlogs' value='true' <?php if($oldMassMessageBlogs == 'true'){echo "checked";} ?> /> <span><?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							<label title='No'><input type='radio' name='wb_MassMessageBlogs' value='false' <?php if($oldMassMessageBlogs == 'false'){echo "checked";} ?> /> <span><?php echo __("No", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><?php echo __("Allow mass messaging to select all blogs (requires Multisite)?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label title='Yes'><input type='radio' name='wb_MassMessageAllBlogs' value='true' <?php if($oldMassMessageAllBlogs == 'true'){echo "checked";} ?> /> <span><?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							<label title='No'><input type='radio' name='wb_MassMessageAllBlogs' value='false' <?php if($oldMassMessageAllBlogs == 'false'){echo "checked";} ?> /> <span><?php echo __("No", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></span></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><?php echo __("Show ALL blogs, not just those with membership?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
						<td>
							<fieldset>
							<label for='wb_MassMessageAllBlogsOverride'><input type='Checkbox' name='wb_MassMessageAllBlogsOverride' id='wb_MassMessageAllBlogsOverride' value='true' <?php if($oldMassMessageAllBlogsOverride == 'true'){echo "checked";} ?> /> <?php echo __("Yes", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
							</fieldset>
						</td>
						</tr>
						<tr>
						<th scope="row"><label for="wb_MassMessageMinimumType"><?php echo __("Minimum role for usage", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label></th>
						<td><select name="wb_MassMessageMinimumType" id="wb_MassMessageMinimumType">
							<?php
							$items = array('Super Admin' => 'super admin', 'Administrator' => 'administrator', 'Editor' => 'editor', 'Author' => 'author', 'Contributor' => 'contributor', 'Subscriber' => 'subscriber');
							foreach($items as $item=>$value){
								$selected = ($oldMassMessageMinimumType == $value ) ? 'selected="selected"' : '';
								echo "<option value='$value' $selected>$item</option>";
							}
							?>
						</select></td>
						</tr>
						</table>
						<table><tr><td><p class="submit"><input type="submit" name="wb_MassMessageOptions" id="submit" class="button-primary" value="Save Changes"  /></p></td>
						<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>"><td><p class="submit"><input type="submit" name="reset_MassMessageOptions" id="submit" class="button-secondary" value="Reset to defaults"  /></p></td></form></tr>
						</table>
						</form></div>
						<?php echo __("Still struggling? You can view the official help", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?> <a href="http://www.stormation.info/portfolio-item/mass-messaging-in-buddypress/"><?php echo __("here...", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></a>
                			<?php
				}
			}
	
		public function displayAdminMessagesLog(){
			global $wpdb;
			$table_name = $wpdb->prefix . 'mass_message_log';
			$logs = $wpdb->get_results( "SELECT * FROM $table_name ");
			?>
			<div class="wrap">
				<?php echo "<h2>".__("Mass Messaging Options", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)."</h2>";?>
                <table class="form-table">
                    <tr>
                        <th scope="row" style="border-bottom:1px solid;"><?php echo __("Sender User", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
                        <th scope="row" style="border-bottom:1px solid;"><?php echo __("Receiver User(s) Id", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
                        <th scope="row" style="border-bottom:1px solid;"><?php echo __("Time", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></th>
                    </tr>
                    <?php if($logs)
						{
							foreach ( $logs as $log )
							{$user_info=get_userdata( $log->user_id );?>
                            <tr>
                                <td style="border-bottom:1px solid;"><?php echo __($user_info->user_login)?></td>
                                <td style="border-bottom:1px solid;"><?php echo __(implode(',', unserialize($log->message_receiver)))?></td>
                                <td style="border-bottom:1px solid;"><?php echo __($log->msg_time)?></td>
                            </tr>
                           <?php }
						}
						else
						{?>
                            <tr>
                                <td colspan="3">No Log</td>
                            </tr>
                        <?php
						}?>
		<?php	
		}
	
		public function menuExists($handle, $sub = false) {
				global $menu, $submenu;
				if(!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
					return false;
				}	
				$check_menu = $sub ? $submenu : $menu;
				if(empty($check_menu)) {
					return false;
				}
				foreach($check_menu as $k => $item) {
					if($sub) {
						foreach($item as $sm) {
							if($handle == $sm[2]) {
								return true;
							}
						}
					} else {
						if($handle == $item[2]) {
							return true;
						}
					}
				}
				return false;
			}
	}
	new WbMassMessagingAdmin();
}