<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Api;

use Keij\AppleLogin\Api\Data\AppleCustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface AppleCustomerRepositoryInterface
{

    /**
     * Save apple customer
     *
     * @param AppleCustomerInterface $appleCustomer
     * @return \Keij\AppleLogin\Api\Data\AppleCustomerInterface
     * @throws LocalizedException
     */
    public function save(AppleCustomerInterface $appleCustomer);

    /**
     * Get customer
     *
     * @param int $appleCustomerId
     * @return \Keij\AppleLogin\Api\Data\AppleCustomerInterface
     * @throws LocalizedException
     */
    public function get($appleCustomerId);

    /**
     * Get customer
     *
     * @param string $appleSub
     * @return \Keij\AppleLogin\Api\Data\AppleCustomerInterface
     * @throws LocalizedException
     */
    public function getByAppleSub($appleSub);

    /**
     * Delete customer
     *
     * @param AppleCustomerInterface $appleCustomer
     * @return bool
     * @throws LocalizedException
     */
    public function delete(AppleCustomerInterface $appleCustomer);

    /**
     * Delete customer by ID
     *
     * @param int $appleCustomerId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($appleCustomerId);
}
