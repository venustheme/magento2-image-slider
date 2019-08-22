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
namespace Ves\ImageSlider\Block\Adminhtml\System\Config\Form\Field;

class Images extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);

        // Disable element if value is inherited from other scope. Flag has to be set before the value is rendered.
        if ($element->getInherit() == 1 && $isCheckboxRequired) {
            $element->setDisabled(true);
        }

        $html = '<td class="label"><label for="' .
        $element->getHtmlId() .
        '">' .
        $element->getLabel() .
        '</label></td>';
        $html .= $this->_renderValue($element);

        if ($isCheckboxRequired) {
            $html .= $this->_renderInheritCheckbox($element);
        }

        $html .= $this->_renderScopeLabel($element);
        $html .= $this->_renderHint($element);

        $script = '<script>
        require(["jquery","mage/adminhtml/browser"
        ], function($) {return function(config) {
            $(".btn-image").click(function(){

                var cc = config["coreConfig"];
               
                var url = cc["files_browser_window_url"] + "target_element_id/" + config.id + "/";
                
                var storeId = cc["store_id"];
                if (storeId) {
                    url += "store/" + storeId;
                }
                MediabrowserUtility.openDialog(url);
                return false;     
            });
        };});
        </script>';

return $this->_decorateRowHtml($element, $html).$script;
}


    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->_getCellInputElementName($columnName);

        if ($column['renderer']) {
            return $column['renderer']->setInputName(
                $inputName
                )->setInputId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
                )->setColumnName(
                $columnName
                )->setColumn(
                $column
                )->toHtml();
            }

            $class = '';
            if(isset($column['type'])){
                switch ($column['type']) {
                    case 'image':
                    $class = '<button class="btn-image" data-id="'.$this->_getCellInputElementId(
                        '<%- _id %>',
                        $columnName
                        ).'">Image</button>';
                    break;
                }
            }

            return '<input type="text" id="' . $this->_getCellInputElementId(
                '<%- _id %>',
                $columnName
                ) .
            '"' .
            ' name="' .
            $inputName .
            '" value="<%- ' .
            $columnName .
            ' %>" ' .
            ($column['size'] ? 'size="' .
                $column['size'] .
                '"' : '') .
            ' class="' .
            (isset(
                $column['class']
                ) ? $column['class'] : 'input-text') . '"' . (isset(
                $column['style']
                ) ? ' style="' . $column['style'] . '"' : '') . '/>'.$class;
            }


    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
    	$this->addColumn(
    		'title',
    		['label' => __('Title'),'style' => '']
    		);
        $this->addColumn(
            'image',
            ['label' => __('Image'),'type' => 'image','style' => '']
            );
        $this->_addAfter = false;
    }

    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     * @return void
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = [
        'label' => $this->_getParam($params, 'label', 'Column'),
        'size' => $this->_getParam($params, 'size', false),
        'style' => $this->_getParam($params, 'style'),
        'type' => empty($params['type'])  ? null    : $params['type'],
        'class' => $this->_getParam($params, 'class'),
        'attributes' => empty($params['attributes'])  ? null    : $params['attributes'],
        'values'    => empty($params['values'])  ? null    : $params['values'],
        'comment'    => empty($params['comment'])  ? null    : $params['comment'],
        'renderer' => false,
        ];
        if (!empty($params['renderer']) && $params['renderer'] instanceof \Magento\Framework\View\Element\AbstractBlock) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }
}