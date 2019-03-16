<?php
//--- INCLUDES
include_once(dirname(__FILE__).'/lib_files.php');
include_once(dirname(__FILE__).'/lib_resize.php');
include_once(dirname(__FILE__).'/pictures-resizer-functions.php');
include_once(dirname(__FILE__).'/../../../wp-load.php');

//--- CONSTANTS
define('VV_UPLOAD_DIR','../../uploads');

if(isset($_POST['vv_callAjax']))
{
	// Prepare result
	$imagesResult = array();
	// Scan upload directory
	vv_scanDirectory(VV_UPLOAD_DIR);
	// write json response
	$retourJSON = array(
		'files' => $imagesResult
	);
	echo json_encode($retourJSON);
}
?>