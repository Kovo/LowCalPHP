<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Helper;

/**
 * Class Image
 * A helper class that deals with image manipulation.
 * @package LowCal\Helper
 */
class Image
{
	/**
	 * @param $file
	 * @param $target_width
	 * @param $target_height
	 * @param $new_name
	 * @param bool $crop
	 * @throws \Exception
	 */
	public static function resizeImage($file, $target_width, $target_height, $new_name, $crop = false)
	{
		list($source_width, $source_height) = getimagesize($file);

		$width_height_ratio = $source_width/$source_height;

		if($crop === false)
		{
			if($source_width > $source_height)
			{
				$source_width = (int)ceil($source_width-($source_width*abs($width_height_ratio-$target_width/$target_height)));
			}
			else
			{
				$source_height = (int)ceil($source_height-($source_height*abs($width_height_ratio-$target_width/$target_height)));
			}

			$final_width = $target_width;
			$final_height = $target_height;
		}
		else
		{
			if($target_width/$target_height > $width_height_ratio)
			{
				$final_width = (int)ceil($target_height*$width_height_ratio);
				$final_height = $target_height;
			}
			else
			{
				$final_height = (int)ceil($target_width/$width_height_ratio);
				$final_width = $target_width;
			}
		}

		$parts_of_filename = explode('.', $file);
		$extension = array_pop($parts_of_filename);

		switch($extension)
		{
			case 'jpeg':
			case 'jpg':
				$new_source_image = imagecreatefromjpeg($file);
				break;
			case 'png':
				$new_source_image = imagecreatefrompng($file);
				break;
			case 'gif':
				$new_source_image = imagecreatefromgif($file);
				break;
			default:
				throw new \Exception('Unknown image format provided.', Codes::IMAGE_UNKNOWN_FORMAT);
		}

		$destination_image = imagecreatetruecolor($final_width, $final_height);

		imagecopyresampled($destination_image, $new_source_image, 0, 0, 0, 0, $final_width, $final_height, $source_width, $source_height);

		switch($extension)
		{
			case 'jpeg':
			case 'jpg':
				imagejpeg($destination_image, $new_name, 100);
				break;
			case 'png':
				imagepng($destination_image, $new_name, 9);
				break;
			case 'gif':
				imagegif($destination_image, $new_name);
				break;
			default:
				throw new \Exception('Unknown image format provided.', Codes::IMAGE_UNKNOWN_FORMAT);
		}
	}
}