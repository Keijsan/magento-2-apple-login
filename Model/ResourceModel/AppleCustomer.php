<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AppleCustomer extends AbstractDb
{
    /**
     *  Apple customer initialize with param is name of ur table and primary key
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init("keij_apple_login_customer", "apple_customer_id");
    }
}
