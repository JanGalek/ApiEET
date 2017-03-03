<?php

/**
 * Created by PhpStorm.
 * User: Galek
 * Date: 2.3.2017
 * Time: 13:51
 */
namespace Galek\ApiEET\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class ApiEETExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig([
            'auth' => 'TEaOzAz3iZaFHyg7QwKolAVViYEJhphe',
            'dic_popl' => 'CZ1212121218',
            'id_provoz' => 666,
            'id_pokl' => "ZKP-1",
            'productsWithDph' => true,
            'dph' => 21,
            'footerReceipt' => null,
            'downloadReceipt' => false,
            'downloadPath' => null,
        ]);

        $builder->addDefinition($this->prefix('galekeet'))
            ->setClass('Galek\ApiEET\Sender', [
                'auth' => $config['auth'],
                'dic_popl' => $config['dic_popl'],
                'id_provoz' => $config['id_provoz'],
                'productsWithDph' => $config['productsWithDph'],
                'dph' => $config['dph'],
                'id_pokl' => $config['id_pokl'],
                'downloadReceipt' => $config['downloadReceipt'],
                'downloadPath' => $config['downloadPath'],
                'footerReceipt' => $config['footerReceipt'],
            ]);

    }
}