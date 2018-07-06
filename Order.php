<?php

class MdtOrder extends MdtEntity{
	
	public static function getOrder($docnum){
		$c = __CLASS__;
		return new $c( (new MdtConnection('getorder'))->addHttpGet(['docnum' => $docnum])->getResponseXml() );
	}
	
	public static function addOrder($order){
		
		$order = new WC_Order( $order );
		$orderData = array(
			'paymentmethod'   => 3,
			'shippingmethod'  => 3,
			'shippingNAME'    => $order->shipping_first_name.' '.$order->shipping_last_name,
			'shippingADDRESS' => $order->shipping_address_1,
			'shippingCAP'     => $order->shipping_postcode,
			'shippingCITY'    => $order->shipping_city,
			'shippingSTATE'   => $order->shipping_country,
			'shippingPHONE'   => $order->billing_phone,
			'shippingEMAIL'   => $order->billing_email,
			'comments'        => $order->customer_note
		);
		
		$items = $order->get_items();
		
		$orderData['productsid'] = [];
		$orderData['productsquantity'] = [];
		
		$msx_products_titles = [];
		
		//throw new Exception(var_export($items, 1), 1);
			
		foreach ($items as $k => $product) {
			
			$productwc = new WC_Product($product['variation_id']?$product['variation_id']:$product['product_id']);
			$idmsx = get_post_meta($productwc->get_id( ), 'msx-id')[0];
			
			
			$msx_products_titles[] = $productwc->get_title();
			
			$orderData['productsid'][] = $idmsx;
			$orderData['productsquantity'][] = $product['qty'];
		}
		
		if(count($msx_products_titles) == 0){
			return;
		}
		
		$requestResponse = new MdtConnection('addorder');
		$requestResponse = $requestResponse->addHttpPost($orderData)->getResponseXml();
		
		
		if ($requestResponse->getName() == 'errors') {
			throw new MdtOrderException( (string) $requestResponse->ERRORINFO, $msx_products_titles, $order );
		}
		
		
		$trackingPage = new MdtSharedOption('tracking');
		$trackingPage = $trackingPage->getValue();
		$trackingPage = get_permalink( $trackingPage );
		
		
		
		$link = preg_replace('/\s/im', '', ($trackingPage.'?docnum='.(string) $requestResponse->DOCNUM));
		
		
		
		
		$brt_link = (string)($requestResponse->LINKTRACKING);

		$brt_id = (string)($requestResponse->TRACKING);
		
		update_post_meta( intval($order->id), 'tracking_link', $link);
		update_post_meta( intval($order->id), 'brt_link', $brt_link);
		update_post_meta( intval($order->id), 'brt_id', $brt_id);
		$c = __CLASS__;
		new $c($requestResponse);
		return;
	}
	
	private function getProductHash($order){
		$products = $order->xpath('PRODUCTS/PRODUCT');
		foreach ($products as $key => $value) {
			$products[$key] = [
				'id' => $this->parseXml( $value->ID ),
				'name' => $this->parseXml( $value->NAME ),
				'quantity' => $this->parseXml( $value->QUANTITY ),
				'barcode' => $this->parseXml( $value->BARCODE ),
				'price' => $this->parseXml( $value->PRICE )
			];
		}
		return $products;
	}
	
	public function __construct( $order ){
		parent::__construct([
			'docnum'		  => $this->parseXml($order->DOCNUM),
			'cardCode'		  => $this->parseXml($order->CARDCODE),
			'brtTrakingId'	  => $this->parseXml($order->TRACKING),
			'linkTrakingBrt'  => $this->parseXml($order->LINKTRACKING),
			'status'		  => $this->parseXml($order->STATUS),
			'orderData'		  => $this->parseXml($order->ORDERDATA),
			'shippingMethod'  => $this->parseXml($order->SHIPPINGMETHOD),
			'paymentmethod'	  => $this->parseXml($order->PAYMENTMETHOD),
			'shippingAddress' => $this->parseXml($order->SHIPPINGADDRESS),
			'comments'		  => $this->parseXml($order->COMMENTS),
			'shippingTotal'	  => $this->parseXml($order->SHIPPINGTOTAL),
			'totalDiscount'	  => $this->parseXml($order->TOTALDISCOUNT),
			'total'		 	  => $this->parseXml($order->DOCTOTAL),
			'linkPayPal'	  => $this->parseXml($order->LINKPAYMENTPAYPAL),
			'linkPaymentIWSmile' => $this->parseXml($order->LINKPAYMENTIWSMILE),
			'products'		  => $this->getProductHash($order)
		]);
	}
	
	public function getMsxProducts(){
		require_once 'Product.php';
		$products = [];
		foreach ($this->getProductHash() as $key => $value) {
			$product = new MsxDropshippingTool();
			$products[ $value['ID'] ] = MdtProduct::getProductById($value['ID'])->setQuantity($value['ID']);
		}
		return $products;
	}
}


?>