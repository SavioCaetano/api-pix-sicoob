<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . "/admin/config.php";

use \App\Pix\Payload as PP;
use \App\Pix\Sicoob as SP;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output\Png;

//Instancia um objeto ApiSicoob com os dados iniciais configurados no construtor da classe
$objPix = new SP();

//Cria uma cobrança para ser enviada ao banco
$cobranca = [
    'calendario' => [
        'expiracao' => 3600                             //Tempo em segundos para expiração do qrCode
    ],
    'devedor' => [
        'cpf' => '00987654321',                         //CPF ou CNPJ do devedor
        'nome' => 'Ciclano de Oliveira'                 //Nome do devedor ou razão social
    ],
    'valor' => [
        'original' => '50.00'                           //Valor da transação
    ],
    'chave' => SICOOBPIX_CHAVE,                         //Chave pix do beneficiário
    'solicitacaoPagador' => 'Mensagem para o Pagador'   //Mensagem que pode ser enviada ao pagador para ser visualizada no momento de scanear o QR Code
];

//A função criaCob faz uma chamada à API do Sicoob enviando os dados da cobrança e retorna um json com dados da cobrança imediata criada
$resposta = $objPix->criarCob($cobranca);

/** 
 * Verificação simples
 * O campo location é a URL da cobrança imediata que foi criada
 * Caso exista esse campo no retorno da chamada a API do Sicoob sabe-se que a criação da cobrança funcionou corretamente
 */
if (isset($resposta['location'])) {
    //instância principal do payload pix
    $obPayload = (new PP)
            ->__set('titularConta', SICOOBPIX_TITULAR)         //nome do titular da conta (beneficiário)
            ->__set('cidTitular', SICOOBPIX_CID_TITULAR)       //cidade do titular da conta (evitar caracteres especiais)
            ->__set('valor', $resposta['valor']['original'])   //valor da transação conforme criação da cobrança imediata
            ->__set('txid', $resposta['txid'])                 //txid gerado automaticamente ao criar cobrança imediata
            ->__set('url', $resposta['location'])              //url do payload dinâmico
            ->__set('pagamentoUnico', true);                   //valor padrão "false" definido na classe Payload, só precisa ser informado caso queira alterar

    //Código de pagamento PIX
    $payloadQrCode = $objPayload->getPayload();
    //QR Code do PIX
    $qrCode = new QrCode($payloadQrCode);
    //Gerando imagem do QR Code
    $image = (new Png)->output($qrCode, 350);

} else {
    echo 'Problema ao gerar PIX';
    echo '<pre>';
    print_r($resposta);
    echo '</pre>';
    exit;
}

?>

<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QrCode Pix Sicoob</title>
</head>
<body style="text-align: center">
    <h1> QR CODE DINÂMICO DO PIX</h1>
    <br>

    <img src="data:image/png;base64, <?= base64_encode($image) ?> " >
    <br>

    Código pix: <br>
    <strong> <?= $payloadQrCode ?> </strong>
</body>
</html>