<?php
//--- CONFIGURATION
define('VV_OPTION_PICTURE_RESIZER', 'vv_picturesResizerOptions');
define('VV_OPTION_NB_IMAGES_MAX', 'nbImages');
define('VV_OPTION_LIMIT_MIN','limitMin');
define('VV_OPTION_LIMIT_MAX','limitMax');
define('VV_OPTION_JPEG_QUALITY','jpegQuality');
define('VV_OPTION_PNG_COMPRESSION','pngCompression');

//--- CONSTANTS
define('VV_CODE_OK',0);
define('VV_CODE_FILE_TYPE',1);
define('VV_CODE_EXIF',2);
define('VV_CODE_TOO_SMALL',3);
define('VV_CODE_TOO_BIG',4);
define('VV_CODE_UNKNOW',5);

function vv_install()
{
	$options = array(
      VV_OPTION_NB_IMAGES_MAX => 3,
      VV_OPTION_LIMIT_MIN => 1920,
	  VV_OPTION_LIMIT_MAX => 6000,
	  VV_OPTION_JPEG_QUALITY => 75,
	  VV_OPTION_PNG_COMPRESSION => 0
   );
	add_option(VV_OPTION_PICTURE_RESIZER, $options);
}

function vv_uninstall()
{
	delete_option(VV_OPTION_PICTURE_RESIZER);
}

function vv_adminMenu()
{
	add_options_page('Administration', 'Pictures Resizer', 'manage_options', basename(__FILE__), 'vv_adminForm');
}

function vv_adminForm()
{
   $options = get_option(VV_OPTION_PICTURE_RESIZER);
   
   if (isset($_POST['vv_updateConfig']))
   {
      if (isset($_POST[VV_OPTION_NB_IMAGES_MAX]) && is_numeric(intval($_POST[VV_OPTION_NB_IMAGES_MAX]))) 
	  {
         $options[VV_OPTION_NB_IMAGES_MAX] = intval($_POST[VV_OPTION_NB_IMAGES_MAX]);
      }
      if (isset($_POST[VV_OPTION_LIMIT_MIN]) && is_numeric(intval($_POST[VV_OPTION_LIMIT_MIN]))) 
	  {
         $options[VV_OPTION_LIMIT_MIN] = intval($_POST[VV_OPTION_LIMIT_MIN]);
      }
	  if (isset($_POST[VV_OPTION_LIMIT_MAX]) && is_numeric(intval($_POST[VV_OPTION_LIMIT_MAX]))) 
	  {
         $options[VV_OPTION_LIMIT_MAX] = intval($_POST[VV_OPTION_LIMIT_MAX]);
      }
	  if (isset($_POST[VV_OPTION_JPEG_QUALITY]) && is_numeric(intval($VV_OPTION_JPEG_QUALITY[VV_OPTION_LIMIT_MAX]))) 
	  {
         $options[VV_OPTION_JPEG_QUALITY] = intval($_POST[VV_OPTION_JPEG_QUALITY]);
      }
	  if (isset($_POST[VV_OPTION_PNG_COMPRESSION]) && is_numeric(intval($_POST[VV_OPTION_PNG_COMPRESSION]))) 
	  {
         $options[VV_OPTION_PNG_COMPRESSION] = intval($_POST[VV_OPTION_PNG_COMPRESSION]);
      }
      update_option(VV_OPTION_PICTURE_RESIZER, $options);
   }

   echo '<h2>Pictures Resizer Configuration</h2>';
   echo '<p>Each new picture will be resized automatically after upload, using this configuration.</p><br>';
   echo '<form method="post" action="'.$_SERVER["REQUEST_URI"].'">';
   echo '<label for="limitMin">Resize Pictures to :</label>';
   echo '<p><input type="text" name="'.VV_OPTION_LIMIT_MIN.'" value="'.$options[VV_OPTION_LIMIT_MIN].'"/>&nbsp;pixels.</p>';
   echo '<label for="limitMax">Ignore Pictures bigger than : </label>';
   echo '<p><input type="text" name="'.VV_OPTION_LIMIT_MAX.'" value="'.$options[VV_OPTION_LIMIT_MAX].'"/>&nbsp;pixels. (big pictures may take lot of resources and time)</p>';
   echo '<label for="limitMax">JPEG Quality : </label>';
   echo '<p><input type="text" name="'.VV_OPTION_JPEG_QUALITY.'" value="'.$options[VV_OPTION_JPEG_QUALITY].'"/>&nbsp;0% - 100%. (full quality pictures may take a lot of resources and time)</p>';
   echo '<label for="limitMax">PNG Compression : </label>';
   echo '<p><input type="text" name="'.VV_OPTION_PNG_COMPRESSION.'" value="'.$options[VV_OPTION_PNG_COMPRESSION].'"/>&nbsp;0 (uncompressed) - 9 (highly compressed). (PNG compression may take a lot of resources and time)</p>';
   echo '<label for="nbImages">Full Scan - Max images for each loop : </label>';
   echo '<p><input type="text" name="'.VV_OPTION_NB_IMAGES_MAX.'" value="'.$options[VV_OPTION_NB_IMAGES_MAX].'"/>&nbsp;reduce it if you get "timeout" or "max execution time" errors</p>';  
   echo '<input type="submit" name="vv_updateConfig" value="Update configuration" />';
   echo '</form><br>';
   include_once(dirname(__FILE__).'/ajax_scan.php');
}

function vv_resizeAttachment($attachment_id)
{ 
	$file = get_attached_file($attachment_id);
	$directory = dirname($file);
	$filename = basename($file);
	vv_resizeImage($directory,$filename);
}

function vv_resizeImage($directory, $source_name)
{	
	$options = get_option(VV_OPTION_PICTURE_RESIZER);
	
	// 1. Split filename and extension
	$file_name = vv_getFileName($source_name);
	$file_ext =	vv_getFileExt($source_name,true);
	
	// 2. Check file type
	$valid_exts	= array('.jpg','.jpeg','.png');
	if(!in_array(strtolower($file_ext),$valid_exts)) return VV_CODE_FILE_TYPE;
	
	// 3. Get EXIF dimensions
	$image_details = GetImageSize($directory.'/'.$source_name);
	if (!$image_details) return VV_CODE_EXIF;
	
	// 4. Check dimensions
	$source_width = $image_details[0];
	$source_height = $image_details[1];
	if($source_width<=$options[VV_OPTION_LIMIT_MIN] && $source_height<=$options[VV_OPTION_LIMIT_MIN]) return VV_CODE_TOO_SMALL;
	else if($source_width>$options[VV_OPTION_LIMIT_MAX] || $source_height>$options[VV_OPTION_LIMIT_MAX]) return VV_CODE_TOO_BIG;
		
	// 5. Create resized image 
	// 5.1 Create parameters
	$result = false;
	$output_resized = $file_name.'temp'.$file_ext;
	$parameters = array(
		VV_PARAM_SOURCE_NAME => $file_name.$file_ext,
		VV_PARAM_SOURCE_DIR => $directory.'/',
		VV_PARAM_OUTPUT_NAME => $output_resized,
		VV_PARAM_OUTPUT_DIR => $directory.'/',
		VV_PARAM_JPEG_QUALITY => $options[VV_OPTION_JPEG_QUALITY],
		VV_PARAM_PNG_COMPRESSION => $options[VV_OPTION_PNG_COMPRESSION]	
	);
	
	// 5.2 Check orientation
	if($source_width < $source_height)
	{
		// Portrait - calculate new width later
		$parameters[VV_PARAM_OUTPUT_WIDTH] = null;
		$parameters[VV_PARAM_OUTPUT_HEIGHT] = $options[VV_OPTION_LIMIT_MIN];
	}else{
		// Landscape - calculate new height later
		$parameters[VV_PARAM_OUTPUT_WIDTH] = $options[VV_OPTION_LIMIT_MIN];
		$parameters[VV_PARAM_OUTPUT_HEIGHT] = null;
	}
	
	// 5.3 Call resize script	
	try
	{
		$result = vv_resize($parameters);
	}catch(Exception $e){
		$result = false;
	}
	
	// 5.4 Get results
	if($result == true)
	{
		// Delete original file, then rename temp file
		if(file_exists($directory.'/'.$source_name) && file_exists($directory.'/'.$output_resized))
		{
			unlink($directory.'/'.$source_name);
			rename($directory.'/'.$output_resized, $directory.'/'.$source_name);
		}
		$result = VV_CODE_OK;
	}else{
		// Delete temp file
		if(file_exists($directory.'/'.$output_resized))
		{
			unlink($directory.'/'.$output_resized);
		}
		$result = VV_CODE_UNKNOW;
	}
	return $result;
}

function vv_scanDirectory($rootDirectory)
{	
	$options = get_option(VV_OPTION_PICTURE_RESIZER);
	// Utilisation du tableau global comme resultat
	global $imagesResult;
	
	$currentDir = opendir($rootDirectory);
	while($entry = readdir($currentDir)) 
	{
		if(is_dir($rootDirectory.'/'.$entry)&& $entry != '.' && $entry != '..') 
		{
			vv_scanDirectory($rootDirectory.'/'.$entry);
		}else{
			if($entry != '.' && $entry != '..') 
			{
				if(count($imagesResult) >= $options[VV_OPTION_NB_IMAGES_MAX]) break;
					
				$res = vv_resizeImage($rootDirectory, $entry);
				$status = '';
				$comments = '';
				switch($res)
				{
					case VV_CODE_OK : $status='Success'; break;
					case VV_CODE_FILE_TYPE : $status='Skipped'; $comments='Invalid file type'; break;
					case VV_CODE_EXIF : $status='Skipped'; $comments='EXIF data unreadable'; break;
					case VV_CODE_TOO_SMALL : $status='Skipped'; $comments='Picture too small'; break;
					case VV_CODE_TOO_BIG : $status='Skipped'; $comments='Picture too big'; break;
					case VV_CODE_UNKNOW : $status='Error'; $comments = 'Unknown error'; break;
				}
				
				if($res == VV_CODE_OK || $res == VV_CODE_EXIF || $res == VV_CODE_UNKNOW)
				{
					$imagesResult[] = array(
						'name' => $rootDirectory.'/'.$entry,
						'status' => $status,
						'comments' => $comments
					);
				}
			}
		}
	}
	closedir($currentDir);
}
?>