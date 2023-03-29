<?php
/** URL de autenticação para API PIX do SICOOB */
define("URL_AUTENTICACAO", "https://auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token");

/** URL de produção API PIX do SICOOB */
define("URL_PIX", "https://api.sicoob.com.br/pix/api/v2");

/** Aqui deve ser informado o valor da chave CLIENT_ID que precisa ser criada no portal (https://developers.sicoob.com.br/portal/) conforme documentação do SICOOB 
 *  Chave fictísia, meramente para exemplificar o formato da mesma */
define("SICOOBPIX_CLIENT_ID", "21a93b72-0wdc-2jk3-6rt5-p1738zu914gs");

/** Caminho do certificado público (A1) - Emitido no CPF ou CNPJ do Cooperado */
define("SICOOBPIX_CAMINHO_CERT_PUBLICO", "./admin/certificados/certificadoPublico.pem");

/** Caminho do certificado privado (A1) - Emitido no CPF ou CNPJ do Cooperado */
define("SICOOBPIX_CAMINHO_CERT_PRIVADO", "./admin/certificados/certificadoKey.pem");

/** Senha do certificado (Caso os arquivos publico e privado tenham senhas diferentes é necessário criar uma constante para cada um) */
define("SICOOBPIX_SENHA_CERT", "abc123");

// Definindo chave pix recebedor
define("SICOOBPIX_CHAVE", "12345678900");

// Definindo titular da conta
define("SICOOBPIX_TITULAR", "Fulano de Tal");

// Definindo cidade do Titular da conta
define("SICOOBPIX_CID_TITULAR", "BELO HORIZONTE");

?>