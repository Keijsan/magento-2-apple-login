<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Block\Login;

use Magento\Backend\Block\Widget\Context;

class Button extends \Magento\Framework\View\Element\Template
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
