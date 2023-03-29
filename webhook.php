<?php

require __DIR__ . '/vendor/autoload.php';

use \App\Pix\Sicoob as SP;

$objPix = new SP();

/** 
 * Chamada de método para criação do webhook
 * Nela são informados como parâmetros a chave pix e a url para onde deve ser encaminhada o callback do webhook
 * Por padrão do Sicoob SEMPRE é adicionado ao fim da URL um /pix, ou seja, o endereço do seu webhook será sempre "https://suaurl/pix"
 * No exemplo abaixo ficaria: https://integracaopix.com.br/pixapi/pix
*/
$resposta = $objPix->criaWebhook('12345678900', 'https://integracaopix.com.br/pixapi');

/**
 * Chamada de método para consulta do webhook
 * Para consultar o webhook criados é necessário informar apenas a chave pix como parâmetro
 */
$resposta = $objPix->consultWebhook('12345678900');

/**
 * Chamada de método para exclusão do webhook
 * Para deletar o webhook criado é necessário informar apenas a chave pix como parâmetro
 */
if ($objPix->deletarWebhook('12345678900')) {
    echo 'Webhook deletado com sucesso';
} else {
    echo 'Problema ao deletar webhook';
}

?>