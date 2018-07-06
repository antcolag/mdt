<?php 
/**
* 
*/
class MdtCatalog extends MdtList
{
	private static $categories;
	private static $wcproducts;
	private static $productlist = null;
		
	public function productsByCategoryId($code, $group = false){
		if(!isset($this[$code])){
			$this[$code] = MdtProductList::productsByCategoryId($code, $group);
		}
		return $this[$code];
	}
	
	public function listCategories(){
		if(!isset(self::$categories)){
			self::$categories = new MdtCategoryList();
		}
		return self::$categories;
	}
	
	public function fill($group = true){
		$categoriesToLoad = array_diff(self::listCategories()->keys(), $this->keys());
		if($categoriesToLoad != []){
			/**/
			$catalog = [];
			foreach ($categoriesToLoad as $code) {
				self::productsByCategoryId($code, $group);
			}
			/**/
			
			
			/*/
			$catalog = (new MdtConnection('productsByCategoryId'))->multipleSelfRequest($categoriesToLoad);
			foreach ($catalog as $code => $products)
				$this[$code] = new MdtProductList($products, $group);
			/**/
		}
		return $this;
	}
	
	public function productList(){
		if(self::$productlist){
			return self::$productlist;
		}
		$list = new MdtProductList();
		foreach($this->fill() as $code => $products){
			$list->join($products);
		}
		return self::$productlist = $list;
	}
	
	public function lostInWc(){
		$storedProducts = self::getStoredProducts();
		$orphanIds = array_diff( $storedProducts->keys(), $this->productList()->keys() );
		$orphans = [];
		foreach ($orphanIds as $value) {
			$orphans[$value] = $storedProducts[$value];
		}
		return new MdtProductList($orphans);
	}
	
	
	public function forAllCategories($what, $ids = null, $fields = null){
		//self::fill();
		$r = [];
		foreach (self::fill() as $key => $value) {
			if($value = $value->{$what}($ids, $fields))
				$r[$key] = $value;
			continue;
		}
		return $r;
	}
	
	/*
	public function forAllCategories($what, $ids = null, $fields = null){
		$r = [];
		foreach (self::listCategories() as $key => $value) {
			if($value = self::productsByCategoryI($key))
				$r[$key] = $value->{$what}($ids, $fields);
			continue;
		}
		return $r;
	}
	*/
	

	static public function getStoredProducts(){
		global $wpdb;
		$query = $wpdb->get_results(
			"	SELECT posts.*, meta.meta_value AS msxid
				FROM $wpdb->posts as posts
				INNER JOIN $wpdb->postmeta as meta
					ON ( posts.ID = meta.post_id )
				WHERE meta.meta_key = 'msx-id'
					AND posts.post_type IN ('product', 'product_variation')
				GROUP BY posts.ID
				ORDER BY posts.post_date
					DESC	"
		);
		$products = [];
		if( count($query) )
			foreach ($query as $value)
				$products[ $value->msxid ] = new WP_Post($value);
		return self::$wcproducts = new MdtProductList($products);
	}
	
	
	public function findProductsByIds($ids){
		$list = $this->productList();
		$result = new MdtProductList();
		foreach ($ids as $id) {
			$result[$id] = $list[$id];
		}
		return $result;
	}
	
	public function __construct($data = [])
	{
		require_once 'Product.php';
		require_once 'Category.php';
		parent::__construct($data);
	}
}

?>