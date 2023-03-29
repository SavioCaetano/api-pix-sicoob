<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . "/admin/config.php";

use \App\Pix\Payload as PP;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output\Png;

$obPayload = (new PP)
                ->__set('chavePix', SICOOBPIX_CHAVE)        //informar uma chave pix válida
                ->__set('descricao', 'Integracao PIX')      //descrição do pagamento
                ->__set('titularConta', SICOOBPIX_TITULAR)  //informar nome do titular da conta da respectiva chave pix
                ->__set('cidTitular', SICOOBPIX_CID_TITULAR)//informar a cidade do titular da conta (evitar caracteres especiais)
                ->__set('valor', '50.00')                   //informar valor da transação
                ->__set('txid', 'INTPIXSICOOB123456');      //informar o txid da cobrança, o mesmo deve ser único e ter entre 1-25 caracteres (regra válida para PAYLOAD estático)

//Código de pagamento PIX
$payloadQrCode = $obPayload->getPayload();
//QR Code do PIX
$obQrCode = new QrCode($payloadQrCode);
//Gerando imagem do QR Code
$image = (new Png)->output($obQrCode, 350);

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QrCode Pix Sicoob</title>
</head>
<body style="text-align: center">
    <h1> QR CODE ESTÁTICO DO PIX</h1>
    <br>

    <img src="data:image/png;base64, <?= base64_encode($image) ?> " >
    <br>

    Código pix: <br>
    <strong> <?= $payloadQrCode ?> </strong>
</body>
</html>