<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Api\Data;

interface AppleCustomerInterface
{
    public const APPLE_CUSTOMER_ID = 'apple_customer_id';
    public const APPLE_SUB = 'apple_sub';
    public const CUSTOMER_ID = 'customer_id';
    public const IS_SENT_MAIL = 'is_sent_mail';

    /**
     * Get apple customer id
     *
     * @return int|null
     */
    public function getAppleCustomerId();

    /**
     * Set apple customer id
     *
     * @param int $appleCustomerId
     * @return \Keij\AppleLogin\Api\Data\AppleCustomerInterface
     */
    public function setAppleCustomerId($appleCustomerId);

    /**
     * Get apple sub
     *
     * @return string|null
     */
    public function getAppleSub();

    /**
     * Set apple sub
     *
     * @param string $appleSub
     * @return \Keij\AppleLogin\Api\Data\AppleCustomerInterface
     */
    public function setAppleSub($appleSub);

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return \Keij\AppleLogin\Api\Data\AppleCustomerInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get is sent mail
     *
     * @return int|null
     */
    public function getIsSentMail();

    /**
     * Set is sent mail
     *
     * @param int $sentMail
     * @return \Keij\AppleLogin\Api\Data\AppleCustomerInterface
     */
    public function setIsSentMail($sentMail);
}
