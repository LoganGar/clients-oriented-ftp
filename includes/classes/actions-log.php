<?php
/**
 * Class that handles all the actions that are logged on the database.
 *
 * @package		ProjectSend
 * @subpackage	Classes
 *
 * Reference of actions list by number:
 *
 * 0-	ProjecSend has been installed correctly
 * 1-	Account logs in through the form
 * 2-	A user creates a new user account
 * 3-	A user creates a new client account
 * 4-	A client registers an account for himself
 * 5-	A file is uploaded by an user
 * 6-	A file is uploaded by a client
 * 7-	A file is downloaded by a user (on "Client view" mode)
 * 8-	A file is downloaded by a client
 * 9-	A zip file was generated by a client
 * 10-	A file has been unassigned from a client.
 * 11-	A file has been unassigned from a group.
 * 12-	A file has been deleted.
 * 13-	A user was edited.
 * 14-	A client was edited.
 * 15-	A group was edited.
 * 16-	A user was deleted.
 * 17-	A client was deleted.
 * 18-	A group was deleted.
 * 19-	A client account was activated.
 * 20-	A client account was deactivated.
 * 21-	A file was marked as hidden.
 * 22-	A file was marked as visible.
 * 23-	A user creates a new group.
 * 24-	Account logs in trhough cookies.*
 * 25-	A file is assigned to a client.
 * 26-	A file is assigned to a group.
 * 27-	A user account was marked as active.
 * 28-	A user account was marked as inactive.
 * 29-	The logo on "Branding" was changed.
 * 30-	ProjectSend was updated.
 *
 * More to be added soon.
 */

class LogActions
{

	var $action = '';

	/**
	 * Create a new client.
	 */
	function log_action_save($arguments)
	{
		global $database;
		$this->state = array();

		/** Define the account information */
		$this->action = $arguments['action'];
		$this->owner_id = $arguments['owner_id'];
		$this->owner_user = get_current_user_username();
		$this->affected_file = (!empty($arguments['affected_file'])) ? $arguments['affected_file'] : '';
		$this->affected_account = (!empty($arguments['affected_account'])) ? $arguments['affected_account'] : '';
		$this->affected_file_name = (!empty($arguments['affected_file_name'])) ? $arguments['affected_file_name'] : '';
		$this->affected_account_name = (!empty($arguments['affected_account_name'])) ? $arguments['affected_account_name'] : '';

		/** Insert the client information into the database */
		$lq = "INSERT INTO tbl_actions_log (action,owner_id,owner_user";
		
			if (!empty($this->affected_file)) { $lq .= ",affected_file"; }
			if (!empty($this->affected_account)) { $lq .= ",affected_account"; }
			if (!empty($this->affected_file_name)) { $lq .= ",affected_file_name"; }
			if (!empty($this->affected_account_name)) { $lq .= ",affected_account_name"; }
		
		$lq .= ") VALUES ('$this->action', '$this->owner_id', '$this->owner_user'";
		
			if (!empty($this->affected_file)) { $lq .= ",$this->affected_file"; }
			if (!empty($this->affected_account)) { $lq .= ",$this->affected_account"; }
			if (!empty($this->affected_file_name)) { $lq .= ",'$this->affected_file_name'"; }
			if (!empty($this->affected_account_name)) { $lq .= ",'$this->affected_account_name'"; }

		$lq .= ")";
		$this->sql_query = $database->query($lq);
		
		//echo $lq.'<br />'; echo mysql_error().'<br />'; exit;
	}

}

?>