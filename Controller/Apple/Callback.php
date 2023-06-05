<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Controller\Apple;

use Keij\AppleLogin\Helper\Data;
use Keij\AppleLogin\Model\AppleCustomer;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Callback extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RawFactory
     */
    protected $rowFactory;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @type Session
     */
    protected $session;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Base64Json
     */
    protected $base64json;

    /**
     * @var AppleCustomer
     */
    protected $appleCustomer;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $helper
     * @param Customer $customer
     * @param RawFactory $rowFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Session $session
     * @param Json $json
     * @param Base64Json $base64json
     * @param AppleCustomer $appleCustomer
     */
    public function __construct(
        Context $context,
        Data $helper,
        Customer $customer,
        RawFactory $rowFactory,
        StoreRepositoryInterface $storeRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Session $session,
        Json $json,
        Base64Json $base64json,
        AppleCustomer $appleCustomer
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->customer = $customer;
        $this->rowFactory = $rowFactory;
        $this->storeRepository = $storeRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->session = $session;
        $this->json = $json;
        $this->base64json = $base64json;
        $this->appleCustomer = $appleCustomer;
    }

    /**
     * Login with apple
     *
     * @return Raw
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultRaw = $this->rowFactory->create();
        $post = $this->getRequest()->getParams();
        try {
            if ($this->validateResponse($post)) {
                $response = $this->helper->curlCall($this->helper->getTokenUrl(), [
                    'grant_type' => 'authorization_code',
                    'code' => $post['code'],
                    'client_id' => $this->helper->getClientId(),
                    'client_secret' => $this->helper->generateJWT(),
                    'redirect_uri' => $this->helper->getRedirectUri(),
                    'scope' => 'name email'
                ]);

                if (!isset($response->access_token)) {
                    throw new \Exception(__("There was a problem accessing your token. Please contact administrator."));
                }

                if (isset($response->error)) {
                    throw new \Exception(__("There was a error processing your request."));
                }

                $userData = $this->extractUserData($response, $post);

                $email = isset($userData['email']) ? $userData['email'] : "";
                if ($email == '') {
                    throw new \Exception(__('Something went wrong while getting user information from apple store.'));
                }

                $customer = $this->appleCustomer->getCustomerByApple($userData['sub']);
                if (!$customer->getId()) {
                    $customer = $this->appleCustomer->createCustomer($userData);
                }

                $this->helper->refreshCustomerSession($customer);
                $redirectionUrl = $this->storeManager->getStore()->getBaseUrl();
                $raw = $resultRaw->setContents(
                    "<script>
                        window.opener.location.href='".$redirectionUrl."';
                        window.close();
                    </script>"
                );

                return $raw;

            } else {
                throw new \Exception(__("There was a error processing your request."));
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->messageManager->addError($message);
            $raw = $resultRaw->setContents(
                "<script>
                    window.opener.location.reload(true);
                    window.close();
                </script>"
            );
            return $raw;
        }
    }

    /**
     * Validated response from the apple
     *
     * @return bool
     */
    public function validateResponse($post)
    {
        if (isset($post['code']) && isset($post['state'])
            && !empty($post['code']) && !empty($post['state'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Extract user
     *
     * @param $response
     * @param $post
     * @return array
     */
    public function extractUserData($response, $post)
    {
        $userData = [];
        if (isset($post['user'])) {
            $info = $this->json->unserialize($post['user']);
            $userData['firstname'] = $info['name']['firstName'] ?? Data::DEFAUT_FIRSTNAME;
            $userData['lastname'] = $info['name']['lastName'] ?? Data::DEFAUT_LASTNAME;
            $userData['email'] = $info['email'] ?? "";
        }

        $claims = explode('.', $response->id_token)[1];
        $claims = (object) $this->base64json->unserialize($claims);
        if (isset($claims->sub)) {
            $userData['sub'] = $claims->sub;
        }

        if (!isset($userData['email']) || $userData['email'] == '') {
            if (isset($claims->email)) {
                $userData['firstname'] = Data::DEFAUT_FIRSTNAME;
                $userData['lastname'] = Data::DEFAUT_LASTNAME;
                $userData['email'] = $claims->email;
            }
        }
        $userData['password'] = null;

        return $userData;
    }
}
