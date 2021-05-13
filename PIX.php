<?php

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

    class Container{
        
        public function __construct($container){

            $this->container = $container;
        }

        public function retornaValorPayload($id,$value){

            $size = str_pad(strlen($value),2,'0',STR_PAD_LEFT);
            return $id.$size.$value;
        }

        public function enviarRequisicao($tipo,$url){

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $tipo,
            CURLOPT_POSTFIELDS => $this->container->dadosInfo->getJson(),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->container->accessToken,
                'Content-Type: text/plain',
                'Cache-Control: no-cache'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        }
    }    
  
    class Token extends Container {

        public function __construct($container){

            $this->container = $container;
        }

        public function obter(){
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://oauth.hm.bb.com.br/oauth/token?',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials&scope=cob.read%20cob.write%20pix.read%20pix.write',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: '.$this->obterBasic(),
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $json = json_decode($response);

            if(isset($json->error)){
                die($json->error);
            }else{
                return $json->access_token;
            }
        } 

        public function obterComCertificado(){
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL             => $this->container->url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_USERPWD         => $this->container->clientId . ':' . $this->container->clientSecret,
                CURLOPT_HTTPAUTH        => CURLAUTH_BASIC,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => 'grant_type=client_credentials&scope=cob.read%20cob.write%20pix.read%20pix.write',
                CURLOPT_SSLCERT         => $this->container->CertUrl,
                CURLOPT_SSLCERTPASSWD   => $this->container->CertPass,
                CURLOPT_HTTPHEADER      => array(
                    'Authorization: ' . $this->container->basic,
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $json = json_decode($response);

            if(isset($json->error)){
                die($json->error);
            }else{
                return $json->access_token;
            }
        } 

        private function obterBasic(){

            return  "Basic ".base64_encode($this->container->clientId.':'.$this->container->clientSecret);
        }

    }

    class SetarInfo extends Container{

        public function adicionarExpiracao($STR_EXP){

            $this->STR_EXP = $STR_EXP;
        }

        public function adicionarValor($VR_PIX){

            $this->VR_PIX = (string) number_format($VR_PIX,2,'.','');
        }

        public function adicionarChavePix($STR_CHAV){
            $this->STR_CHAV = $STR_CHAV;
        }

        public function adicionarSolicitacao($STR_SOL){
            $this->STR_SOL = $STR_SOL;
        }

        public function adicionarTxID($STR_TX_ID){
            $this->STR_TX_ID = $STR_TX_ID;
        }

        public function adicionarNomeTitular($STR_NM){
            $this->STR_NM = utf8_decode($STR_NM);
        }

        public function adicionarCidadeTitular($STR_NM_CID){
            $this->STR_NM_CID = utf8_decode($STR_NM_CID);
        }

        public function adicionarNomeDevedor($STR_NM_DEV){
            $this->STR_NM_DEV =  utf8_decode($STR_NM_DEV);
        }

        public function adicionarCPFCNPJDevedor($CPFCNPJ_DEV){
            $this->CPFCNPJ_DEV = preg_replace('/\D/','',$CPFCNPJ_DEV);
        }

        public function adicionarPagamentoUnico($LG_PAG_UNIC){
            $this->LG_PAG_UNIC = $LG_PAG_UNIC;
        }

        public function adicionarResponse($response){
            $this->response = $response;
        }

        public function adicionarLocation($location){
            $this->location = preg_replace('/^https?\:\/\//','', $location);
        }
    }

    class getInfo {

        public function __construct(SetarInfo $info){

            $this->info = $info;
        }

        function getMerchantAccountInformation(){

            $Merchant  = $this->info->retornaValorPayload(ID_MERCHANT_ACCOUNT_INFORMATION_GUI,'br.gov.bcb.pix');
            $Merchant .= (isset($this->info->STR_CHAV)) ? $this->info->retornaValorPayload(ID_MERCHANT_ACCOUNT_INFORMATION_KEY, $this->info->STR_CHAV) : '';
            $Merchant .= (isset($this->info->STR_SOL)) ? $this->info->retornaValorPayload(ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->info->STR_SOL) : '';
            $Merchant .= (isset($this->info->response->location)) ? $this->info->retornaValorPayload(ID_MERCHANT_ACCOUNT_INFORMATION_URL, $this->info->response->location) : '';

            return $this->info->retornaValorPayload(ID_MERCHANT_ACCOUNT_INFORMATION,$Merchant);
            
        }

        function getAdditionalDataFieldTemplate(){

            $txid = $this->info->retornaValorPayload(ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID,$this->info->STR_TX_ID);

            return $this->info->retornaValorPayload(ID_ADDITIONAL_DATA_FIELD_TEMPLATE,$txid);
        }

        function getUniquePayment(){
            return ($this->info->LG_PAG_UNIC) ? $this->info->retornaValorPayload(ID_POINT_OF_INITIATION_METHOD,'12') : '';
        }

        public function getJson(){

            $dados = array();

            if(isset($this->info->STR_EXP)){
                $dados['calendario']['expiracao'] = $this->info->STR_EXP;
            }

            if(isset($this->info->STR_NM)){
                $dados['devedor']['nome'] = $this->info->STR_NM;
            }
            
            if(isset($this->info->CPFCNPJ_DEV)){
                
                if(strlen($this->info->CPFCNPJ_DEV) == 11){
                    $dados['devedor']['cpf'] = $this->info->CPFCNPJ_DEV;
                }else{
                    $dados['devedor']['cnpj'] = $this->info->CPFCNPJ_DEV;
                }
                
            }

            if(isset($this->info->VR_PIX)){
                $dados['valor']['original'] = $this->info->VR_PIX;
            }

            if(isset($this->info->STR_CHAV)){
                $dados['chave'] = $this->info->STR_CHAV;
            }

            if(isset($this->info->STR_SOL)){
                $dados['solicitacaoPagador'] = $this->info->STR_SOL;
            }

            return json_encode($dados);
        }
    }

    class PixCob extends Container{
        
        public function gerar(){
            
            //gerar uma cobrança PIX Dinamica
            $response = $this->enviarRequisicao('PUT','https://api.hm.bb.com.br/pix/v1/cob/?gw-dev-app-key='.$this->container->devKey);            
            return $this->prepararPayloadDinamico($response);
            
        }

        private function prepararPayloadDinamico($response){
            
            $this->container->dadosInfo->info->adicionarResponse(json_decode($response));
            
            if(!isset( $this->container->dadosInfo->info->response->location )){
                die('Falha ao gerar cobrança pix <br>'.$response);
            }

            $this->container->dadosInfo->info->adicionarTxID($this->container->dadosInfo->info->response->txid);
            $this->container->dadosInfo->info->adicionarLocation($this->container->dadosInfo->info->response->location);
            

            //para payload dinamico nao preciso mais da chave nem da solicitacao
            unset($this->container->dadosInfo->info->STR_CHAV);
            unset($this->container->dadosInfo->info->STR_SOL);

            return true;
            
        }

    }
    
    class ConsultaCob extends Container{
        
        public function consultaUnica($txid){
            
            //consultar uma cobrança PIX

            $response = $this->enviarRequisicao('GET','https://api.hm.bb.com.br/pix/v1/cob/'.$txid.'?gw-dev-app-key='.$this->container->devKey);
            
            return $response;
            
        }

        public function consultaAmpla($pagina){
            
            //consultar uma cobrança PIX

            $response = $this->enviarRequisicao('GET','https://api.hm.bb.com.br/pix/v1/?inicio&fim&paginacao.paginaAtual='.$pagina.'&gw-dev-app-key='.$this->container->devKey);
            
            return $response;
            
        }

    }

    class QrcodePix extends Container{
        
        public function adicionarTexto($STR_TXT_QR){
            $this->container->STR_TXT_QR = $STR_TXT_QR;
        }

        public function adicionarLevel($STR_LV){
            $this->container->STR_LV = $STR_LV;
        }

        public function adicionarTamanho($INT_TAMH){
            $this->container->INT_TAMH = $INT_TAMH;
        }
    }

    class GerarQrCode{

        public function __construct(QrcodePix $qrcode){
            $this->qrcode = $qrcode;
        }

        public function gerar(){

            if (!file_exists($this->qrcode->container->tempDir))
                 mkdir($this->qrcode->container->tempDir);

            $filename = $this->qrcode->container->tempDir.'test.png';

            $errorCorrectionLevel = 'H';

            if (isset($this->qrcode->container->STR_LV) && in_array($this->qrcode->container->STR_LV, array('L','M','Q','H')))
                $errorCorrectionLevel = $this->qrcode->container->STR_LV;    

            $matrixPointSize = 4;
            if (isset($this->qrcode->container->INT_TAMH))
                $matrixPointSize = min(max((int)$this->qrcode->container->INT_TAMH, 1), 10);

            QRcode::png($this->qrcode->container->STR_TXT_QR, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

            return basename($filename);
        }
    }    

    class Payload extends Container{
        // estatico nao tem consulta de pagamento
       
        public function getPayload(){

            $payload  = $this->retornaValorPayload(ID_PAYLOAD_FORMAT_INDICATOR,'01'); 
            $payload .= $this->container->dadosInfo->getUniquePayment();
            $payload .= $this->container->dadosInfo->getMerchantAccountInformation();
            $payload .= $this->retornaValorPayload(ID_MERCHANT_CATEGORY_CODE,'0000');
            $payload .= $this->retornaValorPayload(ID_TRANSACTION_CURRENCY,'986');
            $payload .= $this->retornaValorPayload(ID_TRANSACTION_AMOUNT,$this->container->dadosInfo->info->VR_PIX);
            $payload .= $this->retornaValorPayload(ID_COUNTRY_CODE,'BR');
            $payload .= $this->retornaValorPayload(ID_MERCHANT_NAME,$this->container->dadosInfo->info->STR_NM);
            $payload .= $this->retornaValorPayload(ID_MERCHANT_CITY,$this->container->dadosInfo->info->STR_NM_CID);
            $payload .= $this->container->dadosInfo->getAdditionalDataFieldTemplate();
            
            //valida o hash
            $payload .= $this->getCRC16($payload);
            return $payload;
        }

        private function getCRC16($payload) {
            //ADICIONA DADOS GERAIS NO PAYLOAD
            $payload .= ID_CRC16.'04';
      
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
            return ID_CRC16.'04'.strtoupper(dechex($resultado));
        }

    }

?>