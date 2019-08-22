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
namespace Ves\ImageSlider\Block\Widget;

class Sliders extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	protected $_blockModel;
	protected $_dataHelper;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Cms\Model\Block $blockModel,
		\Ves\ImageSlider\Helper\Data $dataHelper,
		array $data = []
		) {
		parent::__construct($context, $data);
		$this->_blockModel = $blockModel;
		$this->_dataHelper = $dataHelper;
	}

	public function _toHtml(){
		$this->setTemplate("widget/slides.phtml");
		return parent::_toHtml();
	}
	public function getConfig($key, $default = NULL){
		if($this->hasData($key)){
			return $this->getData($key);
		}
		return $default;
	}

	public function getSlides(){
		$data = $this->getData();
		$slides = [];
		foreach ($data as $k => $v) {
			if(preg_match("/block_/", $k)){
				$html = '';
				$number = str_replace("block_", "", $k);
				if(is_numeric($v))	{
					$block = $this->_blockModel->load($v);
					$html = $block->getContent();
				}elseif(isset($data["html_".$number]) && $data["html_".$number]!=''){
					$html = $data["html_".$number];
					$html2 = str_replace(" ", "+", $html);
					if($this->isBase64Encoded($html2)){
		                $html = base64_decode($html2);
		                
		                if($this->isBase64Encoded($html)){
		                    $html = base64_decode($html);
		                }
		            }
				}
				if($html!=''){
					$html = $this->_dataHelper->decodeImg($html);
					$html = $this->_dataHelper->filter($html);
					$slides[] = $html;
				}
			}
		} 
		return $slides;
	}

	public function isBase64Encoded($data) {
        if(base64_encode($data) === $data) return false;
        if(base64_encode(base64_decode($data)) === $data){
            return true;
        }
        if (!preg_match('~[^0-9a-zA-Z+/=]~', $data)) {
            $check = str_split(base64_decode($data));
            $x = 0;
            foreach ($check as $char) if (ord($char) > 126) $x++;
            if ($x/count($check)*100 < 30) return true;
        }
        $decoded = base64_decode($data);
        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $data)) return false;
        // if string returned contains not printable chars
        if (0 < preg_match('/((?![[:graph:]])(?!\s)(?!\p{L}))./', $decoded, $matched)) return false;
        if (!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) return false;

        if(base64_encode(base64_decode($data)) === $data){
            return true;
        }
        return false;
    }

	/**
	 * @return array
	 */
	public function getBlocks(){
		return [];
		$blocks = $this->getConfig('blocks');
		$result = [];
		if($blocks){
			$blocks = explode(',', $blocks);
			$collection = $this->_blockModel->getCollection()
			->addFieldToFilter('identifier',['in'=>$blocks]);
			foreach ($blocks as $k => $v) {
				foreach ($collection as $_block) {
					if($v == $_block->getIdentifier()){
						$result[] = $_block;
					}
				}
			}
		}
		return $result;
	}
}