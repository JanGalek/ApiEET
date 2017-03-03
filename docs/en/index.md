#Quickstart

This extension is here to provide [Api-EET service](https://api-eet.cz/) and support Nette Framework.

Installation
------------

The best way install Galek/ApiEET is using [Composer](http://getcomposer.org/):

```sh
$ composer require galek/apieet
```

Parameters
----------

Authentication token
```php
$auth = "XXXXXXXX";
```

DIČ / VAT number
```php
$dic_popl = "CZ0000019";
```

Identification of workshop
```php
$id_provoz = "1";
```

Identification number of treasury
```php
$id_pokl = "1";
```

Will you add products to receipt with tax ? 
```php
$productsWithDPH = false;
```

Tax number in percent (21%)
```php
$dph = 21;
```

Can you download receipt to your server ?   
**You must have disabled download with authentication (important)!**
```php
$downloadReceipt = false;
```

if you enable download receipt, you must set path to download.  
**Directory must have permissions (important)!**
```php
$downloadPath = null;
```

Footer text, which will be in footer of receipt
```php
$footerReceipt = "Something";
```


Basic usage
-----------

### Create object (short)
```php
use Galek\ApiEET\Sender;

$auth = "TEaOzAz3iZaFHyg7QwKolAVViYEJhphe";
$dic_popl = "CZ1212121218";
$id_provoz = "1";
$id_pokl = "1";

$eet = new Sender($auth, $dic_popl, $id_provoz, $id_pokl);
                  
```

with all options
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
$footerReceipt = "Something";

$eet = new Sender($auth, $dic_popl, $id_provoz, $id_pokl,
                  $productsWithDPH, $dph,
                  $downloadReceipt, $downloadPath, $footerReceipt);
                  
```

### Add Product to receipt

```php
foreach ($orderProducts as $product) {
    $eet->addProduct($product->name, $product->amount, $product->price);
}
```

### Add Payment method

Price with VAT tax
```php
$eet->addPaymentMethod('Pay by card', $order->price)
```

### Add/Set Result price

Price with VAT tax
```php
$eet->addResultPrice($order->price)
```

### Send data

With Identification of receipt
```php
$sqlobject = $this->orders;
$porad_cisl = $sqlobject->insertEET($orderId); //return identification of receipt
    
$eet->send($porad_cisl);
```

Send method return json result, which is convert to object, so you can detect status, and save informations to database for example (good idea if will some error, and we need resend request again)
```php
$sqlobject = $this->orders;
$porad_cisl = $sqlobject->insertEET($orderId); //return identification of receipt
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
        'PRVNI_ZASLANI' => 0 // First send
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
    
// and if $receipt not null, we can add to email as attachment
 if ($receipt !== null) {
    $receiptPath = $downloadPathReceipt . $receipt . '.pdf';
    // Check if file was downloaded (if not, you try check your permissions to download path)
    if (file_exists($receiptPath) {
        $mail->addAttachment($receiptPath);
    } else {
        $mailbody .= '<p>An error occurred while sending a confirmation of the receipt can be downloaded <a href="'.$sent->receipt_url.'">at this link</a>.';;
    }
 }
```


Nette extension
---------------

you register extension to your neon config
```neon
extensions: 
    eet: Galek\ApiEET\DI\ApiEETExtension
```

and settings
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


