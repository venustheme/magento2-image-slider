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
namespace Ves\ImageSlider\Block\Adminhtml\Widget\Form\Field;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class WysiwygEditor extends Template implements RendererInterface
{

    /**
     * @var \Magento\Framework\Data\Form\Element\CollectionFactory
     */
    protected $_factoryCollection;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_factoryElement;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;

    protected $element_id = "";

    /**
     * @param \Magento\Backend\Block\Template\Context                $context           
     * @param \Magento\Framework\Data\Form\Element\Factory           $factoryElement    
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection            
     * @param \Magento\Cms\Model\Wysiwyg\Config                      $wysiwygConfig          
     * @param \Magento\Backend\Helper\Data                           $backendData       
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Backend\Helper\Data $backendData
        ){
        $this->_factoryElement = $factoryElement;
        $this->_factoryCollection = $factoryCollection;
        $this->_backendData = $backendData;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context);
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
    public function render(AbstractElement $element){
        $html = '';
        $config = $this->_wysiwygConfig->getConfig();
        $element_id = $element->getHtmlId().rand().time();
        $this->element_id = $element_id;
        $config['height'] = '300px';
        $config = json_encode($config->getData());

        $value = $element->getValue();
        if(!is_array($value)){
            $value2 = str_replace(" ","+", $value);
            if($this->isBase64Encoded($value2)){
                $value = base64_decode($value2);
                
                if($this->isBase64Encoded($value)){
                    $value = base64_decode($value);
                }
            }
        }

        $class = '';
        if($element->getRequired()){
            $class = 'required-entry';
        }

        $html .= '<div class="admin__field field field-options_'.$element->getId().'  with-note">';
        $html .= $element->getLabelHtml();

        $html .= '<div class="admin__field-control control">';
        $html .= '<textarea id="' . $element_id . '" name="' . $element->getName() . '" class="textarea admin__control-textarea wysiwyg-editor ' . $class . '" rows="5" cols="15" data-ui-id="product-tabs-attributes-tab-fieldset-element-textarea-' . $element->getName() . '" aria-hidden="true">'.$value.'</textarea>';
  
            $html .= $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button',
                '',
                [
                    'data' => [
                        'label' => __('WYSIWYG Editor'),
                        'type' => 'button',
                        'class' => 'action-wysiwyg hidden',
                        'onclick' => 'imagesliderWysiwygEditor.open(\'' . $this->_backendData->getUrl(
                            'catalog/product/wysiwyg'
                        ) . '\', \'' . $element_id . '\')',
                    ]
                ]
            )->toHtml();
            $html .= $this->_getToggleButtonHtml(true);

            $html .= <<<HTML
            <script>
            window.tinyMCE_GZ = window.tinyMCE_GZ || {}; window.tinyMCE_GZ.loaded = true;
            require([
                "jquery",
                "mage/translate", 
                "mage/adminhtml/events",
                "Ves_ImageSlider/js/wysiwyg/tiny_mce/setup",
                "mage/adminhtml/wysiwyg/widget"
            ], function(jQuery){
            var config = $config,
                editor;

            jQuery.extend(config, {
                settings: {
                    theme_advanced_buttons1: 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
            'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
                    theme_advanced_buttons2: null,
                    theme_advanced_buttons3: null,
                    theme_advanced_buttons4: null
                }
            });

            editor{$element_id} = new vesSliderTinyMceWysiwygSetup(
                '{$element_id}',
                config
            );

            editorFormValidationHandler = editor{$element_id}.onFormValidation.bind(editor{$element_id});

            Event.observe("toggle{$element_id}", "click", editor{$element_id}.toggle.bind(editor{$element_id}));

            Event.observe("toggle{$element_id}", "click", function(){jQuery("#toggle{$element_id}").toggleClass("texteditor-enabled"); jQuery("#toggle{$element_id}").parent().find(".action-wysiwyg").toggleClass("hidden");});

            varienGlobalEvents.attachEventHandler("formSubmit", editorFormValidationHandler);
            varienGlobalEvents.clearEventHandlers("open_browser_callback");
            varienGlobalEvents.attachEventHandler("open_browser_callback", editor{$element_id}.openFileBrowser);

            varienGlobalEvents.clearEventHandlers("open_browser_callback");
            varienGlobalEvents.attachEventHandler("open_browser_callback", editor{$element_id}.openFileBrowser);

            jQuery('#{$element_id}')
                .addClass('wysiwyg-editor')
                .data(
                    'wysiwygEditor',
                    editor
                );
            });
            </script>
HTML;
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Return custom button HTML
     *
     * @param array $data Button params
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getButtonHtml($data)
    {
        $html = '<button type="button"';
        $html .= ' class="scalable ' . (isset($data['class']) ? $data['class'] : '') . '"';
        $html .= isset($data['onclick']) ? ' onclick="' . $data['onclick'] . '"' : '';
        $html .= isset($data['style']) ? ' style="' . $data['style'] . '"' : '';
        $html .= isset($data['id']) ? ' id="' . $data['id'] . '"' : '';
        $html .= '>';
        $html .= isset($data['title']) ? '<span><span><span>' . $data['title'] . '</span></span></span>' : '';
        $html .= '</button>';

        return $html;
    }
     /**
     * Return HTML button to toggling WYSIWYG
     *
     * @param bool $visible
     * @return string
     */
    protected function _getToggleButtonHtml($visible = true)
    {
        $html = $this->_getButtonHtml(
            [
                'title' => $this->translate('Show / Hide Editor'),
                'class' => 'action-show-hide',
                'style' => $visible ? '' : 'display:none',
                'id' => 'toggle' . $this->getHtmlId(),
            ]
        );
        return $html;
    }
    /**
     * Translate string using defined helper
     *
     * @param string $string String to be translated
     * @return \Magento\Framework\Phrase
     */
    public function translate($string)
    {
        return (string)new \Magento\Framework\Phrase($string);
    }

    public function getHtmlId(){
        return $this->element_id;
    }

    /**
     * Escape a string's contents.
     *
     * @param string $string
     * @return string
     */
    protected function _escape($string)
    {
        return htmlspecialchars($string, ENT_COMPAT);
    }

    /**
     * Return the escaped value of the element specified by the given index.
     *
     * @param null|int|string $index
     * @return string
     */
    public function getEscapedValue($value = null)
    {

        if ($filter = $this->getValueFilter()) {
            $value = $filter->filter($value);
        }
        return $this->_escape($value);
    }
}