<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

class Postdirekt_Addressfactory_Block_Adminhtml_Sales_Order_Info_Analysis extends
    Mage_Adminhtml_Block_Template
{
    /**
     * @var Postdirekt_Addressfactory_Model_Order_StatusUpdater
     */
    private $statusUpdater;

    /**
     * @var Postdirekt_Addressfactory_Model_Address_Updater
     */
    private $addressUpdater;

    /**
     * @var Postdirekt_Addressfactory_Model_Deliverability_Codes
     */
    private $deliverabilityCodes;

    protected function _construct()
    {
        $this->statusUpdater = Mage::getSingleton('postdirekt_addressfactory/order_statusUpdater');
        $this->addressUpdater = Mage::getSingleton('postdirekt_addressfactory/address_updater');
        $this->deliverabilityCodes = Mage::getSingleton('postdirekt_addressfactory/deliverability_codes');

        parent::_construct();
    }

    private function getOrder(): Mage_Sales_Model_Order
    {
        if ($this->hasData('order')) {
            return $this->getData('order');
        }

        if (Mage::registry('current_order')) {
            return Mage::registry('current_order');
        }

        if (Mage::registry('order')) {
            return Mage::registry('order');
        }

        Mage::throwException(Mage::helper('sales')->__('Cannot get order instance'));
    }

    /**
     * Returns the ADDRESSFACTORY logo image URL.
     *
     * @return string
     * @throws Exception
     */
    public function getLogoUrl(): string
    {
        return Mage::getDesign()->getSkinUrl('images/postdirekt_addressfactory/logo_addressfactory.png');
    }

    /**
     * Check if CTA "cancel" should be offered to the user.
     *
     * @return bool
     */
    public function canCancel(): bool
    {
        if (!$this->getOrder()->canCancel()) {
            return false;
        }

        $currentStatus = $this->statusUpdater->getStatus((int) $this->getOrder()->getId());
        return $currentStatus !== Postdirekt_Addressfactory_Model_Order_Status::DELIVERABLE;
    }

    /**
     * Check if CTA "unhold" should be offered to the user.
     *
     * @return bool
     */
    public function canUnhold(): bool
    {
        return $this->getOrder()->canUnhold();
    }

    public function getAnalysisResult()
    {
        $analysisResult = Mage::getModel('postdirekt_addressfactory/analysis_result');
        $analysisResult->load($this->getOrder()->getShippingAddressId());
        return $analysisResult->getId() ? $analysisResult : null;
    }

    public function getScore(): string
    {
        $analysisResult = $this->getAnalysisResult();
        if (!$analysisResult instanceof Postdirekt_Addressfactory_Model_Analysis_Result) {
            return Postdirekt_Addressfactory_Model_Deliverability_Codes::POSSIBLY_DELIVERABLE;
        }

        $status = $this->statusUpdater->getStatus((int) $this->getOrder()->getId());
        $wasAlreadyUpdated = $status === Postdirekt_Addressfactory_Model_Order_Status::ADDRESS_CORRECTED;

        return $this->deliverabilityCodes->computeScore($analysisResult->getStatusCodes(), $wasAlreadyUpdated);
    }

    public function getHumanReadableScore(): string
    {
        $scores = [
            Postdirekt_Addressfactory_Model_Deliverability_Codes::POSSIBLY_DELIVERABLE => $this->__('Shipping Address Possibly Deliverable'),
            Postdirekt_Addressfactory_Model_Deliverability_Codes::DELIVERABLE => $this->__('Shipping Address Deliverable'),
            Postdirekt_Addressfactory_Model_Deliverability_Codes::UNDELIVERABLE => $this->__('Shipping Address Undeliverable'),
            Postdirekt_Addressfactory_Model_Deliverability_Codes::CORRECTION_REQUIRED => $this->__('Correction Recommended'),
        ];

        return $scores[$this->getScore()] ?? '';
    }

    public function getDetectedIssues(): array
    {
        $analysisResult = $this->getAnalysisResult();
        if (!$analysisResult instanceof Postdirekt_Addressfactory_Model_Analysis_Result) {
            return [];
        }

        return $this->deliverabilityCodes->getLabels($analysisResult->getStatusCodes());
    }

    /**
     * Obtain CSS class for given item.
     *
     * Since we do not have the FA icon set available in M1, we reduce it to "info" and "alert".
     *
     * @param string $icon
     * @return string
     */
    public function getIssueIconClass(string $icon): string
    {
        if (empty($icon)) {
            return '';
        }

        if ($icon === 'icon-alert') {
            return 'icon icon-alert';
        }

        return 'icon icon-info';
    }

    /**
     * Check if the current order's shipping address has a suggested improvement in its analysis result.
     *
     * @return bool
     */
    public function hasAddressSuggestion(): bool
    {
        $shippingAddress = $this->getOrder()->getShippingAddress();
        if (!$shippingAddress instanceof Mage_Sales_Model_Order_Address) {
            return false;
        }

        $analysisResult = $this->getAnalysisResult();
        if (!$analysisResult instanceof Postdirekt_Addressfactory_Model_Analysis_Result) {
            return false;
        }

        return $this->addressUpdater->addressesAreDifferent($analysisResult, $shippingAddress);
    }

    public function canApplyAddressSuggestion(): bool
    {
        $currentStatus = $this->statusUpdater->getStatus((int) $this->getOrder()->getId());
        if ($currentStatus !== Postdirekt_Addressfactory_Model_Order_Status::ADDRESS_CORRECTED) {
            return $this->hasAddressSuggestion();
        }

        return false;
    }

    public function getAddressSuggestionHtml(): string
    {
        $shippingAddress = $this->getOrder()->getShippingAddress();
        $analysisResult = $this->getAnalysisResult();
        if (!$shippingAddress instanceof Mage_Sales_Model_Order_Address
            || !$analysisResult instanceof Postdirekt_Addressfactory_Model_Analysis_Result) {
            return '';
        }

        $firstName = ($shippingAddress->getFirstname() !== $analysisResult->getFirstName())
            ? "<b>{$analysisResult->getFirstName()}</b>" : $analysisResult->getFirstName();

        $lastName = ($shippingAddress->getLastname() !== $analysisResult->getLastName())
            ? "<b>{$analysisResult->getLastName()}</b>" : $analysisResult->getLastName();

        $street = trim(implode(' ', [$analysisResult->getStreet(), $analysisResult->getStreetNumber()]));
        $orderStreet = trim(implode('', $shippingAddress->getStreet()));

        $street = ($street !== $orderStreet) ? "<b>$street</b>" : $street;

        $city = ($analysisResult->getCity() !== $shippingAddress->getCity())
            ? "<b>{$analysisResult->getCity()}</b>" : $analysisResult->getCity();

        $postalCode = ($analysisResult->getPostalCode() !== $shippingAddress->getPostcode())
            ? "<b>{$analysisResult->getPostalCode()}</b>" : $analysisResult->getPostalCode();

        return "<dd><span>$firstName $lastName</span></dd>
                <dd><span>$street</span></dd>
                <dd><span>$city $postalCode</span></dd>";
    }

    public function getAnalyzeButtonHtml(): string
    {
        $analyzeUrl = $this->getUrl('*/sales_order_analysis/analyze', ['order_id' => $this->getOrder()->getId()]);
        $block = $this->getLayout()->createBlock(
            'adminhtml/widget_button',
            'addressfactory.sales.order.info.button_analyze',
            [
                'label' => $this->__('Perform Shipping Address Check'),
                'on_click' => "setLocation('$analyzeUrl')",
                'class' => 'button analyze action-secondary ok_button',
            ]
        );

        if (!$block instanceof Mage_Adminhtml_Block_Widget_Button) {
            return '';
        }

        return $block->toHtml();
    }

    public function getApplySuggestionButtonHtml(): string
    {
        $applyUrl = $this->getUrl('adminhtml/sales_order_analysis_result/apply', ['order_id' => $this->getOrder()->getId()]);
        $block = $this->getLayout()->createBlock(
            'adminhtml/widget_button',
            'addressfactory.sales.order.info.button_result_apply',
            [
                'label' => $this->__('Auto-Correct Address'),
                'on_click' => "setLocation('$applyUrl')",
                'class' => 'button ok_button',
            ]
        );

        if (!$block instanceof Mage_Adminhtml_Block_Widget_Button) {
            return '';
        }

        return $block->toHtml();
    }

    public function getAddressEditButtonHtml(): string
    {
        $editUrl =  $this->getUrl('*/sales_order/address', ['address_id' => $this->getOrder()->getShippingAddressId()]);

        $block = $this->getLayout()->createBlock(
            'adminhtml/widget_button',
            'addressfactory.sales.order.info.button_edit',
            [
                'label' => $this->__('Manually Edit Address'),
                'on_click' => "setLocation('$editUrl')",
                'class' => 'button edit action-secondary',
            ]
        );

        if (!$block instanceof Mage_Adminhtml_Block_Widget_Button) {
            return '';
        }

        return $block->toHtml();
    }

    public function getUnholdButtonHtml(): string
    {
        $unholdUrl = $this->getUrl('*/*/unhold', ['order_id' => $this->getOrder()->getId()]);
        $block = $this->getLayout()->createBlock(
            'adminhtml/widget_button',
            'addressfactory.sales.order.info.button_unhold',
            [
                'label' => $this->__('Unhold Order'),
                'on_click' => "setLocation('$unholdUrl')",
                'class' => 'button unhold action-secondary',
            ]
        );

        if (!$block instanceof Mage_Adminhtml_Block_Widget_Button) {
            return '';
        }

        return $block->toHtml();
    }

    public function getCancelButtonHtml(): string
    {
        $confirmationMessage = Mage::helper('core')->jsQuoteEscape(
            Mage::helper('sales')->__('Are you sure you want to do this?')
        );
        $cancelUrl = $this->getUrlSecure('*/*/cancel', ['order_id' => $this->getOrder()->getId()]);
        $block = $this->getLayout()->createBlock(
            'adminhtml/widget_button',
            'addressfactory.sales.order.info.button_cancel',
            [
                'label' => $this->__('Cancel Order'),
                'on_click' => "confirmSetLocation('$confirmationMessage', '$cancelUrl')",
                'class' => 'button cancel action-secondary',
            ]
        );

        if (!$block instanceof Mage_Adminhtml_Block_Widget_Button) {
            return '';
        }

        return $block->toHtml();
    }

    public function getAddressEditUrl(): string
    {
        return $this->getUrl('*/sales_order/address', ['address_id' => $this->getOrder()->getShippingAddressId()]);
    }
}
