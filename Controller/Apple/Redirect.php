<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Controller\Apple;

use Keij\AppleLogin\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->helper = $helper;
    }

    /**
     * Redirect
     *
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|(\Magento\Framework\Controller\Result\Redirect&\Magento\Framework\Controller\ResultInterface)
     * @throws LocalizedException
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $url = $this->helper->getAuthorizationUrl();
        $redirect->setUrl($url);
        return $redirect;
    }
}
