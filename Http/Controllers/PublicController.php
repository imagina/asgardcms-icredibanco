<?php

namespace Modules\Icredibanco\Http\Controllers;

use Mockery\CountValidator\Exception;

use Modules\IcommerceCredibanco\Entities\Credibanco;
use Modules\IcommerceCredibanco\Entities\Configcredibanco;
use Modules\IcommerceCredibanco\Repositories\TransactionRepository;

use Modules\Core\Http\Controllers\BasePublicController;
use Route;
use Session;

use Modules\User\Contracts\Authentication;
use Modules\User\Repositories\UserRepository;

use Modules\Setting\Contracts\Setting;
use Illuminate\Http\Request as Requests;
use Illuminate\Support\Facades\Log;

use Modules\IcommerceCredibanco\Support\lib\mySoap\ConsultaTx;

use Modules\IcommerceCredibanco\Support\beans\VPOS_plugin_consulta;
use Modules\IcommerceCredibanco\Support\beans\VPOSConsulta;
use Modules\IcommerceCredibanco\Support\beans\VPOSConsultaResp;


class PublicController extends BasePublicController
{
  
    private $order;
    private $setting;
    private $user;
    protected $auth;
    
    protected $crediBanco;

    protected $urlSandbox;
    protected $urlProduction;

    protected $transaction;

    public function __construct(Setting $setting, Authentication $auth, UserRepository $user,TransactionRepository $transaction)
    {

        $this->setting = $setting;
        $this->auth = $auth;
        $this->user = $user;
       
        $this->urlSandbox = "https://testecommerce.credibanco.com/vpos2/MM/transactionStart20.do";
        $this->urlProduction = "https://ecommerce.credibanco.com/vpos2/MM/transactionStart20.do";

        $this->transaction = $transaction;
    }

    /**
     * Go to the payment
     * @param Requests request
     * @return redirect payment 
     */
    public function index(Requests $request)
    {
            
            $order = array(
                'id' => time(),
                'email' => $request->email,
                'first_name' => $request->firstname,
                'last_name' => $request->lastname,
                'telephone' => $request->telephone,
                'total' => $request->total,
                'payment_country' => $request->payment_country,
                'payment_zone' => $request->payment_zone,
                'payment_postcode' => $request->payment_postcode,
                'payment_country' => $request->payment_country,
                'payment_city' => $request->payment_city,
                'payment_address_1' => $request->payment_address_1,
                'shipping_country' => isset($request->CheckShipping) ? $request->payment_country : $request->shipping_country,
                'shipping_city' => isset($request->CheckShipping) ? $request->payment_city : $request->shipping_city,
                'shipping_address_1' => isset($request->CheckShipping) ? $request->payment_address_1 : $request->shipping_address_1,
                'shipping_postcode' => isset($request->CheckShipping) ? $request->payment_postcode : $request->shipping_postcode
            );

            $restDescription = "Order:{$order['id']} - {$order['email']}";

            $config = new Configcredibanco();
            $config = $config->getData();

            try {

                $crediBanco = new Credibanco();

                 if($config->url_action==0){
                    $crediBanco->setUrlgate($this->urlSandbox);
                    $pathKeys = "storage/app/keys/tests/";
                }else{
                    $crediBanco->setUrlgate($this->urlProduction);
                    $pathKeys = "storage/app/keys/";
                }

                $acquirerId = "1";

                $crediBanco->setAcquirerId($acquirerId);
                $crediBanco->setMerchantid($config->merchantId);
                $crediBanco->setTerminalCode($config->nroTerminal);
                
                $VI = $config->vec;

                $gender = "M";

                $billingNationality = isset($order["payment_country"]) ? $order["payment_country"] : "";

                $price = number_format($order["total"], 0, '.', '');

                $arrayIn = [
                    'acquirerId' => $acquirerId, 
                    'commerceId' => $config->merchantId,
                    'purchaseTerminalCode' => $config->nroTerminal,
                    'purchaseOperationNumber' => $order["id"],
                    'purchaseAmount' => $price."00",
                    'purchaseCurrencyCode' => $this->currencyISO($config->currency),
                    'purchasePlanId' => "01",
                    'purchaseQuotaId' => "001",
                    'purchaseIpAddress' => $request->ip(),
                    'purchaseLanguage' => 'SP',
                    'billingCountry' => isset($order["payment_country"]) ? $order["payment_country"] : "",
                    'billingCity' => isset($order["payment_city"]) ? $order["payment_city"] : "",
                    'billingAddress' => isset($order["payment_address_1"]) ? $order["payment_address_1"] : "",
                    'billingPhoneNumber' => isset($order["telephone"]) ? $order["telephone"] : "",
                    'billingCelPhoneNumber' => isset($order["telephone"]) ? $order["telephone"] : "",
                    'billingFirstName' => isset($order["first_name"]) ? $order["first_name"] : "",
                    'billingLastName' => isset($order["last_name"]) ? $order["last_name"] : "",
                    'billingGender' => $gender,
                    'billingEmail' => $order["email"],
                    'billingNationality' => $billingNationality,
                    'fingerPrint' => $order["id"],
                    'additionalObservations' => 'Compra realizada en Linea',
                    'shippingCountry' => $order["shipping_country"],
                    'shippingCity' => $order["shipping_city"],
                    'shippingAddress' => $order["shipping_address_1"],
                    'shippingPostalCode' => $order["shipping_postcode"],
                    'reserved1' =>  '2'
                ];
                
                
                $xmlSalida = createXMLPHP5($arrayIn);

                $namePrivateSign = trim($config->privateSign);
                $namePublicCrypto = trim($config->publicCrypto);
               
                $firmaPrivateSend = \Storage::disk('local')->get($pathKeys.$namePrivateSign);
                $cryptoPublicSend = \Storage::disk('local')->get($pathKeys.$namePublicCrypto);

                //Genera la firma Digital
                $firmaDigital = BASE64URL_digital_generate($xmlSalida,$firmaPrivateSend);

                //Ya se genero el XML y se genera la llave de sesion
                $llavesesion = generateSessionKey();

                //Se cifra el XML con la llave generada
                $xmlCifrado = BASE64URL_symmetric_cipher($xmlSalida,$llavesesion,$VI);

                if(!$xmlCifrado) return null;

                //Se cifra la llave de sesion con la llave publica dada
                $llaveSesionCifrada = BASE64URLRSA_encrypt($llavesesion,$cryptoPublicSend);

                if(!$llaveSesionCifrada) return null;
                if(!$firmaDigital) return null;

                //agregar al formulario
                $arrayOut['SESSIONKEY'] = $llaveSesionCifrada;
                $arrayOut['XMLREQ'] = $xmlCifrado;
                $arrayOut['DIGITALSIGN'] = $firmaDigital;

                $crediBanco->setXmlReq($arrayOut['XMLREQ']);
                $crediBanco->setDigitalSign($arrayOut['DIGITALSIGN']);
                $crediBanco->setSessionKey($arrayOut['SESSIONKEY']);

                
                $crediBanco->executeRedirection();
               
            } catch (Exception $e) {
                    echo $e->getMessage();
            }

    }


     /**
     * Response Page
     * @param Requests request
     * @return response
     */
    public function response(Requests $request)
    {

        //Log::info('CrediBanco Response - Recibiendo Respuesta '.time());

        $arrayIn = array(
            'XMLRES' => $request->XMLRES,
            'DIGITALSIGN' => $request->DIGITALSIGN, 
            'SESSIONKEY' => $request->SESSIONKEY
        );

        $llavesesion = generateSessionKey();

        if($arrayIn['SESSIONKEY']==null || $arrayIn['XMLRES']==null || $arrayIn['DIGITALSIGN'] == null){

            echo "No se encuentra información Resultante";
            
            Log::info('CrediBanco Response - No se encuentra información Resultante - '.time());
            
            return redirect()->route('homepage');
        }

        $config = new Configcredibanco();
        $config = $config->getData();

        $VI = $config->vec;

        if($config->url_action==0)
            $pathKeys = "storage/app/keys/tests/";
        else
            $pathKeys = "storage/app/keys/";
        

        $email_from = $this->setting->get('icommerce::from-email');
        $email_to = explode(',',$this->setting->get('icommerce::form-emails'));
        $sender  = $this->setting->get('core::site-name');

         try {

            $namePrivateCrypto = trim($config->privateCrypto);
            $namePublicCrypto = trim($config->publicCrypto);

            $cryptoPrivateRecive = \Storage::disk('local')->get($pathKeys.$namePrivateCrypto);
            $cryptoPublicSend = \Storage::disk('local')->get($pathKeys.$namePublicCrypto);

            $llavesesion = BASE64URLRSA_decrypt($arrayIn['SESSIONKEY'],$cryptoPrivateRecive);

            $xmlDecifrado = BASE64URL_symmetric_decipher($arrayIn['XMLRES'],$llavesesion, $VI);

            $validation = BASE64URL_digital_verify($xmlDecifrado, $arrayIn['DIGITALSIGN'], $cryptoPublicSend);

            $arrayOut = parseXMLPHP5($xmlDecifrado);

            $orderID = $arrayOut['purchaseOperationNumber'];
            $firstName = $arrayOut["billingFirstName"];
            $lastName = $arrayOut["billingLastName"];
            $userEmail = $arrayOut["billingEmail"];
            $total = $arrayOut["purchaseAmount"];
        
            $userFirstname = "{$firstName} {$lastName}";

            if( $arrayOut['authorizationResult'] == "00" ) {
            /* 00, indica que la transacción ha sido autorizada. Ejemplo errorCode: 00 errorMessage . Aprobada */
               
                $msjTheme = "icredibanco::email.success_order";
                $msjSubject = trans('icommerce::common.emailSubject.complete')."- Order:".$orderID;
                $msjIntro = trans('icommerce::common.emailIntro.complete');
                $state = 12;
               
            }else{

                if( $arrayOut['authorizationResult'] == "01" ) {
                    /* 01, indica que la transacción ha sido rechazada por el VPOS. Ejemplo errorCode: 01 errorMessage: Negada, consulte al emisor de la tarjeta */
                    
                    $msjTheme = "icredibanco::email.error_order";
                    $msjSubject = trans('icommerce::common.emailSubject.failed')."- Order:".$orderID;
                    $msjIntro = trans('icommerce::common.emailIntro.failed');
                    $state = 6;
                   

                }elseif( $arrayOut['authorizationResult'] == "05" ){
                    /* 05, indica que la transacción ha sido denegada en el Banco Emisor. Ejemplo errorCode: 02  ErrorMessage: Negada, puede ser tarjeta bloqueada o timeout */
                    
                    $msjTheme = "icredibanco::email.error_order";
                    $msjSubject = trans('icommercecredibanco::common.emailSubject.denied')."- Order:".$orderID;
                    $msjIntro = trans('icommercecredibanco::common.emailIntro.denied');
                    $state = 4;
                    

                }elseif( $arrayOut['authorizationResult'] == "08" ){
                    /* 08, indica que la transacción ha sido anulada. Ejemplo errorCode: 08 errorMessage: La transacción fué anulada automáticamente por CyberSource */
                    
                    $msjTheme = "icredibanco::email.error_order";
                    $msjSubject = trans('icommercecredibanco::common.emailSubject.canceled')."- Order:".$orderID;
                    $msjIntro = trans('icommercecredibanco::common.emailIntro.canceled');
                    $state = 2;
                    

                }elseif( $arrayOut['authorizationResult'] == "19" ){
                    /* 19, indica que la transacción ha sido autorizada, sujeta a evaluación. Ejemplo errorCode: 19 errorMessage: Transacción autorizada, sujeta a evaluación */
                    
                    $msjTheme = "icredibanco::email.error_order";
                    $msjSubject = trans('icommerce::common.emailSubject.pending')."- Order:".$orderID;
                    $msjIntro = trans('icommerce::common.emailIntro.pending');
                    $state = 10;
                   
                }

            }

            $content=[
                'orderID'=>$orderID,
                'msj' => $msjIntro,
                'user' => $userFirstname,
                'total' => $total
            ];

               
            icommerce_emailSend(['email_from'=>[$email_from],'theme' => $msjTheme,'email_to' => $userEmail,'subject' => $msjSubject, 'sender'=>$sender,'data' => array('title' => $msjSubject,'intro'=> $msjIntro,'content'=>$content)]);
                
            icommerce_emailSend(['email_from'=>[$email_from],'theme' => $msjTheme,'email_to' => $email_to,'subject' => $msjSubject, 'sender'=>$sender,'data' => array('title' => $msjSubject,'intro'=> $msjIntro,'content'=>$content)]);
                
            $order = array(
                "id" =>  $orderID,
                "status" => $state,
                "dateOperation" => date("Y-m-d H:i:s"),
                "total" => $arrayOut["purchaseAmount"],
                "tax" => 0
            );

            $transaction = $this->generateVoucher($order,$arrayOut,2,$config);
            $commerceName  = $this->setting->get('core::site-name');

            $tr = $this->hashData($transaction->id);
            $or = $this->hashData($orderID);

            //$tr = $this->hashDataTest($transaction->id,'e');
            //$or = $this->hashDataTest($orderID,'e');

            return redirect()->route('icredibanco.voucher.showvoucher', [$tr, $or]);
           
        }catch (Exception $e) {

            Log::info('Error en Exception'.time());
            //echo $e->getMessage();
        }
        
    }

    /**
     * Confirmation Page
     * @param Requests request
     * @return response
     */
    public function confirmation(Requests $request)
    {

        return redirect()->route('homepage');

    }

  

     /**
     * Get Iso
     * @param $currency
     * @return Code
     */
    public function currencyISO($currency){
        
        $currency = strtoupper($currency);

        if($currency=="COP")
            return 170;

        if($currency=="USD")
            return 840;

    }

      /**
     * Generate Voucher
     * @param  $order
     * @param  $arrayOut
     * @param  $type (1 = IcommerceCredibanco , 2 = Icredibanco)
     * @param  $config
     * @return transaction
     */
    public function generateVoucher($order,$arrayOut,$type,$config){
        
        $data = array(
           'order_id' => $order["id"],
           'order_status' => $order["status"],
           'type' => $type,
           'commerceId' => $arrayOut['commerceId'],
           'operationDate' => $order["dateOperation"],
           'terminalCode' => $arrayOut['purchaseTerminalCode'],
           'operationNumber' => $arrayOut['purchaseOperationNumber'],
            'currency' =>  $config->currency,
            'amount' => $order["total"],
            'tax' => $order["tax"],
            'description' => $arrayOut['additionalObservations'],
            'errorCode' => $arrayOut['errorCode'],
            'errorMessage' => $arrayOut['errorMessage'],
            'authorizationCode' => isset($arrayOut['authorizationCode'])?$arrayOut['authorizationCode']:'',
            'authorizationResult' => $arrayOut['authorizationResult']
        );

       $transaction = $this->transaction->create($data);

       return $transaction;

    }

     /**
     * Show Voucher
     * @param  $request
     * @return view
     */
    public function voucherShow(Requests $request){
        
        if(isset($request->tr) && isset($request->or)){
           
            
            $tranID = $this->dehashData($request->tr);
            $orderID = $this->dehashData($request->or);
            //$tranID = $this->hashDataTest($request->tr,'d');
            //$orderID = $this->hashDataTest($request->or,'d');
            
            $transaction = $this->transaction->findByOrderTrans($orderID,$tranID);

            if(!empty($transaction))
                $commerceName  = $this->setting->get('core::site-name');
            else
                return redirect()->route('homepage');
           
        }else{
           return redirect()->route('homepage');
        }
        
        $tpl ='icommercecredibanco::frontend.index';
        return view($tpl, compact('transaction','commerceName'));

    }

     /**
     * Encode
     * @param $data
     * @return data
     */
    public function hashData($data){
        return base64_encode($data);
    }

    /**
     * Decode
     * @param $data
     * @return data
     */
    public function dehashData($data){
        return base64_decode($data);
    }


     /**
     * Encode - Decode
     * @param $data
     * @return data
     */
    public function hashDataTest( $string, $action = 'e' ) {
        // you may change these values to your own
        $secret_key = 'kawabonga';
        $secret_iv = 'kawabonga';
     
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
     
        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }
     
        return $output;
    }

}