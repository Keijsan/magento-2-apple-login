<?php
/**
 * Copyright © Keij, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Keij\AppleLogin\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const BASE_URL = 'web/unsecure/base_url';
    public const TYPE = 'apple';
    public const DEFAUT_FIRSTNAME = 'Apple';
    public const DEFAUT_LASTNAME = 'User';
    public const ENABLE_MODULE = 'apple_login/general/enable';
    public const APPLE_CLIENTID = 'apple_login/general/clientid';
    public const APPLE_KEYID = 'apple_login/general/keyid';
    public const APPLE_ISSUERID = 'apple_login/general/issuerid';
    public const APPLE_KEYFILE = 'apple_login/general/auth_key';
    public const APPLE_TOKEN_URL = 'https://appleid.apple.com/auth/token';
    public const APPLE_AUTH_URL = 'https://appleid.apple.com/auth/authorize';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Base64Json
     */
    protected $base64json;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filesystem $filesystem
     * @param Curl $curl
     * @param StoreManagerInterface $storeManager
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Json $json
     * @param Base64Json $base64json
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64json,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->filesystem = $filesystem;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->curl = $curl;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->json = $json;
        $this->base64json = $base64json;
        $this->customerSession = $customerSession;
    }

    /**
     * Return all websites
     *
     * @return mixed
     */
    public function getAllWebsites()
    {
        return $this->storeManager->getWebsites();
    }

    /**
     * Get store config data
     *
     * @param string $path
     * @param string $scope
     * @return string
     */
    public function getConfigValue($path, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     * Check module enable
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfigValue(self::ENABLE_MODULE);
    }

    /**
     * Get apple client id
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->getConfigValue(self::APPLE_CLIENTID);
    }

    /**
     * Get apple key id
     *
     * @return string
     */
    public function getKeyId()
    {
        return $this->getConfigValue(self::APPLE_KEYID);
    }

    /**
     * Get apple team id
     *
     * @return string
     */
    public function getIssuerId()
    {
        return $this->getConfigValue(self::APPLE_ISSUERID);
    }

    /**
     * Get auth key file
     *
     * @return string
     */
    public function getAuthKeyFile()
    {
        return $this->getConfigValue(self::APPLE_KEYFILE);
    }

    /**
     * Get auth key file directory
     *
     * @return string
     */
    public function getAuthKeyFileDir()
    {
        $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $keyFile = $this->getAuthKeyFile();
        if (!empty($keyFile)) {
            return $mediaDir.'apple/'.$keyFile;
        }
        return '';
    }

    /**
     * Get token generation url
     *
     * @return string
     */
    public function getTokenUrl()
    {
        return self::APPLE_TOKEN_URL;
    }

    /**
     * Get aurthoroization url
     *
     * @return string
     */
    public function getAuthorizeUrl()
    {
        return self::APPLE_AUTH_URL;
    }

    /**
     * Get redirect url after successfull login
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->urlBuilder->getBaseUrl().'applelogin/apple/callback/';
    }

    /**
     * Build http query
     *
     * @param array $params
     * @return string
     */
    public function buildHttpQuery($params)
    {
        return str_replace('+', '%20', http_build_query($params));
    }

    /**
     * Get URL to redirect on apple store
     *
     * @return string
     * @throws LocalizedException
     */
    public function getAuthorizationUrl()
    {
        $this->generateJWT();
        $state = bin2hex(random_bytes(5));
        $params = [
            'response_type' => 'code',
            'response_mode' => 'form_post',
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
            'state' => $state,
            'usePopup' => true,
            'scope' => 'name email'
        ];
        $query = $this->buildHttpQuery($params);
        return $this->getAuthorizeUrl().'?'.$query;
    }

    /**
     * Default curl call
     *
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function curlCall($url, $params = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($params) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildHttpQuery($params));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: curl',
        ]);
        $response = curl_exec($ch);
        return (object) $this->json->unserialize($response);
    }

    /**
     * Refresh customer session
     *
     * @param $customer
     * @throws InputException
     * @throws FailureToSendException
     */
    public function refreshCustomerSession($customer)
    {
        if ($customer && $customer->getId()) {
            $this->customerSession->setCustomerAsLoggedIn($customer);

            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
        }
    }

    /**
     * Retrieve positive integer
     *
     * @param string $data
     * @return string
     */
    public function retrievePositiveInteger(string $data)
    {
        while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') > '7f') {
            $data = mb_substr($data, 2, null, '8bit');
        }
        return $data;
    }

    /**
     * Encode
     *
     * @param string $data
     * @return string
     */
    public function encode($data) {

        $encoded = strtr(base64_encode($data), '+/', '-_');
        return rtrim($encoded, '=');
    }

    /**
     * DER-encoded
     *
     * @param string $der
     * @param int $partLength
     * @return string
     */
    public function fromDER(string $der, int $partLength)
    {
        $hex = unpack('H*', $der)[1];

        if ('30' !== mb_substr($hex, 0, 2, '8bit')) {
            throw new \RuntimeException();
        }

        if ('81' === mb_substr($hex, 2, 2, '8bit')) {
            $hex = mb_substr($hex, 6, null, '8bit');
        } else {
            $hex = mb_substr($hex, 4, null, '8bit');
        }

        if ('02' !== mb_substr($hex, 0, 2, '8bit')) {
            throw new \RuntimeException();
        }

        $Rl = hexdec(mb_substr($hex, 2, 2, '8bit'));
        $R = $this->retrievePositiveInteger(mb_substr($hex, 4, $Rl * 2, '8bit'));
        $R = str_pad($R, $partLength, '0', STR_PAD_LEFT);
        $hex = mb_substr($hex, 4 + $Rl * 2, null, '8bit');

        if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
            throw new \RuntimeException();
        }

        $Sl = hexdec(mb_substr($hex, 2, 2, '8bit'));
        $S = $this->retrievePositiveInteger(mb_substr($hex, 4, $Sl * 2, '8bit'));
        $S = str_pad($S, $partLength, '0', STR_PAD_LEFT);

        return pack('H*', $R.$S);
    }

    /**
     * Generate jwt
     *
     * @return false|string
     * @throws LocalizedException
     */
    public function generateJWT() {

        $authFile = $this->getAuthKeyFileDir();
        if(empty($authFile) || !file_exists($authFile)){
            return false;
        }

        $header = [
            'alg' => 'ES256',
            "type" => "JWT",
            'kid' => $this->getKeyId()
        ];
        $body = [
            'iss' => $this->getIssuerId(),
            'iat' => time(),
            'exp' => time() + 86400*180,
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->getClientId(),
            'scope' => 'email name'
        ];

        $privKey = openssl_pkey_get_private(file_get_contents($authFile));
        if (!$privKey) return false;

        $payload = $this->encode($this->json->serialize($header)).'.'.$this->encode($this->json->serialize($body));
        $signature = '';
        $success = openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
        if (!$success) return false;

        $raw_signature = $this->fromDER($signature, 64);

        return $payload.'.'.$this->encode($raw_signature);
    }

    /**
     * Check customer is logged in or not
     *
     * @return boolean
     */
    public function isCustomerLoggedIn()
    {
        if($this->customerSession->isLoggedIn()) {
            return true;
        }
        return false;
    }
}
