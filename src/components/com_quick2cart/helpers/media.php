<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.parameter');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
require_once JPATH_SITE . '/components/com_quick2cart/helpers/gifresizer.php';

/**
 * Methods supporting product records.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       1.0
 */
class Qtc_MediaHelper
{
	/**
	 * [__construct description]
	 */
	public function __construct()
	{
		$params = JComponentHelper::getParams('com_quick2cart');

		$this->sa_config['image_size'] = $params->get('max_size');
	}

	/**
	 * Check for max media size allowed for upload
	 *
	 * @param   integer  $file_size  Max allowed file upload size in KB
	 *
	 * @return  integer  0 or 1
	 */
	public function check_max_size($file_size)
	{
		// @TODO needed?
		$this->media_size = $file_size;
		$max_media_size   = $this->sa_config['image_size'] * 1024;

		if ($file_size > $max_media_size)
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Detect file type, and,
	 * detect media group type image/video/flash
	 *
	 * @param   string  $file_type  MIME type
	 *
	 * @return  array
	 */
	public function check_media_type_group($file_type)
	{
		$allowed_media_types = array(
			'image' => array(
				'image/gif',
				'image/png',
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg'
			)
		);
		/*
		if($this->sa_config['allow_vid_ads'])
		{
		$allowed_media_types['video']=array
		(
				'video/mp4',
		'video/x-flv'
		);
		}

		if($this->sa_config['allow_flash_ads'])
		{
		$allowed_media_types['flash']=array
		(
		'application/x-shockwave-flash'//swf
		);
		}
		*/
		$media_type_group    = '';
		$flag                = 0;

		foreach ($allowed_media_types as $key => $value)
		{
			if (in_array($file_type, $value))
			{
				$media_type_group = $key;
				$flag             = 1;
				break;
			}
		}

		$this->media_type       = $file_type;
		$this->media_type_group = $media_type_group;

		$return['media_type']       = $file_type;
		$return['media_type_group'] = $media_type_group;

		if (!$flag)
		{
			// File type not allowed
			$return['allowed'] = 0;

			return $return;
		}

		// Allowed file type
		$return['allowed'] = 1;

		return $return;
	}

	/**
	 * Method to get ad type
	 *
	 * @param   String  $fextension  file link
	 *
	 * @return  ad type
	 *
	 * @since   1.6
	 *
	 */
	public function get_ad_type($fextension)
	{
		$allowed_media_types = array(
			'image' => array(
				'gif',
				'png',
				'jpeg',
				'pjpeg',
				'jpg'
			)
		);

		$ad_type             = '';
		$flag                = 0;

		foreach ($allowed_media_types as $key => $value)
		{
			if (in_array($fextension, $value))
			{
				$ad_type = $key;
				$flag    = 1;
				break;
			}
		}

		// Allowed file type
		return $ad_type;
	}

	/**
	 * Method get_adzone_media_dimensions
	 *
	 * @param   string  $adzone  adzone
	 *
	 * @return  array
	 */
	public function get_adzone_media_dimensions($adzone)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT img_width,img_height FROM #__ad_zone WHERE id =" . $adzone;
		$db->setQuery($query);
		$adzone_media_dimensions = $db->loadObject();

		return $adzone_media_dimensions;
	}

	/**
	 * Method get_media_extension
	 *
	 * @param   string  $file_name  File name
	 *
	 * @return  array
	 */
	public function get_media_extension($file_name)
	{
		$media_extension       = pathinfo($file_name);
		$this->media_extension = $media_extension['extension'];

		return $media_extension['extension'];
	}

	/**
	 * Check if media resizing is needed
	 *
	 * @param   aray    $adzone_media_dimnesions  Zone dimensions array
	 * @param   string  $file_tmp_name            File name
	 *
	 * @return  array
	 */
	public function check_media_resizing_needed($adzone_media_dimnesions, $file_tmp_name)
	{
		// Get uploaded image height and width
		// This will work for all images, an also for swf files
		list($width_img, $height_img) = getimagesize($file_tmp_name);

		$return['width_img']  = $width_img;
		$return['height_img'] = $height_img;

		$this->width  = $width_img;
		$this->height = $height_img;

		if ($width_img == $adzone_media_dimnesions->img_width && $height_img == $adzone_media_dimnesions->img_height)
		{
			// No resizing needed
			$return['resize'] = 0;

			return $return;
		}

		// Resizing needed
		$return['resize'] = 1;

		return $return;
	}

	/**
	 * Get media file name without extension
	 *
	 * @param   string  $file_name  Name of file
	 *
	 * @return  string
	 */
	public function get_media_file_name_without_extension($file_name)
	{
		$media_extension = pathinfo($file_name);

		return $media_extension['filename'];
	}

	/**
	 * Get new dimensions
	 *
	 * @param   integer  $max_zone_width   Zone width
	 * @param   integer  $max_zone_height  Zone height
	 * @param   string   $option           Resize option
	 *
	 * @return  array
	 */
	public function get_new_dimensions($max_zone_width, $max_zone_height, $option)
	{
		switch ($option)
		{
			case 'exact':
				$new_calculated_width  = $max_zone_width;
				$new_calculated_height = $max_zone_height;
				break;
			case 'auto':
				$new_dimensions = $this->get_optimal_dimensions($max_zone_width, $max_zone_height);
				$new_calculated_width  = $new_dimensions['new_calculated_width'];
				$new_calculated_height = $new_dimensions['new_calculated_height'];
				break;
		}

		$new_dimensions['new_calculated_width']  = $new_calculated_width;
		$new_dimensions['new_calculated_height'] = $new_calculated_height;

		return $new_dimensions;
	}

	/**
	 * Get optimal dimensions
	 *
	 * @param   integer  $max_zone_width   Zone width
	 * @param   integer  $max_zone_height  Zone height
	 *
	 * @return  array
	 */
	public function get_optimal_dimensions($max_zone_width, $max_zone_height)
	{
		// @TODO not sure abt line below
		$top_offset = 0;

		if ($max_zone_height == null)
		{
			if ($this->width < $max_zone_width)
			{
				$new_calculated_width = $this->width;
			}
			else
			{
				$new_calculated_width = $max_zone_width;
			}

			$ratio_orig            = $this->width / $this->height;
			$new_calculated_height = $new_calculated_width / $ratio_orig;

			$blank_height = $new_calculated_height;
			$top_offset   = 0;
		}
		else
		{
			if ($this->width <= $max_zone_width && $this->height <= $max_zone_height)
			{
				$new_calculated_height = $this->height;
				$new_calculated_width = $this->width;
			}
			else
			{
				if ($this->width > $max_zone_width)
				{
					$ratio = ($this->width / $max_zone_width);
					$new_calculated_width  = $max_zone_width;
					$new_calculated_height = ($this->height / $ratio);

					if ($new_calculated_height > $max_zone_height)
					{
						$ratio = ($new_calculated_height / $max_zone_height);
						$new_calculated_height = $max_zone_height;
						$new_calculated_width  = ($new_calculated_width / $ratio);
					}
				}

				if ($this->height > $max_zone_height)
				{
					$ratio = ($this->height / $max_zone_height);
					$new_calculated_height = $max_zone_height;
					$new_calculated_width  = ($this->width / $ratio);

					if ($new_calculated_width > $max_zone_width)
					{
						$ratio = ($new_calculated_width / $max_zone_width);
						$new_calculated_width  = $max_zone_width;
						$new_calculated_height = ($new_calculated_height / $ratio);
					}
				}
			}

			if ($new_calculated_height == 0 || $new_calculated_width == 0 || $this->height == 0 || $this->width == 0)
			{
				die(JText::_('FILE_VALID'));
			}

			if ($new_calculated_height < 45)
			{
				$blank_height = 45;
				$top_offset   = round(($blank_height - $new_calculated_height) / 2);
			}
			else
			{
				$blank_height = $new_calculated_height;
			}
		}

		$new_dimensions['new_calculated_width']  = $new_calculated_width;
		$new_dimensions['new_calculated_height'] = $new_calculated_height;
		$new_dimensions['top_offset']            = $top_offset;
		$new_dimensions['blank_height']          = $blank_height;

		return $new_dimensions;
	}

	/*public function uploadImage($file_field, $maxSize, $max_zone_width, $fullPath, $relPath, $colorR, $colorG, $colorB, $max_zone_height = null){*/
	/**
	 * [uploadImage description]
	 *
	 * @param   [type]  $file_field                   [description]
	 * @param   [type]  $max_zone_width               [description]
	 * @param   [type]  $fullPath                     [description]
	 * @param   [type]  $relPath                      [description]
	 * @param   [type]  $colorR                       [description]
	 * @param   [type]  $colorG                       [description]
	 * @param   [type]  $colorB                       [description]
	 * @param   [type]  $new_media_width              [description]
	 * @param   [type]  $new_media_height             [description]
	 * @param   [type]  $blank_height                 [description]
	 * @param   [type]  $top_offset                   [description]
	 * @param   [type]  $media_extension              [description]
	 * @param   [type]  $file_name_without_extension  [description]
	 * @param   [type]  $max_zone_height              [description]
	 *
	 * @return  [type]                                [description]
	 */

	public function uploadImage($file_field, $max_zone_width, $fullPath,
		$relPath, $colorR, $colorG, $colorB, $new_media_width, $new_media_height, $blank_height,
		$top_offset, $media_extension, $file_name_without_extension, $max_zone_height = null)
	{
		switch ($this->media_type_group)
		{
			case "flash":
				jimport('joomla.filesystem.file');

				// Retrieve file details from uploaded file, sent from upload form
				$file     = $_FILES[$file_field];

				// Clean up filename to get rid of strange characters like spaces etc
				$filename = JFile::makeSafe($file['name']);

				// Set up the source and destination of the file
				$src      = $file['tmp_name'];

				$filename                    = strtolower($filename);
				$filename                    = preg_replace('/\s/', '_', $filename);
				$timestamp                   = time();
				$file_name_without_extension = $this->get_media_file_name_without_extension($filename);
				$filename                    = $file_name_without_extension . "_" . $timestamp . "." . $this->media_extension;

				$dest = $fullPath . "swf" . DS . $filename;

				// First check if the file has the right extension, we need swf only
				if (JFile::upload($src, $dest))
				{
					$dest = $fullPath . "swf" . DS . $filename;

					return $dest;
				}

				break;

			case "video":
				jimport('joomla.filesystem.file');

				// Retrieve file details from uploaded file, sent from upload form
				$file     = $_FILES[$file_field];

				// Clean up filename to get rid of strange characters like spaces etc
				$filename = JFile::makeSafe($file['name']);

				// Set up the source and destination of the file
				$src      = $file['tmp_name'];

				$filename                    = strtolower($filename);
				$filename                    = preg_replace('/\s/', '_', $filename);
				$timestamp                   = time();
				$file_name_without_extension = $this->get_media_file_name_without_extension($filename);
				$filename                    = $file_name_without_extension . "_" . $timestamp . "." . $this->media_extension;

				$dest = $fullPath . "vids" . DS . $filename;

				if (JFile::upload($src, $dest))
				{
					$dest = $fullPath . "vids" . DS . $filename;

					return $dest;
				}

				break;
		}

		$errorList = array();

		// $folder = $relPath; ADDED BY @VIDYASAGAR
		$folder    = $fullPath;
		$match     = "";
		$filesize  = $_FILES[$file_field]['size'];

		if ($filesize > 0)
		{
			$filename = strtolower($_FILES[$file_field]['name']);
			$filename = preg_replace('/\s/', '_', $filename);

			if ($filesize < 1)
			{
				$errorList[] = JText::_('FILE_EMPTY');
			}

			if (count($errorList) < 1)
			{
				$match       = "1";
				$NUM         = time();
				$front_name  = $file_name_without_extension;
				$newfilename = $front_name . "." . $media_extension;
				$save        = $folder . $newfilename;

				if (!file_exists($save))
				{
					list($this->width, $this->height) = getimagesize($_FILES[$file_field]['tmp_name']);
					$image_p = imagecreatetruecolor($new_media_width, $blank_height);
					$white   = imagecolorallocate($image_p, $colorR, $colorG, $colorB);

					// START added to preserve transparency
					imagealphablending($image_p, false);
					imagesavealpha($image_p, true);
					$transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
					imagefill($image_p, 0, 0, $transparent);

					// END added to preserve transparency

					switch ($media_extension)
					{
						case "gif":
							$gr           = new qtc_gifresizer;
							$gr->temp_dir = $folder . 'frames';

							if (!JFolder::exists($gr->temp_dir))
							{
								@mkdir($gr->temp_dir);
							}

							// $gr->resize("gifs/1.gif","resized/1_resized.gif",50,50); //Resizing the animation into a new file.
							$gr->resize($_FILES[$file_field]['tmp_name'], $save, $new_media_width, $new_media_height);
							break;

						case "jpg":
							$image = @imagecreatefromjpeg($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
							break;

						case "jpeg":
							$image = @imagecreatefromjpeg($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
							break;

						case "png":
							$image = @imagecreatefrompng($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
							break;
					}

					switch ($media_extension)
					{
						/*
						case "gif":
						if(!@imagegif($image_p, $save)){
						$errorList[]= JText::_('FILE_GIF');
						}

						break;
						*/
						case "jpg":
							if (!@imagejpeg($image_p, $save, 100))
							{
								$errorList[] = JText::_('FILE_JPG');
							}
							break;
						case "jpeg":
							if (!@imagejpeg($image_p, $save, 100))
							{
								$errorList[] = JText::_('FILE_JPEG');
							}
							break;
						case "png":
							if (!@imagepng($image_p, $save, 0))
							{
								$errorList[] = JText::_('FILE_PNG');
							}
							break;
					}

					@imagedestroy($filename);
				}
				else
				{
					$errorList[] = JText::_('FILE_EXIST');
				}
			}
		}
		else
		{
			$errorList[] = JText::_('FILE_NO');
		}

		if (!$match)
		{
			$errorList[] = JText::_('FILE_ALLOW') . ":" . $filename;
		}

		if (sizeof($errorList) == 0)
		{
			return $fullPath . $newfilename;
		}
		else
		{
			$eMessage = array();

			for ($x = 0; $x < sizeof($errorList); $x++)
			{
				$eMessage[] = $errorList[$x];
			}

			return $eMessage;
		}
	}

	/**
	 * [uploadProdFiles description]
	 *
	 * @return  [type]  [description]
	 */
	public function uploadProdFiles()
	{
		$response['validate']        = new stdclass;
		$response['validate']->error = 0;
		$response['fileUpload']      = new stdclass;

		// Check if request is GET and the requested chunk exists or not. this makes testChunks work
		if ($_SERVER['REQUEST_METHOD'] === 'GET')
		{
			$temp_dir   = JPATH_SITE . '/tmp/' . $_GET['resumableIdentifier'];
			$chunk_file = $temp_dir . '/' . $_GET['resumableFilename'] . '.part' . $_GET['resumableChunkNumber'];

			if (file_exists($chunk_file))
			{
				header("HTTP/1.0 200 Ok");
			}
			else
			{
				header("HTTP/1.0 404 Not Found");
			}
		}

		// Loop through files and move the chunks to a temporarily created directory
		if (!empty($_FILES))
		{
			foreach ($_FILES as $file)
			{
				// Check the error status
				if ($file['error'] != 0)
				{
					// $this->_log('error '.$file['error'].' in file '.$_POST['resumableFilename']);
					$response['validate']->error = 1;
					continue;
				}

				/* IIII init the destination file (format <filename.ext>.part<#chunk>
				 the file is stored in a temporary directory
				*/
				$temp_dir  = JPATH_SITE . '/tmp/' . $_POST['resumableIdentifier'];
				$dest_file = $temp_dir . '/' . $_POST['resumableFilename'] . '.part' . $_POST['resumableChunkNumber'];

				// Create the temporary directory
				if (!is_dir($temp_dir))
				{
					mkdir($temp_dir, 0777, true);
				}

				// Move the temporary file
				if (!move_uploaded_file($file['tmp_name'], $dest_file))
				{
					// $this->_log('Error saving (move_uploaded_file) chunk '.$_POST['resumableChunkNumber'].' for file '.$_POST['resumableFilename']);
					$response['validate']->error = 1;
				}
				else
				{
					// Check if all the parts present, and create the final destination file
					$filePath = $this->createFileFromChunks($temp_dir, $_POST['resumableFilename'], $_POST['resumableChunkSize'], $_POST['resumableTotalSize']);

					if ($filePath)
					{
						$response['fileUpload']->complete = 1;
						$response['fileUpload']->filePath = $filePath;
					}
					else
					{
						$response['fileUpload']->complete = 0;
					}
				}
			}
		}

		header('Content-type: application/json');
		echo json_encode($response);
		jexit();
	}

	/**
	 * [Delete a directory RECURSIVELY]
	 *
	 * @param   [type]  $dir  [description]
	 *
	 * @return  [type]        [description]
	 */
	public function rrmdir($dir)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);

			foreach ($objects as $object)
			{
				if ($object != "." && $object != "..")
				{
					if (filetype($dir . "/" . $object) == "dir")
					{
						$this->rrmdir($dir . "/" . $object);
					}
					else
					{
						unlink($dir . "/" . $object);
					}
				}
			}

			reset($objects);
			rmdir($dir);
		}
	}

	/**
	 * [createFileFromChunks description]
	 *
	 * @param   [string]  $temp_dir   [the temporary directory holding all the parts of the file]
	 * @param   [string]  $fileName   [the original file name]
	 * @param   [string]  $chunkSize  [each chunk size (in bytes)]
	 * @param   [string]  $totalSize  [original file size (in bytes)]
	 *
	 * @return  [type]              [description]
	 */
	public function createFileFromChunks($temp_dir, $fileName, $chunkSize, $totalSize)
	{
		// Count all the parts of this file
		$total_files = 0;

		foreach (scandir($temp_dir) as $file)
		{
			if (stripos($file, $fileName) !== false)
			{
				$total_files++;
			}
		}

		// Check that all the parts are present the size of the last part is between chunkSize and 2*$chunkSize

		if ($total_files * $chunkSize >= ($totalSize - $chunkSize + 1))
		{
			// Create the final destination file
			if (($fp = fopen(JPATH_SITE . '/tmp/' . $fileName, 'w')) !== false)
			{
				for ($i = 1; $i <= $total_files; $i++)
				{
					fwrite($fp, file_get_contents($temp_dir . '/' . $fileName . '.part' . $i));

					// $this->_log('writing chunk '.$i);
				}

				fclose($fp);
			}
			else
			{
				// $this->_log('cannot create the destination file');
				return false;
			}

			// Rename the temporary directory (to avoid access from other
			// concurrent chunks uploads) and than delete it
			if (rename($temp_dir, $temp_dir . '_UNUSED'))
			{
				$this->rrmdir($temp_dir . '_UNUSED');
			}
			else
			{
				$this->rrmdir($temp_dir);
			}
		}

		// Lets make a unique safe file name for each upload
		$name     = JPATH_SITE . '/tmp/' . $fileName;
		$fileInfo = pathinfo($name);
		$fileExt  = $fileInfo['extension'];
		$fileBase = $fileInfo['filename'];

		// Add logggedin userid to file name
		$fileBase = JFactory::getUser()->id . '_' . $fileBase;

		/*add timestamp to file name
		http://www.php.net/manual/en/function.microtime.php
		http://php.net/manual/en/function.uniqid.php
		microtime â�� Return current Unix timestamp with microseconds
		uniqid â�� Generate a unique ID
		*/

		$timestamp = microtime();

		$fileBase = $fileBase . '_' . $timestamp;

		// Clean up filename to get rid of strange characters like spaces etc
		$fileBase = JFile::makeSafe($fileBase);

		// Lose any special characters in the filename
		$fileBase = preg_replace("/[^A-Za-z0-9]/i", "_", $fileBase);

		// Use lowercase
		$fileBase = strtolower($fileBase);

		$fileName = $fileBase . '.' . $fileExt;

		rename($name, JPATH_SITE . '/tmp/' . $fileName);

		return $fileName;
	}
}
