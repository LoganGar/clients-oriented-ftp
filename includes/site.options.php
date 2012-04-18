<?php
/**
 * Gets all the options from the database and define each as a constant.
 *
 * @package		ProjectSend
 * @subpackage	Core
 *
 */
$database->MySQLDB();

/**
 * Get the main admin e-mail address used for sending notifications.
 * This is the system user that is created when ProjectSend is installed.
 */
$sql = $database->query('SELECT * FROM tbl_users WHERE id="1"');
while($row = mysql_fetch_array($sql)) {
	define('ADMIN_EMAIL_ADDRESS',$row['email']);
}

/**
 * Gets the values from the options table, which has 2 columns.
 * The first one is the option name, and the second is the assigned value.
 *
 * @return array
 */
$options_values = array();
$options = $database->query("SELECT * FROM tbl_options");
while ($row = @mysql_fetch_array($options)) {
	$options_values[$row['name']] = $row['value'];
}

$database->Close();

/**
 * Set the options returned before as constants.
 */
if(!empty($options_values)) {
	/**
	 * The allowed file types array is set as variable and not a constant
	 * because it is re-set later on other pages (the options and the upload
	 * forms currently).
	 */
	$allowed_file_types = $options_values['allowed_file_types'];
	
	define('BASE_URI',$options_values['base_uri']);
	define('THUMBS_MAX_WIDTH',$options_values['max_thumbnail_width']);
	define('THUMBS_MAX_HEIGHT',$options_values['max_thumbnail_height']);
	define('THUMBS_FOLDER',$options_values['thumbnails_folder']);
	define('THUMBS_QUALITY',$options_values['thumbnail_default_quality']);
	define('LOGO_MAX_WIDTH',$options_values['max_logo_width']);
	define('LOGO_MAX_HEIGHT',$options_values['max_logo_height']);
	define('LOGO_FILENAME',$options_values['logo_filename']);
	define('THIS_INSTALL_SET_TITLE',$options_values['this_install_title']);
	define('TEMPLATE_USE',$options_values['selected_clients_template']);
	define('TIMEZONE_USE',$options_values['timezone']);
	define('TIMEFORMAT_USE',$options_values['timeformat']);
	
	date_default_timezone_set(TIMEZONE_USE);
}
?>