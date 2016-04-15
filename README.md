SYNERISE MAGENTO integration plugin version: 0.9.

1. Vendor - can be added in two ways:

   1.1. If composer is installed, open the https://github.com/Synerise/PHP-SDK and follow the instructions.

   1.2. If you haven’t installed composer, use “vendor” directory added to the Magento plugin. Place it in the Magento root directory.

2. Installing Magento Plugin

   2.1. Extract app directory into Magento root directory.

   2.2. The plugin has four modules, which can be found in \app\etc\modules. All modules need Synerise_Integration.xml to be active even when you use only one of them.

      2.2.1 Modules

         2.2.1.1. Synerise Integration - the main module is responsible for sending events from Magento to Synerise. After switching it on it needs to be configured. In the upper menu select "Synerise" --> "Integration". A new window will appear. There you can configure the basic integration module. Fill in the proper API key (which can be found in: Synerise Settings --> API). Next, set up the tracker by copying the tracking code, if you're interested in turning it on. There are two methods of copying a tracking code: copy it from Synerise Settings --> Tracking codes or get it through API.  Below the main section you can find a list of events to choose from that can be sent to Synerise, eg. transactions, shopping carts. We recommend turning all the events on. What’s more you can configure the product’s attributes and map them to a key that will be used in Synerise to identify the attribute. You can change the attributes name (key) anytime but beware, changing it if data is already collected will cause data inconsistency in Synerise.

         2.2.1.2. Synerise_Coupon - enables integration with coupons system.

         2.2.1.3. Synerise_Newsletter - enables integration with Synerise newsletter.

         2.2.1.4. Synerise_Export - enables export products to XML file.

3. Logs

You can set your own log path using: $snr->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log'); but by default it’s the same as the main catalogue’s

 '/var/log/synerise.log' directory.

4. Module: Newsletter

Signing up for newsletters: $api = Mage::getModel("synerise_newsletter/subscriber"); $api->subscribe($email, array('sex' => $sex));

5. Module: Coupons

$coupon = Mage::getModel('synerise_coupon/coupon');

$coupon->setCouponCode($couponCode);

$coupon->isSyneriseCoupon(); // validates coupon’s code and chcecks whether the code can be used or not;

$coupon->useCoupon();

6. Module: Integration

This module, apart from setting up API and Tracking code keys, is used to collect data. This is done in two ways:

   6.1. tracking codes - after switching on tracking codes in the panel, a js file will be added to the website. It will send information about clients behavior on the site;

   6.2. events - the difference between a tracking code and an event is that the first one is working on the web browser side, and the events are sent from the server. In Magento panel it is possible to enable or disable every event. You can configurate events in the  \app\code \community\Synerise\Integration\etc\config.xml file. If your purchase path is far from standard, and contains other than standard Magento events, you need to add them in that file.

To track products correctly the web site needs to use Open Graph tags. If you don’t use OG tags you can enable them using Synerise’s plugin. In other case make sure that og tags code contains:

* og:title,

* og:type,

* og:image,

* og:url,

* product:retailer_part_no – product’s code used in the shop.

7. Module: Export

Use it to genereta product’s catalogue. The generated XML file will be located in the media directory. What’s important to keep the XML file up to date you should set the crone’s frequency (which is responsible for generating XML file). By default, cron starts every day 1 o’clock am.