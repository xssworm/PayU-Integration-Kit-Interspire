<?php

class CHECKOUT_PAYU extends ISC_CHECKOUT_PROVIDER
{
	/*
		The Payu merchannt Key
	*/
	public $_id = "checkout_payu";
	private $_merchanntid = 0;

	/*
		The salt to verify the order
	*/
	private $_salt = "";

	/*
		Should the order be passed through in test mode?
	*/
	private $_testmode = "";

	/**
	 * @var boolean Does this provider support orders from more than one vendor?
	 */
	protected $supportsVendorPurchases = true;

	/**
	 * The constructor.
	 */
	 
	public function __construct()
	{
		// Setup the required variables for the Payu checkout module
		parent::__construct();
		$this->SetName(GetLang('PayuName'));
		$this->SetImage("payu-logo.gif");
		$this->SetDescription(GetLang('PayuDesc'));
		$this->SetHelpText(sprintf(GetLang('PayuHelp'), $GLOBALS['ShopPathSSL']));
		$this->_height = 0;
	}

	/**
	 * Set up the configuration options for this module.
	 */
	public function SetCustomVars()
	{

		$this->_variables['displayname'] = array("name" => "Display Name",
		   "type" => "textbox",
		   "help" => GetLang('DisplayNameHelp'),
		   "default" => $this->GetName(),
		   "required" => true
		);

		$this->_variables['merchanntid'] = array("name" => "Merchant Key",
		   "type" => "textbox",
		   "help" => GetLang('PayuMerchantdHelp'),
		   "default" => "",
		   "required" => true
		);

		$this->_variables['salt'] = array("name" => "Salt",
		   "type" => "textbox",
		   "help" => GetLang('PayuHashHelp'),
		   "default" => "",
		   "required" => true
		);

		$this->_variables['testmode'] = array("name" => "Test Mode",
		   "type" => "dropdown",
		   "help" => GetLang('PayuTestModeHelp'),
		   "default" => "no",
		   "required" => true,
		   "options" => array(GetLang('PayuTestModeNo') => "NO",
						  GetLang('PayuTestModeYes') => "YES"
			),
			"multiselect" => false
		);
	}

	/**
	*	Redirect the customer to Payu's site to enter their payment details
	*/
	public function TransferToProvider()
	{
		$total = $this->GetGatewayAmount();
		$this->_merchanntid = trim($this->GetValue("merchanntid"));
		$this->_salt = trim($this->GetValue("salt"));
		
		
		
		$testmode_on = $this->GetValue("testmode");

		$orders = $this->GetOrders();
		list(,$order) = each($orders);

        $orderId =  $order['orderid'];   
        $txnid=$orderId;
		
		
		$billingDetails = $this->GetBillingDetails();
		//$shippingAddresses = $this->GetShippingAddresses();
		//$shippingDetails = current($shippingAddresses);
         
        $productInfo='Prpduct Information';
		
		$udf1='';
		$udf2='';
		$udf3='';
		$udf4='';
		$udf5='';
		$udf6='';
		$udf7='';
		$udf8='';
		$udf9='';
		$udf10='';
		
		
		$hiddenFields = array(
						
				'key'					=> $this->_merchanntid,
				'txnid'			        => $txnid,
				'amount'				=> number_format($total, 0, '.', ''),
				'productinfo'			=> $productInfo,
				'firstname'		        => $billingDetails['ordbillfirstname'],
				'Lastname'		        => $billingDetails['ordbilllastname'],
				
				'City'					=> $billingDetails['ordbillsuburb'],
				'State'					=> $billingDetails['ordbillstate'],
				'Country'				=> $billingDetails['ordbillcountry'],
				'Zipcode'				=> $billingDetails['ordbillzip'],
				'email'					=> $billingDetails['ordbillemail'],
				'phone'					=> $billingDetails['ordbillphone'],
				'Pg'				    => 'CC',
				'surl'                  => $GLOBALS['ShopPath'] . '/checkout.php?action=gateway_ping&provider='.$this->GetId(),
				'Furl'                  => $GLOBALS['ShopPath'] . '/checkout.php?action=gateway_ping&provider='.$this->GetId(),
				'curl'                  => $GLOBALS['ShopPath'] . '/checkout.php?action=gateway_ping&provider='.$this->GetId().'&id='.$orderId,
				'udf1'                  => $udf1
			
		
		);
		
		//$GLOBALS['ShopPath'] . '/checkout.php?action=gateway_ping&provider='.$this->GetId().'&id='.$orderId;
		
		
		
		
		
		
		
		
		 $salt_string=$this->_merchanntid.'|'.$txnid.'|'.number_format($total, 0, '.', '').'|'.
$productInfo.'|'.$billingDetails['ordbillfirstname'].'|'.$billingDetails['ordbillemail'].'|'.$udf1.'|'.$udf2.'|'.$udf3.'|'.$udf4.'|'.$udf5.'|'.$udf6.'|'.$udf7.'|'.$udf8.'|'.$udf9.'|'.$udf10.'|'.$this->_salt;
		
		$hash=hash('sha512', $salt_string);
		
		$hiddenFields['Hash']=$hash;
        // print_r($hiddenFields);die;
						
		if($testmode_on == "YES") 
		  $this->RedirectToProvider('https://test.payu.in/_payment.php', $hiddenFields);	
		else	
  		  $this->RedirectToProvider('https://secure.payu.in/_payment.php', $hiddenFields);
	}

	


     public function ProcessGatewayPing()
	{

       $response=$_REQUEST;
		
       if(isset($response['status']))
	   {
	   		
		$session = $response['SHOP_ORDER_TOKEN']; 
		$this->SetOrderData(LoadPendingOrdersByToken($session));
	

				 $key         = trim($this->GetValue("merchanntid"));
				 $salt        = trim($this->GetValue("salt")); 
				 $amount      = $response['amount'];
				 $productInfo = 'Prpduct Information';
				 $firstname   = $response['firstname'];
				 $email       = $response['email'];
				 
				 $txnid       = $response['txnid'];
				 $orderId    = $response['txnid'];
				  
				    $udf1='';
					$udf2='';
					$udf3='';
					$udf4='';
					$udf5='';
					$udf6='';
					$udf7='';
					$udf8='';
					$udf9='';
					$udf10='';
				  
				 
				 $keyString   =  $key.'|'.$txnid.'|'.$amount.'|'.$productInfo.'|'.$firstname.'|'.$email.'|'.$udf1.'|'.$udf2.'|'.$udf3.'|'.$udf4.'|'.$udf5.'|'.$udf6.'|'.$udf7.'|'.$udf8.'|'.$udf9.'|'.$udf10;
				 $keyArray    = explode("|",$keyString);
				 $reverseKeyArray  = array_reverse($keyArray);
				 $reverseKeyString =implode("|",$reverseKeyArray);
				 
				 
				 
				 if( isset($response) &&  $response['status'] == 'success')
				 {   
					$status='success';
					 $saltString     = $salt.'|'.$status.'|'.$reverseKeyString;
					
					//echo '<br>';
					 $sentHashString = strtolower(hash('sha512', $saltString));
					//echo '<br>';
					 $responseHashString=$_REQUEST['hash'];
					
					
					if($sentHashString == $responseHashString)
					{   
					 
					
					 
					  UpdateOrderStatus($orderId,ORDER_STATUS_PENDING,true,false);
					 $GLOBALS['ISC_CLASS_LOG']->LogSystemSuccess(array('payment', $this->GetName()), GetLang('PayuSuccess'));
					// echo $orderId; die; 
					 header("Location:finishorder.php");
					 
					// return true;
					 
					 
					}
					else
					{
					 
					   $order_status = ORDER_STATUS_DECLINED;
					    $GLOBALS['ISC_CLASS_LOG']->LogSystemSuccess(array('payment', $this->GetName()), GetLang('PayuDeclined'));
					   UpdateOrderStatus( $orderId,ORDER_STATUS_DECLINED,true,false);
					   //die;
					   header("Location:finishorder.php");
					}
					   
				 }
				 else if( $response['status'] == 'failure')
				 {
					  $order_status = ORDER_STATUS_DECLINED;
					  UpdateOrderStatus( $orderId,ORDER_STATUS_DECLINED,false,false);
					  header("Location:finishorder.php");
				 }
		 
		  
	   
	   }
	   else if(!isset($response['status']) && isset($response['id']))
	   {   
			
			
			
			$orderId= $response['id']; 
			$order_status = ORDER_STATUS_CANCELLED;
			$GLOBALS['ISC_CLASS_LOG']->LogSystemError(array('payment', $this->GetName()), GetLang('PayuFailure'),'eeee');
            UpdateOrderStatus($_GET['id'],ORDER_STATUS_CANCELLED,false,false);
		    header("Location:cart.php");
		 
		  
			  
		}
		
		
	}

	

	
	public function GetOrderToken()
	{
		return @$_COOKIE['SHOP_ORDER_TOKEN'];
	}
}