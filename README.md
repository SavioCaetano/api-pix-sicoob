# Integração com API de PIX do Sicoob

# Sobre o projeto

Esse projeto foi desenvolvido devido à necessidade de integração entre um site de e-commerce e o banco Sicoob. Nele foi utilizado a linguagem PHP e a biblioteca curl,
além dos conceitos de programação orientada a objetos.

O projeto contém duas classes que realizam basicamente todas funcionalidades. Uma delas é a Payload, onde foi tratada a construção do objeto payload independete do
PSP (Provedor de Serviços de Pagamento). A estrutura desse objeto é padrão e foi contruída de acordo com a documentação do banco central, ela contém as informações 
necessárias para geração do Pix.

A classe Sicoob então é responsável pela integração com esse PSP. Nessa classe estão contidos os métodos que realizam a comunicação com a API de PIX do Sicoob através 
de requisições http. A partir desse projeto é possível criar uma cobrança imediata, consultar tal cobrança e criar/consultar/deletar um webhook (responsável pelo
recebimento dos callbacks da API do Sicoob). Isso só é possível após a realização da autenticação e recebimento de um Access Token, o que também foi tratado nessa
classe.

O projeto conta com um arquivo de configurações onde foram criadas algumas constantes para facilitar o uso e edição de informações sensíveis, como por exemplo,
chave pix, senha de certificado, nome do titular da conta e também as próprias url's do PSP, tornando simples uma modificação de cenário caso necessário.

## Layout Web

![Web_1](https://github.com/SavioCaetano/api-pix-sicoob/raw/main/Pix.png)

# Tecnologias utilizadas

- PHP
- Composer (gerenciador de dependências)

# Dependências

- PHP >= 7.0
- mpdf/qrcode

# Autor

Sávio Cardoso Caetano

https://www.linkedin.com/in/savio-c-caetano/
