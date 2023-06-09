# Magento2 Apple Login

Extension Apple Login

Let users register and sign in with one click with a Apple ID and make everything easy and friendly

## Compatibility

Magento CE 2.4.x

## Install

#### Manual Installation

1. Create a folder {Magento root}/app/code/Keij/AppleLogin

2. Download the corresponding [latest version](https://github.com/Keijsan/magento-2-apple-login/releases/)

3. Copy the unzip content to the folder ({Magento root}/app/code/Keij/AppleLogin)

### Completion of installation

1. Go to Magento2 root folder

2. Enter following commands:

    ```bash
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy  (optional)
    ```
## Configuration

About guide for setup sign in with apple with developer. You can check this in [here](https://developer.okta.com/blog/2019/06/04/what-the-heck-is-sign-in-with-apple)

<img alt="Keij Apple Login Configuration" src="https://github.com/Keijsan/magento-2-apple-login/blob/main/apple-login/configuration.png" style="width:100%"/>

<img alt="Sign in with apple" src="https://github.com/Keijsan/magento-2-apple-login/blob/main/apple-login/signin.png" style="width:100%"/>

## Uninstall

#### Remove database data and schema

1. Go to Magento2 root folder

2. Enter following commands to remove database data and schema:

    ```bash
    php bin/magento module:uninstall -r Keij_AppleLogin
    ```

### Completion of uninstall

1. Go to Magento2 root folder

2. Enter following commands:

    ```bash
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy  (optional)
    ```
