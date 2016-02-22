"# SYNERISE MAGENTO integration plugin" 
version: 0.9.


1. Vendor
1.1. Jeśli do magento masz podpiętego composera, wejdź na: https://github.com/Synerise/PHP-SDK i zastosuj się do instrukcji na stronie.
Kolejny punk 1.2. pomiń.
1.2. Do pluginu magento został dodany katalog "vendor" umieść w głównym katalogu magento.


2. Instalacja pluginu magento.
2.1. Rozpakuj katalog app w głównym katalogu magento.
2.2. Plugin zawieta cztery moduły można je znaleźć w \app\etc\modules. Wszystkie moduły są zależne od "Synerise_Integration.xml" - ten moduł musi być włączony jeśli nawet używasz tylko jednego z 
postoałych.
2.2.1 Moduły
- Synerise_Integration - po włączeniu należy w panelu magento "Synerise" -> "Inegracja" skonfigurować moduł. Musisz podać odpowiedni klucz z API, włączyć lub wyłączyć trackera oraz podać 
klucz.
Moduł odpowiedzialny jest za wysyłanie eventów, możesz do woli włączyć lub wyłączać dany event z poziomu panelu. Konfigurowalna jest również lista atrybutów produktów, 
która będzie wysyłana
do synerise. Możesz nie tylko włączyć dany atrybut ale również go mapować na dowolony klucz, który zostanie wysłany do synerise. WAŻNE! Jeśli dane są już zbierane, 
zaleca się
nie zmieniać mapowania atrybutów. Będzie to powodowało niespójność danych w synerise.
- Synerise_Coupon - Umożliwa integrację z systemem kuponów.
- Synerise_Newsletter - Integracja z newsletterem sysnerise.
- Synerise_Export - Exportproduktów do xml

3. Logi
Można dowolnie ustawić ścieżkę logowania po przez:
$snr->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');
Obecnie domyślnie jest to ścieżka głównego katalogu projektu do '/var/log/synerise.log'.


4. Moduł:Newsletter
4.1 Zapisanie się do newslettera:
$api = Mage::getModel("synerise_newsletter/subscriber");
$api->subscribe($email, array('sex' => $sex));

5. Moduł:Kupony
$coupon = Mage::getModel('synerise_coupon/coupon');  
$coupon->setCouponCode($couponCode); 
$coupon->isSyneriseCoupon(); //sprawdza poprawność kodu i weryfikuje w synerise, czy kod może być użyty 
$coupon->useCoupon(); // spalanie kuponyu

6. Moduł:Integration
Moduł ten po za zapisaniem kluczy API i Tracking służy do zbierania danych.
Zbieranie danych odbywa się na dwóch poziomach. 
- tracking kody - Po właczeniu w panelu tracking kodu do strony zostanie dodany plik js. Który będzie wysyłał informację o zachowaniu użytkownika na stronie.
- eventy - eventy są wysyłane z poziomu serwera, w panelu mageno jest możliwość dowolnego właczenia i wyłączenia danego eventu. Konfiguracja eventów znajduje się w pliku \app\code
\community\Synerise\Integration\etc\config.xml
jeśli masz mocno zmienioną ścieżkę zakupową i nie są wołane standardowe eventy należy to uwzględnić w tym pliku.