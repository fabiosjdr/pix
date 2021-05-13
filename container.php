<?php
    //Exemplo de integracao do PIX com o PSP do Banco do Brasil

    include 'PIX.php';
    include "phpqrcode/qrlib.php"; 

    $container = new stdClass();
    $container->tempDir =  dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    $info = new SetarInfo($container);
    $info->adicionarExpiracao('36000');
    $info->adicionarNomeTitular('Fulano');
    $info->adicionarCidadeTitular('Brasilia');
    $info->adicionarTxID(uniqid().uniqid().uniqid()); //no modo dinâmico mínimo 26 caracteres não pode repetir, estático mínimo 1
    $info->adicionarNomeDevedor('Devedor de teste');
    $info->adicionarCPFCNPJDevedor('06268143590');
    $info->adicionarValor('3.37');
    $info->adicionarChavePix('28779295827'); 
    $info->adicionarPagamentoUnico(true);
    
    $info->adicionarSolicitacao('Pix dinamico');
   
    $container->dadosInfo = new getInfo($info);    
    
    $container->clientId = '{{seuclienteID}}';
    $container->secret   = '{{seuclienteSecret}}';
    $container->devKey   = '{{seuDevKey}}'; 
    
    // obter token
    $Token = new Token($container);
    $container->accessToken = $Token->obter();

     
    // obter token com certificado 
        // $container->CertUrl  = '{{urlCeritificado}}';
        // $container->CertPass = '{{senhaCeritificado}}';        
        // $container->accessToken = $Token->obterComCertificado();

   
    
    //PIX DINAMICO         

    $PixCob = new PixCob($container); 
    $PixCob->gerar(); //Gera o pix no PSP
    
    $payload   = new Payload($container);
    $strQrCode = $payload->getPayload(); // pega o retorno payload
    
    $QRCODEPIX = new QrcodePix($container);
    $QRCODEPIX->adicionarTexto($strQrCode);
    $QRCODEPIX->adicionarLevel('H');

    $QRBUILD = new GerarQrCode($QRCODEPIX);
    $file = $QRBUILD->gerar(); // gera o QRCODE

    echo "<img src='temp/".$file."' >";


    // Consultar pix gerado
    $PixCob = new ConsultaCob($container);
    $response = $PixCob->consultaUnica($container->dadosInfo->info->response->txid);

    // $PixCob = new ConsultaCob($container);
    // $response = $PixCob->consultaAmpla(2);

    echo '<pre>';
    print_r($response);
    echo '</pre>';
    exit;

    //// PIX ESTATICO    ///// não depende de PSP mas não tem consulta
    $payload   = new Payload($container);
    $strQrCode = $payload->getPayload(); 
    
    $QRCODEPIX = new QrcodePix($container);
    $QRCODEPIX->adicionarTexto($strQrCode);
    $QRCODEPIX->adicionarLevel('H');

    $QRBUILD = new GerarQrCode($QRCODEPIX);
    $file = $QRBUILD->gerar();

    echo "<img src='temp/".$file."' >";

    exit;
?>