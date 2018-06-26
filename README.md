Synerise extension for Magento 1
=====================
This plugin covers basic integration with Synersie system, including tracking and coupon implementation, as well as export of products, customers and orders.
For more information please visit [Synerise Official](https://synerise.com).

Requirements
------------
- PHP >= 5.4
- [Synerise SDK for PHP](https://github.com/Synerise/PHP-SDK)
- [Guzzle, PHP HTTP client 5](https://github.com/guzzle/guzzle)

All libraries are already provided and autoloaded from lib directory.

Installation
------------
Simply extract repository contents into Magento root directory.

Modules
------------

The plugin consists of four separate modules. You can disable unused ones, but mind they're all dependent on Synerise_Integration, which is responsible for basic communication with Synerise API.

## Synerise Integration

Main module provides basic helpers for API and Tracker use.

#### Basic configuration
1. Go to  *Synerise* --> *Integration*.
![Integration](img/integration.png?raw=true)
2. Fill in your legacy API key (Default key) and *save config*.
![Api key](img/api-key.png?raw=true)


> You can manage your API keys in Synerise [business profile settings](https://app.synerise.com/api) under API section.  
> Crate new api key, and remember to set its scope afterwards - check all options.  
> Finally hit *show key* and copy the key string into magento panel.  

Given that the provided key is valid, and it's scope is properly set, the module will obtain the tracking code automatically. Basic profile information should also be visible above the configuration tabs.

#### Additional configuration options

* **Open Graph**  
Open Graph tags are used by tracker to obtain additional information about pages visited by user and to properly track your products. If your site is already provided with OG tags, you can disable them here. In that case, please make sure your tags include: 

> * **og:title**
> * **og:type**
> * **og:image**
> * **og:url**
> * **product:retailer_part_no** – product specific code used in your store

* **Tracking**  
Here you can enable/disable tracking globally.  
Enabling this option, embeds js code ona all pages. This code is responsible for basic tracking of clients behavior on your website.

* **Tracking events**  
Apart from browser side tracking. Magento plugin also uses magento events system to track additional data sever side.  
This tab provides a list of all eligible events. You can choose which one to send. We highly recommend tracking them all though. 

> You can configure events in the *\app\code\community\Synerise\Integration\etc\config.xml* file. If your purchase path is far from standard, and contains other than regular Magento events, you need to add them there.

* **Config product attr**  
This tab provides a list of product attributes. You can choose which one to send and also map them to keys that will be used in Synerise to identify these attributes. 

> You can change attributes name (key) at anytime but beware, changing it if data is already collected will cause data inconsistency in Synerise.

## Synerise Coupon 

Module allows the use of Synserise coupons in your store.  
Coupons are managed through Synerise Panel in [campaigns](https://app.synerise.com/coupons) under Coupons section.

#### Configuration

Go to *Synerise* --> *Coupon* --> *Configuration*.

* **Enable** - allows you to disable the use of synersie coupons.  
* **Validate coupon format** - Optional validation of coupon code format, to be considered as synerise coupon. Defautl format is [EAN-13](https://en.wikipedia.org/wiki/EAN-13)

#### Price rules

Module uses the default magento price rules system to handle Synerise coupons.  
You can find the list off all your coupons under *Synerise* --> *Coupon* --> *Shopping Cart Price Rules*.  
Use the *Import Synerise Rules* button to fetch your rules form Synerise system.  

Whereas the basic options are editable only via Synerise panel, you can still edit default **Websites** & **Customer Groups** settings. Also, you can set additional **Conditions** & **Actions** to further define your Promo Rule for magento.

## Synerise Newsletter 

Enables integration with Synerise newsletter.  

> *Notice*: Newsletter module extends Mage_Newsletter_Model_Subscriber model
> The reason behind this approach is to prevent magento from handling emails and confirmation.

All emails, including confirmation are handled externally via Synerise.  
Magento will still store the newsletter agreement information as usual.

#### Configuration

> Please make sure to configure your [newsletter settings](https://app.synerise.com/setting/newsletter) before enabling this module.

Go to *Synerise* --> *Newsletter*.

* **Enable** - allows you to disable the module
* **Require confirmation from registered users** - by default magento treats logged in users as already confirmed. You can alter this behavior by changing this setting. Registered users will receive confirmation email like other users.

#### Additional data

By modifying the newsletter submit form, you can send additional data to Synerise.

> You can add a *sex* field. Allowed values are: *1* for man & *2* for woman.


## Synerise Export 

Module allows you to generate XML feeds containing active products and categories.

#### Configuration

Go to *Synerise* --> *Export*.

* **Config**
	* **Select store** - select the stores to generate feeds.
	* **Unique hash** - random string, used for feed path.
* **Mapping of the attributes** - select the appropriate attributes to be mapped
* **Generation settings** - set cron job to generate feeds. You can also generate them manually.

Logs
------------
Enable *Synerise* --> *Integration* --> *Developer* --> *Debug* option to enable logging. Api calls will be logged to */var/log/synerise.log*.
