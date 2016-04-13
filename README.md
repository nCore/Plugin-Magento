SYNERISE MAGENTO integration plugin
version: 1.0.1.


1. Vendor
1.1. Jeœli do magento masz podpiêtego composera, wejdŸ na: https://github.com/Synerise/PHP-SDK i zastosuj siê do instrukcji na stronie.
Kolejny punkt 1.2. pomiñ.
1.2. Do pluginu magento zosta³ dodany katalog "vendor" umieœæ w g³ównym katalogu magento.

2. Instalacja pluginu magento.
2.1. Rozpakuj katalog app w g³ównym katalogu magento.
2.2. Plugin zawiera cztery modu³y mo¿na je znaleŸæ w \app\etc\modules. Wszystkie modu³y s¹ zale¿ne od "Synerise_Integration.xml" - ten modu³ musi byæ w³¹czony jeœli nawet u¿ywasz tylko jednego z 
postoa³ych.
2.2.1 Modu³y
* Synerise_Integration - po w³¹czeniu nale¿y w panelu magento "Synerise" -> "Inegracja" skonfigurowaæ modu³. Musisz podaæ odpowiedni klucz z API, w³¹czyæ lub wy³¹czyæ trackera oraz podaæ 
klucz.
Modu³ odpowiedzialny jest za wysy³anie eventów, mo¿esz do woli w³¹czyæ lub wy³¹czaæ dany event z poziomu panelu. Konfigurowalna jest równie¿ lista atrybutów produktów, 
która bêdzie wysy³ana do Synerise. Mo¿esz nie tylko w³¹czyæ dany atrybut ale równie¿ go mapowaæ na dowolny klucz, który zostanie wys³any do synerise. WA¯NE! Jeœli dane s¹ ju¿ zbierane, 
zaleca siê nie zmieniaæ mapowania atrybutów. Bêdzie to powodowa³o niespójnoœæ danych w Synerise.
	* Synerise_Coupon - Umo¿liwia integracjê z systemem kuponów.
	* Synerise_Newsletter - Integracja z newsletterem sysnerise.
	* Synerise_Export - Export produktów do xml

3. Logi
Mo¿na dowolnie ustawiæ œcie¿kê logowania po przez:
$snr->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');
Obecnie domyœlnie jest to œcie¿ka g³ównego katalogu projektu do '/var/log/synerise.log'.

4. Modu³:Newsletter
4.1 Zapisanie siê do newslettera:
$api = Mage::getModel("synerise_newsletter/subscriber");
$api->subscribe($email, array('sex' => $sex));

5. Modu³:Kupony
$coupon = Mage::getModel('synerise_coupon/coupon');  
$coupon->setCouponCode($couponCode); 
$coupon->isSyneriseCoupon(); //sprawdza poprawnoœæ kodu i weryfikuje w Synerise, czy kod mo¿e byæ u¿yty 
$coupon->useCoupon(); // spalanie kuponu

6. Modu³:Integration
Modu³ ten po za zapisaniem kluczy API i Tracking s³u¿y do zbierania danych.
Zbieranie danych odbywa siê na dwóch poziomach. 
	* tracking kody - Po w³¹czeniu w panelu tracking kodu do strony zostanie dodany plik js. Który bêdzie wysy³a³ informacjê o zachowaniu u¿ytkownika na stronie.
	* eventy - eventy s¹ wysy³ane z poziomu serwera, w panelu mageno jest mo¿liwoœæ dowolnego w³aczenia i wy³¹czenia danego eventu. Konfiguracja eventów znajduje siê w pliku \app\code
\community\Synerise\Integration\etc\config.xml
jeœli masz mocno zmienion¹ œcie¿kê zakupow¹ i nie s¹ wo³ane standardowe eventy nale¿y to uwzglêdniæ w tym pliku.

Do poprawnego œledzenia niezbêdne jest umieszczenie tagów Open Graph na stronie. Mo¿esz wykorzystaæ funkcjê osadzania og tagów przez wtyczkê Synersie, b¹dŸ skorzystaj z innych rozwi¹zañ. Upewnij siê jednak, ¿e w og tagach produktu znajduje siê:
* og:title 
* og:type 
* og:image 
* og:url 
* product:retailer_part_no – kod produktu stosowany w sklepie

7. Modu³:Export
Modu³ s³u¿y do eksportu katalogu. Po wygenerowaniu plik XML bêdzie znajdowa³ siê w katalogu z mediami. Dodatkow nale¿y ustawiæ czêstoliwoœæ uruchamiania crona do generownaia xml domyœlnie cron bêdzie uruchamia³ siê o 01:00 raz dziennie.