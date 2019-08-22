<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_ImageSlider
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\ImageSlider\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
	protected $_filterProvider;

	/**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

	/**
	 * @param \Magento\Framework\App\Helper\Context      $context        
	 * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider 
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager   
	 */
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
        parent::__construct($context);
        $this->_filterProvider = $filterProvider;
		$this->_storeManager    = $storeManager;
    }

	public function filter($str)
	{
		$html = $this->_filterProvider->getPageFilter()->filter($str);
		return $html;
	}

	public function decodeImg($str) {
		$orginalStr = $str;
		$count = substr_count($str, "<img");
		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$firstPosition = 0;
		for ($i=0; $i < $count; $i++) {
			if($firstPosition==0) $tmp = $firstPosition;
			if($tmp>strlen($str)) continue;
			$firstPosition = strpos($str, "<img", $tmp);
			$nextPosition = strpos($str, "/>", $firstPosition);
			$tmp = $nextPosition;
			if(!strpos($str, "<img")) continue;
			$length = $nextPosition - $firstPosition;
			$img = substr($str, $firstPosition, $length+2);
			if(!strpos($img, $this->_storeManager->getStore()->getBaseUrl())) {
                continue;
            }
			$newImg = $this->filter($img);
			$f = strpos($newImg, 'src="', 0)+5;
			$n = strpos($newImg, '"', $f+5);
			$src = substr($newImg, $f, ($n-$f));
			if (!strpos($img, 'placeholder.gif')) {
				$src1 = '';
				if (strpos($newImg, '___directive')) {
					$e = strpos($newImg, '___directive', 0) + 13;
					$e1 = strpos($newImg, '/key', 0);
					$src1 = substr($newImg, $e, ($e1-$e));
					$src1 = base64_decode($src1);
				} else {
					$mediaP = strpos($src, "wysiwyg", 0);
					$src1 = substr($src, $mediaP);
					$src1 = '{{media url="'.$src1.'"}}';
				}
				$orginalStr = str_replace($src, $src1, $orginalStr);
				$newImg = str_replace($src, $src1, $newImg);
			}
		}
		return $orginalStr;
	}
}