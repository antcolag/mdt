<?php
class MdtCategory extends MdtEntity implements MdtStorable{
	
	private static $parentCategories;
	
	public function saveInWoocommerce(){
		$parent = $this->getParent();
		if($parent){
			$parent = $parent->saveInWoocommerce();
		} else {
			$parent = 0;
		}
		if(!$category = $this->getFromWoocommerce()){
			
			$category = wp_insert_term(
				$this->description, // the term 
				'product_cat', // the taxonomy
				[
					'description'=> $this->description,
					'slug' => $this->code,
					'parent' => $parent['term_id']
				]
			);
		}
		return $category;
	}
	public function getFromWoocommerce(){
		return term_exists($this->code, 'product_cat' );
	}
	
	public function getParent(){
		preg_match('/('.implode('|', self::$parentCategories).').+?/', $this->code, $matches);
		if(isset($matches[1])){
			$parent = MsxDropshippingTool::listCategories()[$matches[1]];
			return $parent;
		}
		return 0;
	}
	
	function productList($categoryId){
		require_once 'Product.php';
		return MdtProductList::productsByCategoryId($categoryId);
	}
	
	public function __construct($data)
	{
		if(!isset(self::$parentCategories)){
			self::$parentCategories = ['VB', 'FAL', 'OGG', 'CRE', 'CALZ', 'BDSM', 'DVD', 'GDS', 'ABU' , 'ABD'];
		}
		parent::__construct([
			'code' => $this->parseXml($data->attributes()['code']),
			'description' => ucfirst(strtolower( $this->parseXml($data) ))
		]);
	}
}



class MdtCategoryList extends MdtList implements MdtStorable{
	private static $categories;
	public function __construct(){
		if(!isset(self::$categories)){
			self::$categories = [];
			$connection = new MdtConnection('productsbycategoryid');
			foreach($connection->getResponseXml()->xpath('categories/category') as $category) {
				$category = new MdtCategory($category);
				self::$categories[$category->code] = $category;
			}
		}
		parent::__construct(self::$categories);
	}
	
	public function saveInWoocommerce(){
		foreach ($this as $value) {
			$value->saveInWoocommerce();
		}
	}
	
	public function getFromWoocommerce(){
		
	}
}