<?php

/**
 * Renderer for Hint Banner in System Configuration
 */
class HE_TwoFactorAuth_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'he_twofactor/system/config/fieldset/hint.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    public function getTwoFactorAuthVersion()
    {
        return (string)Mage::getConfig()->getNode('modules/HE_TwoFactorAuth/version');
    }

}