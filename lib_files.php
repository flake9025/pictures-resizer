<?php
/*
	Files Functions
	Vincent Villain, 2008
*/

function vv_getFileName($file)
{
	$var = explode(".",$file);
	$nb_dots = count($var)-1;
	return substr($file, 0, strlen($file)-strlen($var[$nb_dots])-1);
}

function vv_getFileExt($file,$dot=false)
{
  if ($dot) return substr($file, strrpos($file, '.'));
  else return substr($file, strrpos($file, '.')+1);
}

function vv_removeAccents($file,$utf8=false)
{
	if($utf8)   
		$filename = utf8_decode($file);
	else
		$filename = $file;
	 
	$replace = array(
			"�" => "Y", "�" => "u", "�" => "A", "�" => "A", 
			"�" => "A", "�" => "A", "�" => "A", "�" => "A", 
			"�" => "A", "�" => "C", "�" => "E", "�" => "E", 
			"�" => "E", "�" => "E", "�" => "I", "�" => "I", 
			"�" => "I", "�" => "I", "�" => "D", "�" => "N", 
			"�" => "O", "�" => "O", "�" => "O", "�" => "O", 
			"�" => "O", "�" => "O", "�" => "U", "�" => "U", 
			"�" => "U", "�" => "U", "�" => "Y", "�" => "s", 
			"�" => "a", "�" => "a", "�" => "a", "�" => "a", 
			"�" => "a", "�" => "a", "�" => "a", "�" => "c", 
			"�" => "e", "�" => "e", "�" => "e", "�" => "e", 
			"�" => "i", "�" => "i", "�" => "i", "�" => "i", 
			"�" => "o", "�" => "n", "�" => "o", "�" => "o", 
			"�" => "o", "�" => "o", "�" => "o", "�" => "o", 
			"�" => "u", "�" => "u", "�" => "u", "�" => "u", 
			"�" => "y", "�" => "y", "%" => "");
			
	return strtr($filename,$replace); 
}
?>
