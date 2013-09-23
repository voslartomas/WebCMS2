<?php

namespace WebCMS;

/**
 * Description of ThumbnailCreator
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class ThumbnailCreator {
	
	private $filesPath;
	
	private $thumbnailsPath;
	
	private $settings;
	
	private $thumbnails;
	
	public function __construct($settings, $thumbnails) {
		$this->settings = $settings;
		$this->thumbnails = $thumbnails;
		
		$this->filesPath = WWW_DIR . 'upload';
		$this->thumbnailsPath = WWW_DIR . 'thubmnails';
	}
	
	/**
	 * 
	 * @param string $filename
	 * @param string $path
	 * @param string $thumbnail
	 */
	public function createThumbFromFile($filename, $path, $thumbnail){
		
		$path = $this->filesPath . $path;
		
		$file_path = $path . $filename;
		
		if(file_exists($file_path)){
			$image = \Nette\Image::fromFile($file_path);
			
			// ziskame nastaveni pro nahledy obrazku
			$pictureConfig = $this->thumbnails;
			
			foreach($pictureConfig as $conf){
				
				$tmpImage = clone $image;
								
				if($conf->getX() && $conf->getY()){
					
					$tmpImage->resize($conf->getX(), $conf->getY(), Image::FILL)
							 ->crop('50%', '50%', $conf->getX(), $conf->getY());
				}else
					$tmpImage->resize($conf->getX(), $conf->getY());
				
				// watermark
				if($this->settings->get('Apply watermark', \WebCMS\Settings::SECTION_IMAGE)) 
						$tmpImage = $this->applyWatermark($tmpImage);
				
				if(!empty($conf->getKey())) 
					$tmpImage->save($this->thumbnailsPath . $conf->getKey() . $filename, 90);
			}
		}
	}
	
	/**
	 * 
	 * @param \Nette\Image $image
	 * @return type
	 */
	public function applyWatermark($image){
		
		$section = \WebCMS\Settings::SECTION_IMAGE;
		
		if($this->settings->get('Apply watermark', $section)){
		
			$watermark = $this->settings->get('Watermark picture path', $section);
		
			if(empty($watermark)){
				return $image;
			}
			
			// set watermak picture
			$watermark= \Nette\Image::fromFile($watermark);
			
			if($watermark->getWidth() > 0){
				/* Watermark positioning */
				 if($this->settings->get('Watermark position', $section) == 0){
					 $left = 0;
					 $top = 0;
				 }elseif($this->settings->get('Watermark position', $section) == 1){
					 $left = $image->getWidth() - $watermark->getWidth();
					 $top = 0;
				 }elseif($this->settings->get('Watermark position', $section) == 2){
					 $left = "50%";
					 $top = "50%";
				 }elseif($this->settings->get('Watermark position', $section) == 3){
					 $left = 0;
					 $top = $image->getHeight() - $watermark->getHeight();
				 }elseif($this->settings->get('Watermark position', $section) == 4){
					 $left = $image->getWidth() - $watermark->getWidth();
					 $top = $image->getHeight() - $watermark->getHeight();
				 }

				 $image->place($watermark, $left, $top);
			
			}else{
				throw new \Nette\FileNotFoundException('Watermark file not found.');
			}
		
		}elseif($this->settings->get('Apply watermark', $section) == 2){
		
			// inicializace a predani promennych
			$text = $this->settings->get('Watermark text', $section);
			$font = $this->settings->get('Watermark text font', $section);
			$color = $this->settings->get('Watermark text color', $section);
			$font = "libs/fonts/".$font;
			$size = $this->settings->get('Watermark text size', $section);
			
			$dimensions = imagettfbbox($size, 0, $font, $text);
			
			$stringWidth = $dimensions[2] - $dimensions[0];
			$stringHeight = $dimensions[1] - $dimensions[7];
			
			/* Watermark positioning */
			if($this->settings->get('Watermark position', $section) == 0){
				$left = 0;
				$top = $string_height;
			}elseif($this->settings->get('Watermark position', $section) == 1){
				$left = $image->getWidth() - $stringWidth;
				$top = $stringHeight;
			}elseif($this->settings->get('Watermark position', $section) == 2){
				$left = $image->getWidth() / 2 - $stringWidth / 2;
				$top = $image->getHeight() / 2 - $stringHeight / 2;
			}elseif($this->settings->get('Watermark position', $section) == 3){
				$left = 0;
				$top = $image->getHeight() - 1;
			}elseif($this->settings->get('Watermark position', $section) == 4){
				$left = $image->getWidth() - $stringWidth;
				$top = $image->getHeight() - 1;
			}
			
			$color = $image->colorallocate(hexdec('0x' . $color{0} . $color{1}), hexdec('0x' . $color{2} . $color{3}), hexdec('0x' . $color{4} . $color{5}));
			
			$image->ttftext($size, 0, $left, $top, $color, $font, $text);
		}
		
		return $image;
	}
}

?>
