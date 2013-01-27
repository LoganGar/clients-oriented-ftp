<?php
$get_file_info = array();
$get_client_info = array();
$notifications_sent = 0;

/**
 * First, get the list of different files that have notifications to be sent.
 */
$notifications_query = "SELECT * FROM tbl_notifications";
$notifications_sql = $database->query($notifications_query);
while ($row = mysql_fetch_array($notifications_sql)) {
	if (!in_array($row['file_id'], $get_file_info)) {
		$get_file_info[] = $row['file_id'];
	}
	if (!in_array($row['client_id'], $get_client_info)) {
		$get_client_info[] = $row['client_id'];
	}
	$found_notifications[] = array(
									'client_id' => $row['client_id'],
									'file_id' => $row['file_id'],
									'timestamp' => $row['timestamp'],
									'upload_type' => $row['upload_type']
								);
}

$files_to_get = implode(',',$get_file_info);
$clients_to_get = implode(',',$get_client_info);

/**
 * Get the information of each file
 */
$files_query = "SELECT id, filename, description FROM tbl_files WHERE id IN ($files_to_get)";
$files_sql = $database->query($files_query);
while ($row = mysql_fetch_array($files_sql)) {
	$file_data[$row['id']] = array(
								'id' => $row['id'],
								'filename' => $row['filename'],
								'description' => $row['description']
							);
}

/**
 * Get the information of each client
 */
$clients_query = "SELECT id, user, name, email, level, notify, created_by, active FROM tbl_users WHERE id IN ($clients_to_get)";
$clients_sql = $database->query($clients_query);
while ($row = mysql_fetch_array($clients_sql)) {
	$clients_data[$row['id']] = array(
								'id' => $row['id'],
								'user' => $row['user'],
								'name' => $row['name'],
								'email' => $row['email'],
								'level' => $row['level'],
								'notify' => $row['notify'],
								'created_by' => $row['created_by'],
								'active' => $row['active']
							);
	$mail_by_user[$row['user']] = $row['email'];
}

/**
 * Prepare the list of clients and admins that will be
 * notified, adding to each one the corresponding files.
 */
foreach ($clients_data as $client) {
	$email_body = '';
	if ($client['notify'] == '1' && $client['active'] == '1') {
		/**
		 * Only clients that are active will receive e-mails.
		 */
		foreach ($found_notifications as $notification) {
			if ($notification['client_id'] == $client['id']) {
				if ($notification['upload_type'] == '1') {
					/** If file is uploaded by user, add to client's email body */
					$use_id = $notification['file_id'];
					$notes_to_clients[$client['user']][] = array(
																'file_name' => $file_data[$use_id]['filename'],
																'description' => $file_data[$use_id]['description']
															);
					//echo make_excerpt($file_data[$use_id]['description'],200)."<br /><br /><br />";
				}
				else {
					/** Add the file to the account's creator email */
					$use_id = $notification['file_id'];
					$notes_to_admin[$client['created_by']][$client['name']][] = array(
																	'file_name' => $file_data[$use_id]['filename'],
																	'description' => $file_data[$use_id]['description']
																);
				}
			}
		}
	}
}

/** Prepare the emails for CLIENTS */
foreach ($notes_to_clients as $mail_username => $mail_files) {
	$address = $mail_by_user[$mail_username];
	$files_list = '';
	foreach ($mail_files as $mail_file) {
		/** Make the list of files */
		$files_list.= '<li style="margin-bottom:11px;">';
		$files_list.= '<p style="font-weight:bold; margin:0 0 5px 0; font-size:14px;">'.$mail_file['file_name'].'</p>';
		if (!empty($mail_file['description'])) {
			$files_list.= '<p>'.$mail_file['description'].'</p>';
		}
		$files_list.= '</li>';
	}
	/** Create the object and send the email */
	$notify_client = new PSend_Email();
	$try_sending = $notify_client->psend_send_email('new_files_for_client',$address,'','','','',$files_list);
	if ($try_sending == 1) {
		$notifications_sent++;
	}
}

/** Prepare the emails for ADMINS */
foreach ($notes_to_admin as $admin) {
	$files_list = '';
	foreach ($admin as $mail_username => $mail_files) {
		$files_list.= '<li style="font-size:15px; font-weight:bold; margin-bottom:5px;">'.$mail_username.'</li>';
		foreach ($mail_files as $mail_file) {
			/** Make the list of files */
			$files_list.= '<li style="margin-bottom:11px;">';
			$files_list.= '<p style="font-weight:bold; margin:0 0 5px 0;">'.$mail_file['file_name'].'</p>';
			if (!empty($mail_file['description'])) {
				$files_list.= '<p>'.$mail_file['description'].'</p>';
			}
			$files_list.= '</li>';
		}
		/** Create the object and send the email */
		$notify_admin = new PSend_Email();
		$try_sending = $notify_admin->psend_send_email('new_files_for_client',$address,'','','','',$files_list);
		if ($try_sending == 1) {
			$notifications_sent++;
		}
	}
}

if ($notifications_sent > 0) {
	/**
	 * Remove the notifications from the database.
	 */
	$notifications_del_query = "DELETE FROM tbl_notifications";
	$notifications_del_sql = $database->query($notifications_del_query);
	$msg = __('E-mail notifications have been sent.','cftp_admin');
	echo system_message('ok',$msg);
}
?>