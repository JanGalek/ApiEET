<?php

$container = require __DIR__ . '/bootstrap.php';

use Galek\ApiEET\Sender;

abstract class ApiEETTestCase extends Tester\TestCase
{
    /**
     * @var Sender
     */
    public $eet;

    public function __construct()
    {
        $auth = "TEaOzAz3iZaFHyg7QwKolAVViYEJhphe";
        $dic_popl = "CZ1212121218";
        $id_provoz = "1";
        $id_pokl = "1";
        $productsWithDPH = false;
        $dph = 21;
        $downloadReceipt = false;
        $downloadPath = null;
        $footerReceipt = "Something";

        $this->eet = new Sender($auth, $dic_popl, $id_provoz, $id_pokl,
            $productsWithDPH, $dph,
            $downloadReceipt, $downloadPath, $footerReceipt);
    }
}
