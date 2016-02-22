<?php
require '../vendor/autoload.php';

$snr = Synerise\SyneriseTracker::getInstance([
	'apiKey'=>'b43c7c2c-ee52-4051-afa8-073e9c9c5f84',
	'apiVersion'=>'2.0.1',
	'allowFork'=>true,
	]);

$snr->client->customIdentify('1');

// Track an event
$snr->event->track("Add to favourites 1", array(
	"Product Name"=>"iPhone 6",
    "Product Category"=>"Smartphones"));

$snr->event->track("Add to favourites 2", array(
	"Product Name"=>"iPhone 7",
    "Product Category"=>"Smartphones"));

$snr->event->track("Add to favourites 3", array(
	"Product Name"=>"iPhone 8",
    "Product Category"=>"Smartphones"));

// Add product to cart from category Women/Shoes/Boots
$snr->transaction->addProduct(
	[
	'$category'=>"She/Shoes/Boots",
	'$sku'=>"ABCD44DFK-W21",
	'$producer'=>"Mai Piu Senza",
	'$name'=>"High heeled boots - nero",
	'$regularPrice'=>129.99, 
	'$discountPrice'=>99.99, // If product has been sold in discount price
	'$currency'=>"EUR", 
	'$quantity'=>1,
	'$discountCode'=>"WL2016",
	'$location'=>"eCommerce",
	'Size' => 39,
	'Color' => "Black",
	]);


// Remmove product to cart from category Women/Shoes/Boots
$snr->transaction->removeProduct(
	[
	'$category'=>"She/Shoes/Boots",
	'$sku'=>"ABCD44DFK-W21",
	'$producer'=>"Mai Piu Senza",
	'$name'=>"High heeled boots - nero",
	'$regularPrice'=>129.99, 
	'$discountPrice'=>99.99, // If product has been sold in discount price
	'$currency'=>"EUR", 
	'$quantity'=>1,
	'$discountCode'=>"WL2016",
	'$location'=>"eCommerce",
	'Size' => 39,
	'Color' => "Black",
	]);


// // Track charge
$snr->transaction->charge(array(
	'$orderId'=>"UK12345678",
	'$totalAmount'=>347.39,
	'$discountAmount'=>347.39,
	'$revenue'=>10.44,
	'$currency'=>"EUR",
	'$paymentType'=>"PayPal",
	'$deliveryType'=>"UPS",
	'$productsQuantity'=>2,
	'$tax'=>23,
	'products' => [
		[
			'$category'=>"She/Shoes/Boots",
			'$sku'=>"ABCD44DFK-W21",
			'$producer'=>"Mai Piu Senza",
			'$name'=>"High heeled boots - nero",
			'$regularPrice'=>129.99, 
			'$discountPrice'=>99.99, // If product has been sold in discount price
			'$currency'=>"EUR",
			'$quantity'=>1,
			'$discountCode'=>"WL2016",
			'$location'=>"eCommerce",
			'Size' => 39,
			'Color' => "Black",
		],[
			'$category'=>"She/Shoes/Boots",
			'$sku'=>"ABCD44DFK-W21",
			'$producer'=>"Mai Piu Senza",
			'$name'=>"High heeled boots - nero",
			'$regularPrice'=>129.99, 
			'$discountPrice'=>99.99, // If product has been sold in discount price
			'$currency'=>"EUR",
			'$quantity'=>1,
			'$discountCode'=>"WL2016",
			'$location'=>"eCommerce",
			'Size' => 39,
			'Color' => "Black",
		],
	],
	'Order region'=>"Region #1",
	'Order city'=>"Krakow"));


// Setup clinet with cutom itentify
$snr->client->customIdentify('9876');

// Setup clinet with cutom itentify and pass client data
$snr->client->update('9876',array(
	'$email'=>"john.smith@mail.com",
	'$firstname'=>"John",
    '$secondname'=>"Smith",
    '$age'=>33,
    'Client type'=>"Premium"
));

// Optional - clear all cache on Client
$snr->client->reset();

