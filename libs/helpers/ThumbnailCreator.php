<?php

namespace WebCMS;

/**
 * Description of ThumbnailCreator
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class ThumbnailCreator {
	
	private $settings;
	
	private $thumbnails;
	
	public function __construct($settings, $thumbnails) {
		$this->settings = $settings;
		$this->thumbnails = $thumbnails;
	}
	
	/**
	 * 
	 * @param string $filename
	 * @param string $path
	 * @param string $thumbnail
	 */
	public function createThumbnails($filename, $filepath){
		
		$thumbnails = WWW_DIR . '/thumbnails/';
		$files = WWW_DIR . '/upload/';
		$thumbnails = str_replace($files, $thumbnails, $filepath);
		
		$filepath .= $filename;
		if(file_exists($filepath)){
			$image = \Nette\Image::fromFile($filepath);

			// ziskame nastaveni pro nahledy obrazku
			$pictureConfig = $this->thumbnails;
			
			foreach($pictureConfig as $conf){
				
				$tmpImage = clone $image;
								
				if($conf->getX() && $conf->getY()){
					
					$tmpImage->resize($conf->getX(), $conf->getY(), \Nette\Image::FILL)
							 ->crop('50%', '50%', $conf->getX(), $conf->getY());
				}else
					$tmpImage->resize($conf->getX(), $conf->getY());
				
				// watermark
				if($this->settings->get('Apply watermark', \WebCMS\Settings::SECTION_IMAGE)->getValue() == 1) 
						$tmpImage = $this->applyWatermark($tmpImage);
				
				$key = $conf->getKey();
				if(!empty($key)) 
					$tmpImage->save($thumbnails . $conf->getKey() . $filename, 90);
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
		
		if($this->settings->get('Apply watermark', $section)->getValue() == 1){
		
			$watermark = $this->settings->get('Watermark picture path', $section)->getValue();
		
			if(empty($watermark)){
				return $image;
			}
			
			// set watermak picture
			$watermark= \Nette\Image::fromFile($watermark);
			
			if($watermark->getWidth() > 0){
				/* Watermark positioning */
				 if($this->settings->get('Watermark position', $section)->getValue() == 0){
					 $left = 0;
					 $top = 0;
				 }elseif($this->settings->get('Watermark position', $section)->getValue() == 1){
					 $left = $image->getWidth() - $watermark->getWidth();
					 $top = 0;
				 }elseif($this->settings->get('Watermark position', $section)->getValue() == 2){
					 $left = "50%";
					 $top = "50%";
				 }elseif($this->settings->get('Watermark position', $section)->getValue() == 3){
					 $left = 0;
					 $top = $image->getHeight() - $watermark->getHeight();
				 }elseif($this->settings->get('Watermark position', $section)->getValue() == 4){
					 $left = $image->getWidth() - $watermark->getWidth();
					 $top = $image->getHeight() - $watermark->getHeight();
				 }

				 $image->place($watermark, $left, $top);
			
			}else{
				throw new \Nette\FileNotFoundException('Watermark file not found.');
			}
		
		}elseif($this->settings->get('Apply watermark', $section)->getValue() == 2){
		
			// inicializace a predani promennych
			$text = $this->settings->get('Watermark text', $section)->getValue();
			$font = $this->settings->get('Watermark text font', $section)->getValue();
			$color = $this->settings->get('Watermark text color', $section)->getValue();
			$font = "libs/fonts/".$font;
			$size = $this->settings->get('Watermark text size', $section)->getValue();
			
			$dimensions = imagettfbbox($size, 0, $font, $text);
			
			$stringWidth = $dimensions[2] - $dimensions[0];
			$stringHeight = $dimensions[1] - $dimensions[7];
			
			/* Watermark positioning */
			if($this->settings->get('Watermark position', $section)->getValue() == 0){
				$left = 0;
				$top = $string_height;
			}elseif($this->settings->get('Watermark position', $section)->getValue() == 1){
				$left = $image->getWidth() - $stringWidth;
				$top = $stringHeight;
			}elseif($this->settings->get('Watermark position', $section)->getValue() == 2){
				$left = $image->getWidth() / 2 - $stringWidth / 2;
				$top = $image->getHeight() / 2 - $stringHeight / 2;
			}elseif($this->settings->get('Watermark position', $section)->getValue() == 3){
				$left = 0;
				$top = $image->getHeight() - 1;
			}elseif($this->settings->get('Watermark position', $section)->getValue() == 4){
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
