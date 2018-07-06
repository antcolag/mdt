<?php

/**
* 
*/
class MdtProductAttributeList extends MdtList
{
	protected $product;
	
	public function getProduct(){
		return $this->product;
	}
	
	public function setProduct($product){
		$this->product = $product;
		return $this;
	}
	
	public function __construct($data = [], $product = null, $tagParent = null, $attr = false)
	{
		if(!$data){
			$data = [];
		}
		$this->product = $product;
		if($data instanceof SimpleXmlElement)
			$data = self::getArray($data, $tagParent, $attr);
		parent::__construct($data);
	}
}

/**
* 
*/
class MdtFeatureList extends MdtProductAttributeList
{	
	public function saveInWoocommerce(){
		global $wpdb;
		
		$productid = $this->product->getWcPostId();
		
		$productAttributes = [];
		foreach ($this as $name => $values) {
			if(!$values)
				continue;
			$name = strtolower($name);
			$filteredName = apply_filters('sanitize_title', $name);
			

			if(!is_array($values)){
				$values = [$values];
			}
			
			$filteredValues = [];
			
			foreach ($values as $k => $v) {
				$filteredValues[$k] = apply_filters('sanitize_title', $v);
			}
    		
			$pa_name = "pa_$filteredName";			
			
			$visible = true;
			if($name == 'size'){
				foreach ($this as $key => $value) {
					if(($key != $name) && ($this[$key] == $values))
						$visible = false;
				}
			}

			
			$productAttributes[$pa_name] = [
				'name'         => $pa_name,
				'value'        => implode($filteredValues, '|'),
				'is_visible'   => $visible,
				'is_variation' => $name == 'size',
				'is_taxonomy'  => 1
			];
			
			if( count($filteredValues) > 1 ){
				wp_set_object_terms( $productid, 'variable', 'product_type', false );
			}
			
			
			foreach ($filteredValues as $v) {
				update_post_meta( $productid, "attribute_$pa_name", $v);
			}
			
			if(!taxonomy_exists($pa_name)){
				$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', [
					'attribute_name' => $filteredName,
					'attribute_label' => $filteredName,
					'attribute_type' => 'select',
					'attribute_orderby' => 'menu_order',
					'attribute_public' => false
				]);
				register_taxonomy($pa_name, 'product', [
					'hierarchical' => true,
					'label' => $name,
					'description' => $name
				]);
			}
			
			
			foreach ($filteredValues as $k => $v) {
				if( term_exists($values[$k], $pa_name) )
					continue;
				$insert_result = wp_insert_term( $values[$k], $pa_name, [
					'description'=> $values[$k],
					'slug' => $v,
					'parent'=> 0
				]);
			}
			
			$term_id = wp_set_object_terms( $productid, $filteredValues, $pa_name, true);

			
			$wpdb->insert( $wpdb->termmeta, [
				'meta_key' => "order_$pa_name",
				'term_id' => $term_id[0],
				'meta_value' => 0
			]);
		}
		$meta = update_post_meta( $productid, '_product_attributes', $productAttributes);
		
		
		flush_rewrite_rules();
		delete_transient( 'wc_attribute_taxonomies' );
		return $meta;
	}
	
	public function __construct($data, $product)
	{
		$size = false;
		if($data instanceof SimpleXmlElement){
			$size = MdtEntity::parseXml($data->SIZE);
		}
		if(isset($data['size'])){
			$size = $data['size'];
			unset($data['size']);
		}
		parent::__construct($data, $product, 'FEATURES/FEATURE', 'name');
		if( $size ){
			$this['size'] = $size;
		}
	}
}

/**
* 
*/
class MdtMediaList extends MdtProductAttributeList
{	
	public function dropProductMedias(){	
		$attachments = $this->getFromWoocommerce();
		foreach ( $attachments as $value ) {
			wp_delete_attachment( $value->ID, true );
		}
	}
	
	public function getFromWoocommerce($type = ''){
		global $wpdb;
		$msxid = $this->product->id;
		$iftype = ', type.meta_key AS type';
		if($type = $type? $type : $this->type){
			$type = "AND  type.meta_key = 'mdt-$this->type'";
			$iftype = '';
		}
		$query = $wpdb->get_results(
			"	SELECT posts.* , msxid.meta_value AS msxid $iftype
				FROM wp_1_posts AS posts 
				INNER JOIN wp_1_postmeta AS msxid
					ON ( posts.ID = msxid.post_id ) 
				INNER JOIN wp_1_postmeta AS type
					ON ( posts.ID = type.post_id )
				WHERE msxid.meta_key = 'msx-id'
					AND CAST(msxid.meta_value AS CHAR) = '$msxid'
					$type
					AND posts.post_type = 'attachment'
				GROUP BY posts.ID
				ORDER BY posts.post_date DESC	"
		);
		foreach ($query as $key => $value) {
			$query[$key] = new WP_Post($value);
		}
		return $query;
	}
	
	private $type;
	
	public function __construct($data, $product, $tagParent, $type)
	{
		$this->type = $type;
		parent::__construct($data, $product, $tagParent);
	}
}

/**
* 
*/
class MdtVideoList extends MdtMediaList
{
	public function __construct($data, $product)
	{
		parent::__construct($data, $product, "*[contains(name(),'VIDEO')]", 'video');
	}
	
	public function saveInWoocommerce(){
		$videos = [];
		$this->dropProductMedias();
		foreach ($this as $key => $value) {
			if(!$value){
				continue;
			}
			$attach_id = wp_insert_attachment([
				'post_mime_type' => 'import',
				'post_title' => $this->product->name,
				'post_content' => '',
				'post_status'=> 'inherit',
				'guid' => $value,
			],null, $this->product->getWcPostId());
			
			$videos[$key] = $attach_id;
			
			update_post_meta($attach_id, 'mdt-video', true);
			update_post_meta($attach_id, 'msx-id', $this->product->id);
			
		}
		update_post_meta( $this->product->getWcPostId(), '_product_video_gallery', implode(',', $videos));
		return $videos;
	}
}

/**
* 
*/
class MdtPhotoList extends MdtMediaList
{
	private $master;
	private $storingData = [];
	public function saveInWoocommerce(){
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$gallery = [];
		$this->dropProductMedias();
		foreach ($this as $key => $value) {
			if(!$value){
				continue;
			}
			
			$attach_id = wp_insert_attachment([
				'post_mime_type' => 'image/jpeg',
				'post_title' => $this->product->name,
				'post_content' => '',
				'post_status'=> 'inherit',
				'guid' => $value,
			],null, $this->product->getWcPostId());
			
			$a = update_post_meta($attach_id, 'mdt-image', true);
			
			$b = update_post_meta($attach_id, '_wp_attachment_image_alt', $this->product->name);
			$c = update_post_meta($attach_id, 'mdt-media-width', '500');
			$d = update_post_meta($attach_id, 'mdt-media-height', '500');
			
			update_post_meta($attach_id, 'msx-id', $this->product->id);
			if($key == 0)
				set_post_thumbnail( $this->product->getWcPostId(), $attach_id );
			else
				$gallery[] = $attach_id;
		}
		$e = update_post_meta( $this->product->getWcPostId(), '_product_image_gallery', implode(',', $gallery));
		return $gallery;
	}	
	
	public function __construct($data, $product)
	{
		parent::__construct($data, $product, 'IMAGES/*', 'image');
	}
}



class MdtListOfGrouppedProductIds extends MdtProductAttributeList
{
	public function groupProductsId(){
		if(!count($this)){
			return false;
		}
		$id = "";
		foreach (str_split($this[0]) as $key => $letter) {
			foreach ($this as $productId) {
				if($productId[$key] != $letter)
					goto end;
			};
			$id .= $letter;
		}
		end:
		$tails = [];
		foreach ($this as $productId) {
			$tails[] = substr($productId, $key);
		}
		sort($tails);
		$id .= '.'.implode('-', $tails);
		return $id;
	}
	
	public static function extractGroupProductIds($id){
		
		preg_match_all('/(.+)\.(([^-]+-?)+)/', $id, $matches);
		$result = [];
		if($matches[2]){
			foreach (explode('-', $matches[2][0]) as $value)
				$result[] = $matches[1][0].$value;
		}
		return $result;
	}
	
	public function __construct($data = [], $product)
	{
		if(is_string($data)){
			return parent::__construct(self::extractGroupProductIds($data), $product);
		}if($data instanceof SimpleXmlElement){
			$data = explode(',', (string) $data->GROUPPRODUCTS);
		}
		$data = (isset($data[0]) && $data[0] == '')? [] : $data;
		parent::__construct($data, $product);
	}
}


?>