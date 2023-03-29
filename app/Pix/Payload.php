<?php

namespace App\Pix;

class Payload {
    
    /**
    * IDs do Payload do Pix - Fonte: documentação do banco central
    * @var string
    */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_POINT_OF_INITIATION_METHOD = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_ACCOUNT_INFORMATION_URL = '25';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    /**
     * Chave pix
     * @var string
     */
    private $chavePix;

    /**
     * Descrição do pagamento
     * @var string
     */
    private $descricao;

    /**
     * Nome do titular da Conta
     * @var string
     */
    private $titularConta;

    /**
     * Cidade do titular da conta
     * @var string
     */
    private $cidTitular;

    /**
     * Id da transação
     * @var string
     */
    private $txid;

    /**
     * Valor da transação
     * @var string
     */
    private $valor;

    /**
     * Define se o pagamento deve ser feito apenas uma vez
     * @var boolean
     */
    private $pagamentoUnico = false;

    /**
     * URL do payload dinâmico (brcode)
     * @var string
     */
    private $url;

    public function __get($attr) {
        return $this->$attr;
    }

    public function __set($attr, $value) {
        $this->$attr = $value;
        return $this;
    }

    /**
     * Responsável por retornar o valor completo de um objeto do payload
     * @param string $id
     * @param string $valor
     * @return string $id + $tamanho + $valor
     */
    private function getValor($id, $valor) {
        $tamanho = str_pad(strlen($valor),2,'0',STR_PAD_LEFT);
        return $id.$tamanho.$valor;
    }

    /**
     * Método responsável por retornar os valores completos da informação da conta
     * @return string
     */
    private function getInformacoesContaComerciante() {
        //domínio do banco
        $gui = $this->getValor(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI, 'br.gov.bcb.pix');
        
        //chave pix (no qr code dinâmico é opcional informar esse valor, pois o mesmo está contido na url do payload)
        $chave = strlen($this->chavePix) ? $this->getValor(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY, $this->chavePix) : '';
        
        //descrição do pagamento (valor opcional)
        $descricao = strlen($this->descricao) ? $this->getValor(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->descricao) : '';
        
        //URL do QR Code dinâmico. É preciso retirar o https dessa url
        $url = strlen($this->url) ? $this->getValor(self::ID_MERCHANT_ACCOUNT_INFORMATION_URL, preg_replace('/^https?\:\/\//', '', $this->url)) : '';
        
        return $this->getValor(self::ID_MERCHANT_ACCOUNT_INFORMATION, $gui.$chave.$descricao.$url);
    }

    /**
     * Método responsável por retornar os valores completos do campo adicional do pix (TXID)
     * @return string
     */
    private function getCampoDadosAdicionais() {
        $txid = $this->getValor(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->txid);
        return $this->getValor(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $txid);
    }

    /**
     * Método responsável por retornar o valor do ID_POINT_OF_INITIATION_METHOD
     * @return string
     */
    private function getPagamentoUnico() {
        return $this->pagamentoUnico ? $this->getValor(self::ID_POINT_OF_INITIATION_METHOD, '12') : '';
    }

    /**
     * Método responsável por gerar o código completo do payload do pix
     * @return string
     */
    public function getPayload() {
        $payload = $this->getValor(self::ID_PAYLOAD_FORMAT_INDICATOR, '01').
                    $this->getPagamentoUnico().
                    $this->getInformacoesContaComerciante().
                    $this->getValor(self::ID_MERCHANT_CATEGORY_CODE, '0000').
                    $this->getvalor(self::ID_TRANSACTION_CURRENCY, '986').
                    $this->getvalor(self::ID_TRANSACTION_AMOUNT, $this->valor).
                    $this->getvalor(self::ID_COUNTRY_CODE, 'BR').
                    $this->getvalor(self::ID_MERCHANT_NAME, $this->titularConta).
                    $this->getvalor(self::ID_MERCHANT_CITY, $this->cidTitular).
                    $this->getCampoDadosAdicionais();

        return $payload.$this->getCRC16($payload);
    }

    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    private function getCRC16($payload) {
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16.'04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16.'04'.strtoupper(dechex($resultado));
    }

}

?>