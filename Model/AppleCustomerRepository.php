<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Model;

use Keij\AppleLogin\Api\Data\AppleCustomerInterface;
use Keij\AppleLogin\Model\AppleCustomerFactory;
use Keij\AppleLogin\Api\AppleCustomerRepositoryInterface;
use Keij\AppleLogin\Model\ResourceModel\AppleCustomer as ResourceAppleCustomer;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AppleCustomerRepository implements AppleCustomerRepositoryInterface
{
    /**
     * @var AppleCustomerFactory
     */
    protected $appleCustomerFactory;

    /**
     * @var ResourceAppleCustomer
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param ResourceAppleCustomer $resource
     * @param AppleCustomerFactory $appleCustomerFactory
     */
    public function __construct(
        ResourceAppleCustomer $resource,
        AppleCustomerFactory $appleCustomerFactory
    ) {
        $this->resource = $resource;
        $this->appleCustomerFactory = $appleCustomerFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(AppleCustomerInterface $appleCustomer)
    {
        try {
            $this->resource->save($appleCustomer);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the apple customer: %1',
                $exception->getMessage()
            ));
        }
        return $appleCustomer;
    }

    /**
     * @inheritDoc
     */
    public function get($appleCustomerId)
    {
        if (!$appleCustomerId) {
            throw new NoSuchEntityException(__('Apple customer with id "%1" does not exist.', $appleCustomerId));
        }
        $appleCustomer = $this->appleCustomerFactory->create();
        $this->resource->load($appleCustomer, $appleCustomerId);
        return $appleCustomer;
    }

    /**
     * @inheritDoc
     */
    public function delete(AppleCustomerInterface $appleCustomer)
    {
        try {
            $appleCustomerModel = $this->appleCustomerFactory->create();
            $this->resource->load($appleCustomerModel, $appleCustomer->getId());
            $this->resource->delete($appleCustomerModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Apple customer: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($appleCustomerId)
    {
        return $this->delete($this->get($appleCustomerId));
    }

    /**
     * @inheritDoc
     */
    public function getByAppleSub($appleSub)
    {
        if (!$appleSub) {
            throw new NoSuchEntityException(__('Apple Customer with auth sub "%1" does not exist.', $appleSub));
        }
        $appleCustomer = $this->appleCustomerFactory->create();
        $this->resource->load($appleCustomer, $appleSub, 'apple_sub');
        return $appleCustomer;
    }
}
