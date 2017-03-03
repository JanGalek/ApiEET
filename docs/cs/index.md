#Quickstart

This extension is here to provide [Api-EET service](https://api-eet.cz/) and support Nette Framework.

Instalace
------------

Nejlépe nainstalujeme Galek/ApiEET použitím [Composeru](http://getcomposer.org/):

```sh
$ composer require galek/apieet
```

Parametry
----------

Autorizační token
```php
$auth = "XXXXXXXX";
```

DIČ
```php
$dic_popl = "CZ0000019";
```

Identifikace provozovny
```php
$id_provoz = "1";
```

Identifikace pokladny
```php
$id_pokl = "1";
```

Budete přidávat produkty na účtenku s cenou včetně dph ? 
```php
$productsWithDPH = false;
```

DPH (21%)
```php
$dph = 21;
```

Stáhnout účtenku na váš server ?    

**Musíme mít vypnuté stahování s autorizací (důležité)!**
```php
$downloadReceipt = false;
```

Pokud povolíte stáhnutí účtenky na váš server, tak musíte nastavit cestu kam se mají účtenky ukládat.  
**Složka musí mít oprávnění (důležité)!**
```php
$downloadPath = null;
```

Text patičky, který bude v patičce účtenky
```php
$footerReceipt = "Děkujeme za váš nákup";
```


Basic usage
-----------

### Vytvoření objektu (krátké)
```php
use Galek\ApiEET\Sender;

$auth = "TEaOzAz3iZaFHyg7QwKolAVViYEJhphe";
$dic_popl = "CZ1212121218";
$id_provoz = "1";
$id_pokl = "1";

$eet = new Sender($auth, $dic_popl, $id_provoz, $id_pokl);
                  
```

se všemi možnostmi
```php
use Galek\ApiEET\Sender;

$auth = "TEaOzAz3iZaFHyg7QwKolAVViYEJhphe";
$dic_popl = "CZ1212121218";
$id_provoz = "1";
$id_pokl = "1";
$productsWithDPH = false;
$dph = 21;
$downloadReceipt = false;
$downloadPath = null;
$footerReceipt = "Děkujeme za Váš nákup";

$eet = new Sender($auth, $dic_popl, $id_provoz, $id_pokl,
                  $productsWithDph, $dph,
                  $downloadReceipt, $downloadPath, $footerReceipt);
                  
```

### Přidání produktu na účtenku

```php
foreach ($orderProducts as $product) {
    $eet->addProduct($product->name, $product->amount, $product->price);
}
```

### Přidání platební metody

Cena včetně DPH
```php
$eet->addPaymentMethod('Pay by card', $order->price)
```

### Přidání/Nastavení celkové ceny

Cena včetně DPH
```php
$eet->addResultPrice($order->price)
```

### Odeslání dat

S identifikací účtenky
```php
$sqlobject = $this->orders;
$porad_cisl = $sqlobject->insertEET($orderId); //vrací identifikační číslo účtenky
    
$eet->send($porad_cisl);
```

Metoda **send($porad_cislo)** vrací json, který je převeden do objektu, takže můžete zjistit stav, a uložit informace například do databáze (dobrý nápad, pokud by nastala chyba, a potřebovali bychom odeslat požadavek znovu)
```php
$sqlobject = $this->orders;
$porad_cisl = $sqlobject->insertEET($orderId); //vrací identifikační číslo účtenky
$sent = $eet->send($porad_cisl);
        
$receipt = null;
    
if ($sent->result == "success") {
    $receipt = $sent->uuid_zpravy;
    $eetdata = [
        'RESULT' => $sent->result,
        'API_REQUEST_ID' => $sent->api_request_id,
        'EET_REQUEST_ID' => $sent->eet_request_id,
        'UUID_ZPRAVY' => $sent->uuid_zpravy,
        'DAT_TRZBY' => $sent->dat_trzby,
        'FIK' => $sent->fik,
        'BKP' => $sent->bkp,
        'PKP' => $sent->pkp,
        'RECEIPT_URL' => $sent->receipt_url,
        'PRVNI_ZASLANI' => 0
    ];
} elseif ($sent->result == 'api_error') {
    $eetdata = [
       'RESULT' => $sent->result,
       'ERROR_TYPE' => $sent->error_type,
       'ERROR_STRING' => $sent->error_string,
       'ERROR_MESSAGE' => $sent->error_message,
       'PRVNI_ZASLANI' => 0
   ];
} elseif ($sent->result == 'eet_error') {
    $eetdata = [
       'RESULT' => $sent->result,
       'ERROR_CODE' => $sent->error_code,
       'ERROR_MESSAGE' => $sent->error_message,
       'DAT_DRZBY' => $sent->dat_trzby,
       'BKP' => $sent->bkp,
       'PKP' => $sent->pkp,
       'RECEIPT_URL' => $sent->receipt_url,
       'PRVNI_ZASLANI' => 0
   ];
} else {
    $eetdata = [
       'RESULT' => 'Unknown Error',
       'ERROR_MESSAGE' => 'Unknown',
       'PRVNI_ZASLANI' => 0
   ];
}
    
$sqlobject->updateEET($porad_cisl, $eetdata);
    
// pokud $receipt není null, můžete přidat účtenku do přílohy k emailu
 if ($receipt !== null) {
    $receiptPath = $downloadPathReceipt . $receipt . '.pdf';
    // Zkontrolujeme jestli byla účtenka stažena (pokud ne, zkuste zkontrolovat oprávnění složky, kam se účtenky stahují)
    if (file_exists($receiptPath) {
        $mail->addAttachment($receiptPath);
    } else {
        $mailbody .= '<p>Nastala chyba při posílání účtenky, účtenku si můžete stáhnout na <a href="'.$sent->receipt_url.'">tomto odkaze</a>.';;
    }
 }
```


Nette rozšíření
---------------

zaregistrujeme rozšíření do neon configu
```neon
extensions: 
    eet: Galek\ApiEET\DI\ApiEETExtension
```

a nastavíme
```neon
eet:    
    auth: 'TEaOzAz3iZaFHyg7QwKolAVViYEJhphe'
    dic_popl: 'CZ1212121218'
    id_provoz: 666
    id_pokl: "ZKP-1"
    productsWithDph: true
    dph: 21
    footerReceipt: null
    downloadReceipt: true
    downloadPath: %appDir%/../download/eet/
```

Presenter
```php
<?php
namespace App\Presenters;  
    
class HomepagePresenter extends BasePresenter
{
    /**
     * @var \App\Galek\Service\EET\Sender @inject
     */
    public $eet;
    
 }
```


