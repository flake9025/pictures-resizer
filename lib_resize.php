<?php
/*
	Picture Resize Function
	Vincent Villain, 2008
	
	@param parameters					required	Array of parameters
	@param parameters['source_name']	required	The source image to resize
	@param parameters['source_dir']		required	Source directory
	@param parameters['output_name']	required	The output image name
	@param parameters['output_dir']		optional	The output directory
	@param parameters['output_width'] 	optional    Width of the resized picture
	@param parameters['output_height']	optional	Height of the resized picture
	@return true if all is ok, false otherwise
*/

//--- CONSTANTS
define('VV_PARAM_SOURCE_NAME','source_name');
define('VV_PARAM_SOURCE_DIR','source_dir');
define('VV_PARAM_OUTPUT_NAME','output_name');
define('VV_PARAM_OUTPUT_DIR','output_dir');
define('VV_PARAM_OUTPUT_WIDTH','output_width');
define('VV_PARAM_OUTPUT_HEIGHT','output_height');
define('VV_PARAM_JPEG_QUALITY','jpeg_quality');
define('VV_PARAM_PNG_COMPRESSION','png_compression');

function vv_resize($parameters)
{	
	$source_name = isset($parameters[VV_PARAM_SOURCE_NAME]) ? $parameters[VV_PARAM_SOURCE_NAME] : null;
	$source_dir = isset($parameters[VV_PARAM_SOURCE_DIR]) ? $parameters[VV_PARAM_SOURCE_DIR] : '.';
	$output_name = isset($parameters[VV_PARAM_OUTPUT_NAME]) ? $parameters[VV_PARAM_OUTPUT_NAME] : null;
	$output_dir = isset($parameters[VV_PARAM_OUTPUT_DIR]) ? $parameters[VV_PARAM_OUTPUT_DIR] : '.';
	$output_width = isset($parameters[VV_PARAM_OUTPUT_WIDTH]) ? $parameters[VV_PARAM_OUTPUT_WIDTH] : null;
	$output_height = isset($parameters[VV_PARAM_OUTPUT_HEIGHT]) ? $parameters[VV_PARAM_OUTPUT_HEIGHT] : null;
	$jpeg_quality = isset($parameters[VV_PARAM_JPEG_QUALITY]) ? $parameters[VV_PARAM_JPEG_QUALITY] : 100;
	$png_compression = isset($parameters[VV_PARAM_PNG_COMPRESSION]) ? $parameters[VV_PARAM_PNG_COMPRESSION] : 0;		   
			   
	// Positionnement du repertoire
	$source_file = $source_dir.$source_name;
	
	// Verification de l'existence de l'image
	if (!isset($source_name) || !is_file($source_file)) return false;   

	// Proprietes de l'image
	$image_details		= GetImageSize($source_file);
	if (!$image_details) return false;
		
	$source_width		= $image_details[0];
	$source_height		= $image_details[1];
	$source_type		= $image_details[2];
	$source_mime		= $image_details['mime'];
	
	// Verification du format de sortie
	if(!isset($output_width) && !isset($output_height)) return false;
	
	// Format Portrait
	if(!isset($output_width))
	{
		// Contraint le rééchantillonage à la hauteur donnée
		$output_width	= round(($output_height / $source_height) * $source_width);
	// Format paysage
	}else{
		// Contraint le rééchantillonage à la largeur donnée	
		$output_height	= round(($output_width / $source_width) * $source_height);
	}
	
	$output_image = ImageCreateTrueColor($output_width,$output_height);
	if(!$output_image) return false;
	
	//Gestion du format de fichier
	switch ($source_mime)
	{
		case 'image/gif' 	: $source_image = imagecreatefromgif($source_file);break;
		case 'image/jpeg' 	: $source_image = imagecreatefromjpeg($source_file);break;
		case 'image/png' 	: $source_image = imagecreatefrompng($source_file);break;
		default				: return false;
	}
	
	// Conservation de la transparence
	imagesavealpha($source_image, true);
    imagesavealpha($output_image, true);
    imagealphablending($output_image, false);
    
	// Copie et rééchantillonne l'image originale
	ImageCopyResampled($output_image,$source_image,0,0,0,0,$output_width,$output_height,$source_width,$source_height);
	
	// Creation du repertoire
	if(!is_dir($output_dir))
	{
		mkdir($output_dir, 0777 , true);
	}
	
	// Génération de la vignette
	switch ($source_mime)
	{
		case 'image/jpeg' 	: imagejpeg($output_image,$output_dir.$output_name,$jpeg_quality);break;
		case 'image/png' 	: imagepng($output_image,$output_dir.$output_name,$png_compression);break;
		default				: return false;
	}
 
	imagedestroy($output_image);
	return true;
}
?>