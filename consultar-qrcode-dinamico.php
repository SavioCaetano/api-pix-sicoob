<?php

require __DIR__ . '/vendor/autoload.php';

use \App\Pix\Sicoob as SP;

//Instancia um objeto ApiSicoob com os dados iniciais configurados no construtor da classe
$objPix = new SP();

//Consulta cobrança imediata através do TXID (o exemplo abaixo é apenas um modelo do formato de um TXID)
$resposta = $objPix->consultCob('HEUZARME0CTTAV756848621598423584700');

//Consulta cobrança imediata através de parâmetros informados na requisição (filtros)
$parametros = ['inicio' => '2023-03-27T01:00:00-03:00', 'fim' => '2023-03-30T01:00:00-03:00', 'status' => 'CONCLUIDA']; // Consulte o site do Banco Central para outras opções de filtros
$resposta = $objPix->consultCob(null, $parametros);

/**
 * Verificando se foi encontrada alguma cobrança de acordo com a consulta
 * payloadURL é um campo do objeto json presente na resposta (caso positiva) da consulta
 */
if (isset($resposta['payloadURL'])) {
    echo '<pre>';
    print_r($resposta);
    echo '</pre>'; exit;

} else {
    echo 'Problema ao consultar PIX';
    echo '<pre>';
    print_r($resposta);
    echo '</pre>'; exit;
}

?>