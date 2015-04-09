<?php

namespace WebCMS;

use Mobile_Detect;

/**
 * Mobiledetectlib wrapper
 *
 * @author Josef Sukdol <josef.sukdol@gmail.com>
 */
class MobileDetect extends Mobile_Detect
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Return type of the detected device
	 *
	 * @return string
	 */
	public function getDeviceType()
	{
		if ($this->isPhone()) {
			$device = 'mobile';
		} elseif ($this->isTablet()) {
			$device = 'tablet';
		} else {
			$device = 'desktop';
		}

		return $device;
	}

	/**
	 * Check if the device is mobile phone
	 *
	 * @return bool
	 */
	public function isPhone()
	{
		return $this->isMobile() && !$this->isTablet();
	}

	/**
	 * Check if the device is not mobile phone
	 *
	 * @return bool
	 */
	public function isNotPhone()
	{
		return (($this->isMobile() && $this->isTablet()) || !$this->isMobile());
	}
}
