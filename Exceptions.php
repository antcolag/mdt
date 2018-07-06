<?php

class MdtException extends Exception
{
	public function type(){
		return get_class();
	}
	public function __construct($msg)
	{
		dlog(['MdtException' => $msg]);
		if(is_array($msg)){
			$arr = $msg;
			$msg = '';
			foreach ($arr as $key => $value) {
				$msg .= "$key => $value; ";
			}
		}
		parent::__construct($msg);
	}
}


class MdtLoginException extends MdtException
{
	
	public function type(){
		return 'Login exception';
	}
	
	private static $msg = "Mister sex login error: ";
	
	public static function NoCredentialException($usern,$passwd,$BP,$msxurl){
		$c = __CLASS__;
		$msg .= $usern? '' : 'username not set ';
		$msg .= $passwd? '' : 'password not set ';
		$msg .= $BP? '' : 'BP not set ';
		$msg .= $msxurl? '' : 'msx url not set';
		return new $c("$msg<br>usern: $usern,<br>passwd: $passwd,<br>BP: $BP,<br>url: $msxurl");
	}
	
	public function __construct($msg)
	{
		parent::__construct(self::$msg.$msg);
	}
}


class MdtRequestException extends MdtException
{	
	public function __construct($msg)
	{	
		parent::__construct($msg);
	}
}


class MdtProductException extends MdtException
{	
	public static function InsertingException($id, $postid = null){
		$c = __CLASS__;
		dlog($postid);
		return new $c("$id: the product cannot be found or inserted");
	}
	public function __construct($msg)
	{	
		parent::__construct("Error Inserting product ".$msg);
	}
}


class MdtOrderException extends MdtException
{
	
	public function __construct($msg, $productList, $order)
	{
		$msxTotal = count(isset($productList)? $productList : []);
		$total = count($order->get_items());
		$someProductsPhrase = 'rimuovere questi prodotti per proseguire:';
		$allProductsPhrase = 'i prodotti scelti non sono disponibili al momento, ci dispiace per il disguido';		
		$str = ($msxTotal < $total)? $someProductsPhrase.'<ul><li>'.implode('</li><li> ', $products).'</li></ul>' : $allProductsPhrase;
		$str = "<div class=\"msx-error\"><div>$str</div></div>";
		$order->cancel_order( $msg );
		parent::__construct($str);
	}
}

/**
* 
*/
class MdtStoppingException extends MdtException
{
	
	function __construct()
	{
		parent::__construct('Stopping');
	}
}


?>