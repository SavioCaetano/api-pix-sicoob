<?php

namespace App\Pix;

require_once __DIR__ . "/../../admin/config.php";

class Sicoob {

    /**
     * URL de autenticação do PSP
     * @var string
     */
    private $urlAutenticacao;

    /**
     * URL base do PSP
     * @var string
     */
    private $urlPix;

    /**
     * Cliente ID do PSP
     * @var string
     */
    private $clientId;

    /**
     * Caminho absoluto até o arquivo do certificado público
     * @var string
     */
    private $certPublico;

    /**
     * Caminho absoluto até o arquivo do certificado privado
     * @var string
     */
    private $certPrivado;

    /**
     * São as opções de escopo que serão solicitadas durante a autenticação (escrita, leitura)
     */
    private $scope;

    /**
     * Token de acesso à API do PIX retornado após autenticação
     * @var string
     */
    private $token;

    /**
     * Hora que foi gerado o token
     * @var string
     */
    private $horaToken;

    /**
     * Define os dados iniciais da classe
     */
    public function __construct() {
        $this->urlAutenticacao = URL_AUTENTICACAO;
        $this->urlPix = URL_PIX;
        $this->clientId = SICOOBPIX_CLIENT_ID;
        $this->certPublico = [realpath(SICOOBPIX_CAMINHO_CERT_PUBLICO), SICOOBPIX_SENHA_CERT];
        $this->certPrivado = [realpath(SICOOBPIX_CAMINHO_CERT_PRIVADO), SICOOBPIX_SENHA_CERT];
    }

    /**
     * Método responsável por criar uma cobrança imediata
     * @param array $request
     * @return array
     */
    public function criarCob($requisicao) {
        return $this->send('POST', '/cob', $requisicao);
    }

    /**
     * Método responsável por consultar uma cobrança imediata
     * ******* Opções de parâmetros opcionais para consulta *******
     * ['inicio' => 'date-time', 'fim' => 'date-time', 'cpf' => 'numCPF', 'cnpj' => 'numCNPJ', 'status' => 'status'];
     * @param string $txid
     * @return array
     * @throws \Exception
     */
    public function consultCob(string $txId = null, array $parametros = null) {
        try {
            if (is_null($txId) && is_null($parametros)) {
                throw new \Exception('Obrigatório algum parâmetro para consulta');
            } if (!is_null($txId)) {
                return $this->send('GET', '/cob/' . $txId);
            } else {
                $stringConsulta = http_build_query($parametros);
                return $this->send('GET', "/cob?{$stringConsulta}");
            }

        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    /**
     * Método responsável por configurar o webhook pix
     * @param string $chave
     * @param string $urlWebhook
     * @return bool
     * @throws \Exception
     */
    public function criaWebhook(string $chave, string $urlWebhook) {
        try {
            $webhook = ['webhookUrl' => $urlWebhook];
            $this->send('PUT', "/webhook/{$chave}", $webhook);
            return true;
        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    /**
     * Método responsável por consultar webhook cadastrado
     * @param string|null $chave
     * @return string
     * @throws \Exception
     */
    public function consultWebhook(string $chave = null) {
        try {
            $url = is_null($chave) ? '/webhook' : "/webhook/{$chave}";
            return $this->send('GET', $url);
        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    /**
     * Método responsável por excluir webhook
     * @param string $chave
     * @return bool
     * @throws \Exception
     */
    public function deletarWebhook(string $chave)
    {
        try {
            $this->send('DELETE', "/webhook/{$chave}");
            return true;
        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    /**
     * Método responsável por obter o token de acesso à API Pix
     * @return void
     */
    private function gerarToken() {
        //Funções do escopo da autenticação
        $this->scope = implode(' ', ['cob.read', 'cob.write', 'pix.write', 'pix.read', 'webhook.read', 'webhook.write']);

        //Endpoint de autenticação
        $endPoint = $this->urlAutenticacao;
        
        //Cabeçalho da requisição
        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        //corpo da requisitação
        $request = 'grant_type=client_credentials' . '&' . 'client_id=' . $this->clientId . '&' . 'scope=' . $this->scope;

        $curl = curl_init();
        
        //define as opções da chamada da requisição
        curl_setopt_array($curl, [
            CURLOPT_URL             => $endPoint,               //endereço para onde será feita a requisição
            CURLOPT_RETURNTRANSFER  => true,                    //este parâmetro diz que queremos resgatar o retorno da requisição
            CURLOPT_CUSTOMREQUEST   => 'POST',                  //método http que será utilizado na requisição
            CURLOPT_POSTFIELDS      => $request,                //enviando os parâmetros (corpo) da requisição (PUT ou POST)
            CURLOPT_SSLCERT         => $this->certPublico[0],   //definindo certificado público através do caminho absoluto do mesmo
            CURLOPT_SSLCERTPASSWD   => $this->certPublico[1],   //definindo a senha do certificado publico informado
            CURLOPT_SSLKEY          => $this->certPrivado[0],   //definindo certificado privado através do caminho absoluto do mesmo
            CURLOPT_SSLKEYPASSWD    => $this->certPrivado[1],   //definindo a senha do certificado privado informado
            CURLOPT_HTTPHEADER      => $headers                 //insere o cabeçalho na requisição
        ]);

        $resposta = curl_exec($curl);
        curl_close($curl);

        //resposta convertida de json para array
        $resposta = json_decode($resposta, true);

        $this->token = $resposta;
        $this->horaToken = time();
    }

    /**
     * Método responsável por pegar e verificar se o token foi gerado e está dentro do prazo de expiração
     * Caso já tenha expirado é gerado um novo token
     * @return string
     */
    public function getToken() {
        if (is_null($this->token)) {
            $this->gerarToken();
        }
        $token = $this->token['access_token'];
        $tokenExpiracao = $this->horaToken + $this->token['expires_in'];
        if ($tokenExpiracao < time()) {
            $this->gerarToken();
        }
        $token = $this->token['access_token'];
        return $token;
    }

    /**
     * Método responsável por enviar requisições para o PSP
     * @param string $metodo
     * @param string $recurso
     * @param array $requisicao
     * @return array
     */
    private function send($metodo, $recurso, $requisicao = []) {
        //Endpoint completo
        $endPoint = $this->urlPix . $recurso;

        //Cabeçalho da requisição
        $headers = [
            'Cache-Control: no-cache',                      //para evitar problemas com cache
            'Content-type: application/json',               //informando que o corpo da requisição é um json
            'Authorization: Bearer ' . $this->getToken()    //autorização (Access Token da autenticação)
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL             => $endPoint,               //endereço para onde será feita a requisição
            CURLOPT_RETURNTRANSFER  => true,                    //este parâmetro diz que queremos resgatar o retorno da requisição
            CURLOPT_CUSTOMREQUEST   => $metodo,                 //método http que será utilizado na requisição (POST, PUT, GET, DELETE)
            CURLOPT_SSLCERT         => $this->certPublico[0],   //definindo certificado público através do caminho absoluto do mesmo
            CURLOPT_SSLCERTPASSWD   => $this->certPublico[1],   //definindo a senha do certificado publico informado
            CURLOPT_SSLKEY          => $this->certPrivado[0],   //definindo certificado privado através do caminho absoluto do mesmo
            CURLOPT_SSLKEYPASSWD    => $this->certPrivado[1],   //definindo a senha do certificado privado informado
            CURLOPT_HTTPHEADER      => $headers                 //insere o cabeçalho na requisição
        ]);

        //condição para caso o método seja POST ou PUT
        switch($metodo) {
            case 'POST':
            case 'PUT':
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requisicao)); //enviando os parâmetros (corpo) da requisição
                break;
        }

        $resposta = curl_exec($curl);
        curl_close($curl);

        //retorna a resposta convertida de json para array
        return json_decode($resposta, true);
    }
}
?>