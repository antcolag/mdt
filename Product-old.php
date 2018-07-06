<?php
class MdtProduct extends MdtEntity implements MdtStorable{
	
	
	public function __construct($data = null){
		if($data === null)
			return;
		if($data instanceof SimpleXmlElement)
			return $this->FromXml($data);
		if ($data instanceof WP_Post) 
			return $this->FromWc($data);
		$c = __CLASS__;
		if ($data instanceof $c) 
			return self::__construct($data->toHash());
		if(is_string($data))
			return parent::__construct([
				'id'			 => $data,
				'name'			 => null,
				'description'	 => null,
				'price'			 => null,
				'priceRes'		 => null,
				'priceBase'		 => null,
				'videos'		 => new MdtVideoList([], $this),
				'images'		 => new MdtPhotoList([], $this),
				'features'		 => new MdtFeatureList([], $this),
				'groupProducts'	 => new MdtListOfGrouppedProductIds($data, $this),
				'status'		 => null,
				'category'		 => null,
				'barcode'		 => null,
				'quantity'		 => null,
				'net'			 => null
			]);
		return $this->FromHash($data);
	}
	
	private function FromWc($data){
		$this->wcProduct = new WC_Product($data);
		$this->wcPostId = $this->wcProduct->id;
		$msxid = isset($data->msxid)? $data->msxid : get_post_meta($this->wcPostId, 'msx-id')[0];
		return parent::__construct([
			'id'			 => $msxid,
			'name'			 => $data->post_title,
			'description'	 => $data->post_content,
			'price'			 => null,
			'priceRes'		 => null,
			'priceBase'		 => null,
			'videos'		 => new MdtVideoList([], $this),
			'images'		 => new MdtPhotoList([], $this),
			'features'		 => new MdtFeatureList([], $this),
			'groupProducts'	 => new MdtListOfGrouppedProductIds($msxid, $this),
			'status'		 => null,
			'category'		 => null,
			'barcode'		 => null,
			'quantity'		 => null,
			'net'			 => null,
		]);
	}
	
	private function FromXml($data){
		return parent::__construct([
			'id'			 => $this->parseXml($data->ID),
			'name'			 => $this->parseXml($data->NAME),
			'description'	 => $this->parseXml($data->DESCRIPTION_IT),
			'price'			 => $this->parseXml($data->PRICE),
			'priceRes'		 => $this->parseXml($data->PRICE_RES),
			'priceBase'		 => $this->parseXml($data->PRICE_BASE),
			'videos'		 => new MdtVideoList($data, $this),
			'images'		 => new MdtPhotoList($data, $this),
			'features'		 => new MdtFeatureList($data, $this),
			'groupProducts'	 => new MdtListOfGrouppedProductIds($data, $this),
			'status'		 => $this->parseXml($data->STATUS),
			'category'		 => $this->parseXml($data->CATEGORYID),
			'barcode'		 => $this->parseXml($data->BARCODE),
			'quantity'		 => $this->parseXml($data->QUANTITY),
			'net'			 => $this->parseXml($data->NET)
		]);
	}
	
	private function FromHash($data){
		if(isset($data['size'])){
			$data['features']['size'] = $data['size'];
		}
		return parent::__construct([
			'id'			 => $data['id'],
			'name'			 => $data['name'],
			'description'	 => $data['description'],
			'price'			 => $data['price'],
			'priceRes'		 => $data['priceRes'],
			'priceBase'		 => $data['priceBase'],
			'videos'		 => new MdtVideoList($data['videos'], $this),
			'images'		 => new MdtPhotoList($data['images'], $this),
			'features'		 => new MdtFeatureList($data['features'], $this),
			'groupProducts'	 => new MdtListOfGrouppedProductIds($data['groupProducts'], $this),
			'status'		 => $data['status'],
			'category'		 => $data['category'],
			'barcode'		 => $data['barcode'],
			'quantity'		 => $data['quantity'],
			'net'			 => $data['net']
		]);
	}
	
	
	
	
	public function setQuantity($quantity){
		$this->data['quantity'] = $quantity;
		return $this;
	}
	
	public function updateInWoocommerce($columns = null){
		return $this->saveInWoocommerce($columns);
	}
	
	protected $wcProduct;
	
	/* woocommerceproduct */
	public function getFromWoocommerce(){
		global $wpdb;
		if($this->wcproduct){
			return $this->wcproduct;
		}
		$query = $wpdb->get_results(
			"	SELECT posts.*, meta.meta_value AS msxid
				FROM $wpdb->posts as posts
				INNER JOIN $wpdb->postmeta as meta
					ON ( posts.ID = meta.post_id )
				WHERE meta.meta_key = 'msx-id'
					AND CAST(meta.meta_value as CHAR) = '$this->id'
					AND posts.post_type IN ('product', 'product_variation')
				GROUP BY posts.ID
				ORDER BY posts.post_date
					DESC	"
		);
		$factory = new WC_Product_Factory();
		foreach ($query as $key => $value) {
			$value = new WC_Product($value);
			$query[$key] = $value;
		}
		$this->wcPostId = (int) isset($query[0])? $query[0]->id : false;
		return $query;
	}
	
	protected $wcPostId = null;
	
	public function getWcPostId(){
		if( !$this->wcPostId ){
			$this->getFromWoocommerce();
			
		}
		return $this->wcPostId;
	}
	
	protected $postParent = 0;
	
	public function getWpPostStorableHash($columns = null){
		$hash = [];
		$post_excerpt = count($this->description)>101?substr($this->description, 0, 100).'...':$this->description;
		$fullHash = [
			'post_content' => $this->description,
			'post_title' => $this->name,
			'post_excerpt' => $post_excerpt,
			'post_type' => 'product'// (isset($this->postParent)? 'product' : 'product_variation')
		];
		if(!$columns)
			return $fullHash;
		foreach ($columns as $value)
			if(isset($fullHash[$value]))
				$hash[$value] = $fullHash[$value];
		return $hash;
	}
	
	public function getWpStorableMetaHash($columns = null){
		$hash = [];
		$fullHash = [
			'price' => $this->price,
			'regular_price' => $this->priceBase,
			'sale_price' => $this->price,
			'price_res' => $this->priceRes,
			'images' => $this->images,
			'videos' => $this->videos,
			'product_cat' => $this->category,
			'attributes' => $this->features,
			'barcode' => $this->barcode,
			'net' => $this->net
		];
		
		if(!$columns)
			return $fullHash;
		foreach ($columns as $value)
			if(isset($fullHash[$value]))
				$hash[$value] = $fullHash[$value];
		return $hash;
	}
	
	public function saveInWoocommerce($columns = null, $postParent = 0){
		global $wpdb;
		if(!is_array($columns)){
			$columns = [$columns];
		}
		
		$this->postParent = $postParent;
		$updateHash = [];
		$inwc = $this->getWcPostId();
		
		
		
		
		if( $inwc ){
			$metaHash = $this->getWpStorableMetaHash();
			$storableHash = $this->getWpPostStorableHash();
			$updateHash['ID'] = $inwc;
		} else {
			if(!$this->name){
				return;
			}
			$metaHash = $this->getWpStorableMetaHash();
			$storableHash = $this->getWpPostStorableHash();
			$columns = array_merge(array_keys($storableHash), array_keys($metaHash));
		}
		
		foreach ($storableHash as $key => $value)
			if(in_array($key, $columns))
				$updateHash[$key] = $value;
		$updateHash['post_status'] = 'publish';
		$updateHash['comment_status'] = 'open';
		$updateHash['post_type'] = $this->postParent? 'product_variation' : 'product';
		$updateHash['post_parent'] = $this->postParent;
		
		$postid = wp_insert_post( $updateHash );
		
		
		if(!$postid){
			$postid = $inwc;
			if(!$postid || $postid instanceof WP_Error )
				throw MdtProductException::InsertingException($this->id, $postid);
		}
		$this->wcPostId = $postid;
		if(!$inwc){
			update_post_meta($postid, 'msx-id', $this->id );
			update_post_meta($postid, '_visibility', 'visible');
			update_post_meta($postid, '_sku', 'msx-'.$this->id);
		}
		
		
		foreach ($columns as $value) {
			$data = isset($metaHash[$value])?$metaHash[$value] : '';
			if(!$data)
				continue;
			
			switch ($value) {
			case 'sale_price':
				if(isset($columns['full_price'])){
					break;
				}
			case 'price':
				if(isset($columns['full_price'])){
					$data = $metaHash['regular_price'];
				}
			case 'regular_price':
				if($this instanceof MdtVariableProduct){
					continue;
				}
				$data = floatval( implode( '.', explode( ',', $data )) );
				$value = "_$value";
			default:
				$meta = update_post_meta( $postid, $value, $data );
			case 'post_title':
			case 'post_content':
			case 'post_excerpt':
				break;
			case 'images':
			case 'videos':
				$media = $data->saveInWoocommerce();
				break;
			case 'product_cat':
				$category = MsxDropshippingTool::listCategories()[$this->category];
				if( !($wccat = $category->getFromWoocommerce()) )
					$wccat = $category->saveInWoocommerce();
				wp_set_post_terms($postid, $wccat['term_id'], 'product_cat', true);
				break;
			case 'attributes':
				$data->saveInWoocommerce();
				break;
				
			case 'full_price':
				delete_post_meta( $postid, "_sale_price" );
				break;	
			}
		}
		if(!$inwc){
			update_post_meta($postid, '_manage_stock', 'yes');
		}
		update_post_meta($postid, '_stock_status', $this->status == 0? 'outofstock' : 'instock');
		update_post_meta($postid, '_backorders', $this->status == 3? 'notify' : 'no');
		update_post_meta($postid, '_stock', $this->quantity);
		
	/* *
		if(in_array('videos', $columns) && !$this->postParent)
			$this->videos->finishStoreProductMedias();
		if(in_array('images', $columns) && !$this->postParent)
			$this->images->finishStoreProductMedias();
	/* */
		return $postid;
	}
	
	
	
	public function getWpStorableString($value='')
	{
		# code...
	}
	
	
	
	
	/* woocommerceproduct */
	public function deleteFromWoocommerce(){
		$r = [];
		$deleting = $this->getFromWoocommerce();
		foreach ($deleting as $value) {
			$value = wp_delete_post($value->id, true);
			if(isset($value->ID)){
				$r[] = $value->ID;
			}
		}
		$this->dropProductMedias();
		return $r;
	}
	
	
	
	public function dropProductMedias(){
		$this->images && $this->images->dropProductMedias();
		$this->videos && $this->videos->dropProductMedias();
	}
	public function unsetVariation($v){
		unset($this->data['groupProducts'][$v]);
	}
}


/**
* 
*/
class MdtProductList extends MdtList implements MdtStorable
{
	
	public function saveInWoocommerce($ids = null, $fields = null){
		$fields = $fields? $fields : MsxDropshippingTool::getSharedOption('fields');
		return $this->forAllProducts('saveInWoocommerce', $ids, $fields);
	}
	
	public function deleteFromWoocommerce($ids = null, $fields = null){
		return $this->forAllProducts('deleteFromWoocommerce', $ids);
	}
	
	private function forAllProducts($what, $ids = null, $fields = null){
		$r = [];
		$count = count($ids !== null? $ids : $this);
		$i = 0;
		
		$status = MsxDropshippingTool::getStatus();
		$status['doing'] = $what;
		$status['total'] = $count;
		$status['subtotal'] = count($this);
		$status['catstart'] = date('H:i:s d/M/Y');
		$status['actual'] = isset($status['actual'])?$status['actual'] : 0;
		MsxDropshippingTool::setStatus($status);
		
		foreach ($this as $value) {
			$msxid = $value->id;
			if( is_null($ids) || in_array($msxid, $ids) ){
				$i++;
				if(MsxDropshippingTool::isStopping())
					throw new MdtStoppingException();
				$id = $value->{$what}($fields);
				$status = MsxDropshippingTool::getStatus();
				$status['now'] = date('H:i:s d/M/Y');
				$status['subactual'] = $i;
				$status['actual']++;
				$status['msxid'] = $msxid;
				$status['category'] = $value->category;
				$status['result'] = $id;
				$status['memory KiB'] = memory_get_usage() >> 10;
				MsxDropshippingTool::setStatus($status);
				$r[] = $msxid;
				wp_cache_flush();
			}
		}
		return $r;
	}
	
	public function getFromWoocommerce(){
		$products = [];
		foreach ($this as $key => $value) {
			$products[$key] = $value->getFromWoocommerce()[0];
		}
		return $products;
	}
	
	public static function ProductsByCategoryId($code, $group = false){
		$c = __CLASS__;
		$connection = new MdtConnection('productsbycategoryid');
		return new $c( $connection->addHttpGet(['categoryId' => $code])->getResponseXml(), $group );
	}
	
	public function __construct($products = [], $group = true)
	{
		if($products instanceof SimpleXmlElement)
			$products = $products->xpath('products/product');
		$productHash = [];
		foreach ($products as $productObj){
			$product = new MdtProduct($productObj);
			$productHash[$product->id] = $product;
		}
		if($group)
			$productHash = self::groupVariations($productHash);
		parent::__construct($productHash);
	}
	
	private static function groupVariations($initialhash = []){
		$producthash = [];
		foreach ($initialhash as $id => $product) {
			if ( !($product instanceof MdtVariableProduct) && count( $groupProducts = $product->groupProducts ) ) {
				foreach ($groupProducts as $value) {
					if(!isset($initialhash[$value])){
						goto next;
					}
				}
				$groupProductsListId = $groupProducts->groupProductsId();
				if(!isset($producthash[$groupProductsListId])){
					$producthash[$groupProductsListId] = new MdtVariableProduct();
				}
				$product = $producthash[$groupProductsListId]->addVariation($product);
			} else {
				$producthash[$id] = $product;
			}
			next:
		}
		return $producthash;
	}
}

/**
* 
*/
class MdtVariableProduct extends MdtProduct
{
	private $variations;
	public function __construct( $product = null )
	{
		if($product == null){
			return;
		}
		$this->addVariation($product);
	}
	
	private function init( $product = null )
	{
		if(!$product){
			return false;
		}
		parent::__construct($product);
		$this->variations = new MdtProductList($product->groupProducts);
		$this->data['id'] = $product->groupProducts->groupProductsId();
		$this->data['status'] = 0;
		$this->images->setProduct($this);
		$this->videos->setProduct($this);
		$this->groupProducts->setProduct($this);
		$this->features->setProduct($this);
	}
	
	public function addVariation($product = null){
		if(!$product){
			return false;
		}
		if(!$product instanceof MdtProduct){
			$product = new MdtProduct($product);
		}
		if(!$this->data){
			$this->init($product);
			$product = new MdtProduct($product);
		}
		foreach ($product->data as $key => $value) {
			$this->data[$key] = $this->{$key}? $this->data[$key] : $value;
		}
		if($product->id == $this->id){
			return $this;
		}
		unset($product->data['videos']);
		unset($product->data['images']);
		
		$this->variations[$product->id] = $product;
		
		if( (!$this->data['status']) && ($product->data['status']) ){
			$this->data['status'] = 1;
		}
		
		foreach ($product->features as $key => $value) {
			
			if(!isset($this->features[$key])){
				$this->features[$key] = [];
			}
			$feature = $this->features[$key];
			if(!is_array($feature)){
				$feature = [$feature];
			}
			if(!is_array($value)){
				$value = [$value];
			}
			if($feature == $value){
				continue;
			}
			$feature[] = count($value)>1? $value: $value[0];
			$feature = array_unique($feature);
			$feature = array_values($feature);
			$this->features[$key] = $feature;
		}
		
		$name = [];
		$myname = explode(' ', $this->name);
		$productname = explode(' ', $product->name);
		
		foreach ($myname as $word) {
			if(in_array($word, $productname))
				$name[] = $word;
		}
		$name = join($name, ' ');
		$this->data['name'] = preg_replace('/\s$/', '', $name);
		return $this;
	}
	
	public function deleteFromWoocommerce(){
		foreach ($this->variations as $key => $value) {
			$value->deleteFromWoocommerce();
		}
		return parent::deleteFromWoocommerce();
	}
	
	public function saveInWoocommerce($columns = null){
		$myid = parent::saveInWoocommerce($columns);
		foreach ($this->variations as $id => $product) {
			$wcid = $product->saveInWoocommerce($columns, $myid);
		}
		WC_Product_Variable::sync($myid);
		return $myid;
	}
}





require_once 'ProductAttributeList.php';

?>