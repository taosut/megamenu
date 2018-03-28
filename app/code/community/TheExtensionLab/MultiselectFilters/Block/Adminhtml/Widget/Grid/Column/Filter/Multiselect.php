<?php
/**
 * Multiselect Filter Block
 *
 * @category    TheExtensionLab
 * @package     TheExtensionLab_MultiselectFilters
 * @copyright   Copyright (c) TheExtensionLab (http://www.theextensionlab.com)
 * @license     Open Software License (OSL 3.0)
 * @author      James Anelay @ TheExtensionLab <james@theextensionlab.com>
 */
class TheExtensionLab_MultiselectFilters_Block_Adminhtml_Widget_Grid_Column_Filter_Multiselect extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{
    protected function _getOptions()
    {
        $optionGroups = $this->getColumn()->getOptionGroups();
        if ($optionGroups) {
            return $optionGroups;
        }

        $colOptions = $this->getColumn()->getOptions();
        if (!empty($colOptions) && is_array($colOptions) ) {
            foreach ($colOptions as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
            return $options;
        }
        return array();
    }

    /**
     * Render an option with selected value
     *
     * @param array $option
     * @param string $value
     * @return string
     */
    protected function _renderOption($option, $values)
    {
        $explodedValues = explode(',',$values);
        $selected = ((in_array($option['value'], $explodedValues) && (!is_null($explodedValues)) && ($values != "")) ? ' selected="selected"' : '' );
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.'>'.$this->escapeHtml($option['label']).'</option>';
    }

    public function getHtml()
    {
        $values = $this->getValue();
        $html = '<select name="'.$this->_getHtmlName().'" id="'.$this->_getHtmlId().'" class="no-changes" data-placeholder="'.$this->__('Click here to filter').'" multiple>';

        foreach ($this->_getOptions() as $option){
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $this->escapeHtml($option['label']) . '">';
                foreach ($option['value'] as $subOption) {
                    $html .= $this->_renderOption($subOption, $values);
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_renderOption($option, $values);
            }
        }
        $html.='</select>';
        return $html;
    }

    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }

        $values = explode(",",$this->getValue());
        return array('in' => $values);
    }

    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = $this->escapeHtml($options[$item]);
                    }
                    elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return implode(', ', $res);
            } elseif (isset($options[$value])) {
                return $this->escapeHtml($options[$value]);
            } elseif (in_array($value, $options)) {
                return $this->escapeHtml($value);
            }
        }
    }

}
