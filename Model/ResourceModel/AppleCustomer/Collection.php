<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Model\ResourceModel\AppleCustomer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Collection initialize with 2 param is model and resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(\Keij\AppleLogin\Model\AppleCustomer::class, \Keij\AppleLogin\Model\ResourceModel\AppleCustomer::class);
    }
}
