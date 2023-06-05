<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Model;

use Keij\AppleLogin\Api\Data\AppleCustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Math\Random;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;

class AppleCustomer extends AbstractModel implements AppleCustomerInterface
{
    public const SENT_MAIL = 1;

    /**
     * @var AppleCustomerRepository
     */
    protected $appleCustomerRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagementInterface;

    /**
     * @var EmailNotificationInterface
     */
    protected $emailNotificationInterface;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param AppleCustomerRepository $appleCustomerRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $messageManager
     * @param CustomerFactory $customerFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param StoreManagerInterface $storeManager
     * @param StoreRepositoryInterface $storeRepository
     * @param Random $random
     * @param AccountManagementInterface $accountManagementInterface
     * @param EmailNotificationInterface $emailNotificationInterface
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                        $context,
        Registry                       $registry,
        AppleCustomerRepository        $appleCustomerRepository,
        CustomerRepositoryInterface    $customerRepository,
        ManagerInterface               $messageManager,
        CustomerFactory                $customerFactory,
        CustomerInterfaceFactory       $customerDataFactory,
        StoreManagerInterface          $storeManager,
        StoreRepositoryInterface       $storeRepository,
        Random                         $random,
        AccountManagementInterface     $accountManagementInterface,
        EmailNotificationInterface     $emailNotificationInterface,
        AbstractResource               $resource = null,
        AbstractDb                     $resourceCollection = null,
        array                          $data = []
    ) {
        $this->appleCustomerRepository = $appleCustomerRepository;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
        $this->customerFactory = $customerFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->random = $random;
        $this->accountManagementInterface = $accountManagementInterface;
        $this->emailNotificationInterface = $emailNotificationInterface;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Apple customer initialize with param is ResourceModel to get data from db
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(\Keij\AppleLogin\Model\ResourceModel\AppleCustomer::class);
    }

    /**
     * Get apple customer id
     *
     * @return array|int|mixed|null
     */
    public function getAppleCustomerId()
    {
        return $this->getData(self::APPLE_CUSTOMER_ID);
    }

    /**
     * Set apple customer id
     *
     * @param int $appleCustomerId
     * @return AppleCustomerInterface|AppleCustomer
     */
    public function setAppleCustomerId($appleCustomerId)
    {
        return $this->setData(self::APPLE_CUSTOMER_ID, $appleCustomerId);
    }

    /**
     * Get apple sub
     *
     * @return array|mixed|string|null
     */
    public function getAppleSub()
    {
        return $this->getData(self::APPLE_SUB);
    }

    /**
     * Set apple sub
     *
     * @param string $appleSub
     * @return AppleCustomerInterface|AppleCustomer
     */
    public function setAppleSub($appleSub)
    {
        return $this->setData(self::APPLE_SUB, $appleSub);
    }

    /**
     * Get customer id
     *
     * @return array|int|mixed|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return AppleCustomerInterface|AppleCustomer
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get is sent mail
     *
     * @return array|int|mixed|null
     */
    public function getIsSentMail()
    {
        return $this->getData(self::IS_SENT_MAIL);
    }

    /**
     * Set is sent mail
     *
     * @param int $sentMail
     * @return AppleCustomerInterface|AppleCustomer
     */
    public function setIsSentMail($sentMail)
    {
        return $this->setData(self::IS_SENT_MAIL, $sentMail);
    }

    /**
     * Set apple customer
     *
     * @param string $sub
     * @param int $customerId
     * @return $this
     * @throws \Exception
     */
    public function setAppleCustomer($sub, $customerId)
    {
        $this->setData(
            [
                'apple_sub'              => $sub,
                'customer_id'            => $customerId,
                'is_sent_mail'           => self::SENT_MAIL,
            ]
        )->setId(null)->save();

        return $this;
    }

    /**
     * Get customer by apple
     *
     * @param string $sub
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerByApple($sub)
    {
        try {
            $appleCustomer = $this->appleCustomerRepository->getByAppleSub($sub);
            $customer = $this->customerRepository->getById($appleCustomer->getCustomerId());
        } catch (\Exception $e) {
            $customer = $this->customerFactory->create();
        }
        return $customer;
    }

    /**
     * Create customer
     *
     * @param array $user
     * @param string $storeCode
     * @return \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer
     * @throws \Exception
     */
    public function createCustomer($user, $storeCode = null)
    {
        try {
            $customer = $this->customerRepository->get($user['email']);
        } catch (\Exception $e) {
            $customer = $this->customerFactory->create();
        }
        if ($customer->getId()) {
            $this->setAppleCustomer($user['sub'], $customer->getId());
        } else {
            $customer = $this->createAppleCustomer($user);
        }
        return $customer;
    }

    /**
     * Create apple customer
     *
     * @param array $user
     * @param string $storeCode
     * @return \Magento\Customer\Model\Customer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createAppleCustomer($user, $storeCode = null)
    {
        if ($storeCode == null) {
            $store = $this->storeManager->getStore();
        } else {
            $store = $this->storeRepository->get($storeCode);
        }
        $customer = $this->customerDataFactory->create();
        $customer->setFirstname($user['firstname'])
            ->setLastname($user['lastname'])
            ->setEmail($user['email'])
            ->setStoreId($store->getId())
            ->setWebsiteId($store->getWebsiteId())
            ->setCreatedIn($store->getName());

        try {
            $customer = $this->customerRepository->save($customer);
            $newPasswordToken  = $this->random->getUniqueHash();
            $this->accountManagementInterface->changeResetPasswordLinkToken($customer, $newPasswordToken);
            $this->emailNotificationInterface->newAccount(
                $customer,
                EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD
            );
            $this->setAppleCustomer($user['sub'], $customer->getId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $customer = $this->customerFactory->create()->load($customer->getId());
        return $customer;
    }
}
