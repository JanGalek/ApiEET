<?php
/**
 * Created by PhpStorm.
 * User: Galek
 * Date: 2.3.2017
 * Time: 13:52
 */

namespace Galek\ApiEET;

class Sender
{
    /**
     * @var string
     */
    public $auth = 'TEaOzAz3iZaFHyg7QwKolAVViYEJhphe';

    /**
     * @var string
     */
    public $dic_popl = 'CZ0000000';

    /**
     * @var string
     */
    public $id_provoz = '1';

    /**
     * @var string
     */
    public $id_pokl = '1';

    /**
     * @var array
     */
    private $zpusoby_platby = [
        'karta' => [
            "nazev" => "Platební karta"
        ],
        'hotovost' => [
            "nazev" => "Hotovost"
        ]
    ];

    /**
     * @var array
     */
    private $products = [];

    /**
     * @var int
     */
    public $celk_trzba = 0;

    /**
     * @var int
     */
    public $round = 0;

    /**
     * @var int
     */
    public $porad_cis = 1;

    /**
     * @var null
     */
    public $footerReceipt = null;

    /**
     * @var bool
     */
    public $downloadReceipt = false;

    /**
     * @var string
     */
    public $downloadPath = null;

    /**
     * @var bool
     */
    public $prvni_zaslani = true;

    /**
     * @var bool
     */
    public $productsWithDph = false;

    /**
     * @var float
     */
    public $dph = 1.21;

    /**
     * Now not need
     * @var bool
     */
    public $storno = false;

    /**
     * @var void
     */
    private $onSuccess;

    /**
     * @var void
     */
    private $onError;

    /**
     * @var void
     */
    private $onApiError;

    /**
     * @var void
     */
    private $onEETError;

    /**
     * @var void
     */
    private $onWarning;

    /**
     * @var void
     */
    private $onBefore;

    /**
     * Sender constructor.
     * @param $auth
     * @param $dic_popl
     * @param $id_provoz
     * @param $id_pokl
     * @param $productsWithDph
     * @param $dph
     * @param $downloadReceipt
     * @param $downloadPath
     * @param $footerReceipt
     */
    public function __construct(
        $auth,
        $dic_popl,
        $id_provoz,
        $id_pokl,
        $productsWithDph = false,
        $dph = 21,
        $downloadReceipt = false,
        $downloadPath = null,
        $footerReceipt = null)
    {

        $this->auth = $auth;
        $this->dic_popl = $dic_popl;
        $this->id_provoz = $id_provoz;
        $this->id_pokl = $id_pokl;
        $this->downloadReceipt = $downloadReceipt;
        $this->productsWithDph = $productsWithDph;
        $this->dph = ($dph > 2 ? 1 + ($dph / 100) : $dph);

        if ($productsWithDph === true) {
            $this->dph = 1;
        }

        if ($downloadPath !== null) {
            $this->downloadPath = $downloadPath;
        }

        if ($footerReceipt !== null) {
            $this->footerReceipt = $footerReceipt;
        }
    }

    public function isRepeat()
    {
        $this->prvni_zaslani = false;
    }

    /**
     * Now not need
     */
    public function enableStorno()
    {
        $this->storno = true;
    }

    /**
     * Now not need
     */
    public function disableStorno()
    {
        $this->storno = false;
    }

    /**
     * @param $name
     * @param $price
     */
    public function addPaymentMethod($name, $price)
    {
        $this->zpusoby_platby[$name] = $price;
    }

    /**
     * @param $name
     * @param $amount
     * @param $price
     * @return mixed
     */
    public function addProduct($name, $amount, $price)
    {
        $this->products[] = [
            'nazev' => $name,
            'mnozstvi' => $amount,
            'cena' => $price * $this->dph
        ];
        end($this->products);
        return key($this->products);
    }

    /**
     * @param $text
     */
    public function setFooterReceipt($text)
    {
        $this->footerReceipt = $text;
    }

    /**
     * @param $token
     */
    public function setAuth($token)
    {
        $this->auth = $token;
    }

    /**
     * @param $dir
     */
    public function setDownloadPath($dir)
    {
        $this->downloadPath = $dir;
    }

    /**
     * @param $value
     */
    public function setProductsWithDph($value = false)
    {
        $this->productsWithDph = $value;

        if ($value === true) {
            $this->dph = 1;
        }
    }

    /**
     * @param $price
     * @param bool $round
     */
    public function addResultPrice($price, $round = true)
    {
        $cDPH = $price * $this->dph;
        $round2 = round($cDPH);
        $this->celk_trzba = $round2;

        if ($round) {
            $z = $round2 - $cDPH;
            $this->round = ($z < 0 ? $z * -1 : $z);
        }
    }

    /**
     * @return array
     */
    private function prepareData()
    {
        $celk_trzba = $this->celk_trzba;
        $products = $this->products;
        $round = $this->round;
        $zpusoby_platby = $this->zpusoby_platby;

        if ($this->storno) {
            $celk_trzba = $celk_trzba * -1;
            $round = $round * -1;

            foreach ($zpusoby_platby as $name => $value) {
                $zpusoby_platby[$name] = $value * -1;
            }

            foreach ($products as $index => $product) {
                $products[$index]['cena'] = $product['cena'] * -1;
            }
        }

        $data = [
            'prvni_zaslani' => $this->prvni_zaslani,
            'dic_popl' => $this->dic_popl,
            'id_provoz' => $this->id_provoz,
            'id_pokl' => $this->id_pokl,
            'porad_cis' => $this->porad_cis,
            'celk_trzba' => $celk_trzba,
            'polozky_uctenky' => [],
            'zpusoby_platby' => []
        ];

        foreach ($zpusoby_platby as $name => $value) {
            $data[] = [
                'nazev' => $name,
                'castka' => $value
            ];
        }

        if (!empty($this->products)) {
            $data['polozky_uctenky'] = $products;
        }

        if ($this->round > 0) {
            $data['zaokrouhleni'] = $round;
        }

        if ($this->footerReceipt !== null) {
            $data['paticka_uctenky'] = $this->footerReceipt;
        }

        return $data;
    }

    /**
     * @param $porad_cis
     * @return mixed
     */
    public function send($porad_cis)
    {
        $this->porad_cis = $porad_cis;

        $data = $this->prepareData();
        $data_string = json_encode($data);

        $auth = 'Auth: ' . $this->auth;

        $options = [
            CURLOPT_URL => "https://rest.api-eet.cz/v1/sale-add",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [$auth]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response);

        if ($res->result == 'success') {
            if ($this->onSuccess !== null) {
                call_user_func($this->onSuccess);
            }
        } elseif ($res->result == 'api_error') {
            if ($this->onApiError !== null) {
                call_user_func($this->onApiError);
            }

            if ($this->onError !== null) {
                call_user_func($this->onError);
            }
        } elseif ($res->result == 'eet_error') {
            if ($this->onEETError !== null) {
                call_user_func($this->onEETError);
            }

            if ($this->onError !== null) {
                call_user_func($this->onError);
            }
        } else {
            if ($this->onError !== null) {
                call_user_func($this->onError);
            }
        }

        if ($res->result != 'api_error') {
            if ($this->downloadReceipt) {
                $this->downloadReceipt($res->receipt_url);
            }
        }

        return $res;
    }

    /**
     * @param $url
     * @return string
     */
    public function downloadReceipt($url)
    {
        set_time_limit(0); // unlimited max execution time
        $name = $this->downloadPath . basename($url);

        if (!file_exists($name)) {
            $options = array(
                CURLOPT_FILE => fopen($name, 'w'),
                CURLOPT_TIMEOUT => 28800, // set this to 8 hours so we dont timeout on big files
                CURLOPT_URL => $url,
            );

            $ch = curl_init();
            curl_setopt_array($ch, $options);
            curl_exec($ch);
            curl_close($ch);
        }
        return $name;
    }

    public function onSuccess($callback = null)
    {
        $this->onSuccess = $callback;
    }

    public function onError($callback = null)
    {
        $this->onError = $callback;
    }

    public function onApiError($callback = null)
    {
        $this->onApiError = $callback;
    }

    public function onEETError($callback = null)
    {
        $this->onEETError = $callback;
    }

    public function onWarning($callback = null)
    {
        $this->onWarning = $callback;
    }

    public function onBefore($callback = null)
    {
        $this->onBefore = $callback;
    }

    public function testCallback()
    {
        call_user_func($this->onSuccess);
    }
}