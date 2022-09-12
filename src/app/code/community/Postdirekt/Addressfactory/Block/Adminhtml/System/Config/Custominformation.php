<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Block_Adminhtml_System_Config_Custominformation
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Init template.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->getTemplate()) {
            $this->setTemplate('postdirekt_addressfactory/system/config/custominfo.phtml');
        }

        return $this;
    }

    /**
     * Returns the rendered template.
     *
     * @param \Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Returns the current module version.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        /** @var Postdirekt_Addressfactory_Helper_Data $helper */
        $helper = Mage::helper('postdirekt_addressfactory');
        return $helper->getModuleVersion();
    }

    /**
     * Returns the logo image URL.
     *
     * @return string
     */
    public function getLogoUrl()
    {
        return Mage::getDesign()->getSkinUrl('images/postdirekt_addressfactory/logo_deutsche_post.png');
    }
}
