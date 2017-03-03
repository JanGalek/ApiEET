<?php
require __DIR__ . '/ApiEETTestCase.php';

use Tester\Assert;
use Galek\ApiEET\Sender;

class SenderTest extends ApiEETTestCase
{
    /*public function testConstruct()
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

        $eet = new Sender($auth, $dic_popl, $id_provoz, $id_pokl,
            $productsWithDPH, $dph,
            $downloadReceipt, $downloadPath, $footerReceipt);
    }*/

    public function testInfo()
    {
        Assert::equal('TEaOzAz3iZaFHyg7QwKolAVViYEJhphe', $this->eet->auth);
        Assert::equal('CZ1212121218', $this->eet->dic_popl);
        Assert::equal('1', $this->eet->id_provoz);
        Assert::equal('1', $this->eet->id_pokl);
        Assert::false($this->eet->productsWithDph);
        Assert::equal(1.21, $this->eet->dph);
        Assert::false($this->eet->downloadReceipt);
        Assert::null($this->eet->downloadPath);
        Assert::equal('Something', $this->eet->footerReceipt);
    }

    public function testAddProduct()
    {
        Assert::equal(0, $this->eet->addProduct('Banan', 1, 12));
        Assert::equal(1, $this->eet->addProduct('Apple', 1, 8));
    }

    public function testSend()
    {
        $this->eet->addPaymentMethod('card', 20);
        $this->eet->addResultPrice(20);

        Assert::type('object', $this->eet->send(1));
    }

    public function testSentData()
    {
        $this->eet->addPaymentMethod('card', 20);
        $this->eet->addResultPrice(20);
        $sent = $this->eet->send(1);

        Assert::equal('success', $sent->result);
    }
}

(new SenderTest())->run();