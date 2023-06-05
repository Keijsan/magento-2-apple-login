<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Block\Adminhtml\Config\System;

use Keij\AppleLogin\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class RedirectUrl extends FormField
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * Get template of url
     *
     * @param AbstractElement $element
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $redirectUrl = $this->helper->getRedirectUri();
        $html = '<input style="opacity:1;" readonly id="apple-login" class="input-text admin__control-text"
                        value="%s" onclick="this.select()" type="text">';

        return sprintf($html, $redirectUrl);
    }
}
