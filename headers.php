<?php



/**
 * @package MsxDropshippingTool
 * @version 0.0.1
 */
/*
Plugin Name: MsxDropshippingTool
Plugin URI: asdru.404.mn
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in the union between msx-international and woocommerce and you will see on the screen on every page. No, you will not.
Text Domain: msx-to-wc
Author: asdr
Version: 0.0.1
*/


/**
* share states betwen actors
*/
require_once 'Connection.php';
require_once 'Exceptions.php';
require_once 'Viewer.php';
require_once 'Cron.php';
require_once 'Plugin.php';

add_action( 'mdt_cron', 'mdt_cron_function' );
add_action( 'mdt_cron_once', 'mdt_cron_once_function' );
add_action( 'wp_ajax_mdt', 'mdt_ajax_aut' );
add_action( 'wp_ajax_nopriv_mdt', 'mdt_ajax_pub' );
add_action( 'woocommerce_checkout_order_processed', 'mdt_status_pending',  1, 1  );
add_action( 'admin_menu', 'mdt_admin_menu' );
add_action('wp_head', 'mdt_common_scripts');
//add_filter('image_add_caption_shortcode', 'glog');

add_action( 'add_meta_boxes','mdt_product_metaboxes' );
add_filter( 'get_attached_file', 'mdt_manage_attachment_file', 10, 2 );
add_filter( 'wp_get_attachment_image_src', 'mdt_manage_attachment_src', 10, 4 );
add_filter( 'wp_get_attachment_metadata', 'mdt_manage_attachment_metadata', 10, 2 );
add_filter( 'cron_schedules', 'mdt_register_cron_schedules' );

add_shortcode( 'mdt_tracking', 'mdt_tracking' );


$mdt = new MsxDropshippingTool(new MdtViewer( isset($_REQUEST['viewer'])? $_REQUEST['viewer'] : defined('MDT_CLI_API')? 'console' : 'json' ));

function mdt_common_scripts(){ ?>
<script>
jQuery(document).delegate(".pp_pic_holder", "hover", function(){jQuery(this).find("li").map(function(i){jQuery(this).removeClass("default").find("img").attr("src", pp_images[i]);})})
</script>
<?php }



function mdt_cron_function($uno){
	return (new MdtConnection())->selfRequest('autoUpdate');
}

function mdt_cron_once_function($uno){
	return mdt_cron_function($uno);
}

function mdt_register_cron_schedules( $schedules ) {
 	return MdtCron::registerMdtSchedule( $schedules );
}

function mdt_admin_menu() {
	add_submenu_page(   'edit.php?post_type=product',
						'My Sub Level in wc',
						'msx to wc2',
						'manage_options',
						'mdt-admin-sub-page',
						'mdt_settings_open' );
	
}
function mdt_settings_open(){
	require_once 'settings.php';
	return mdt_settings();
}
/* [msx_tracking_func]
 * se vuoi i parametri tipo [bartag foo="foo-value"], passa un parametro e
 * 	extract( shortcode_atts(
 *		array(
 *			'foo' => 'something default',
 *			'bar' => 'another default value',
 *		),
 * 		$parametro ) );
 */
function mdt_tracking( ) {
	require_once 'tracking.php';
	return mdt_tracking_func();
}
function mdt_manage_attachment_src($attr, $attachment_id, $size, $icon) {
	/* */if( count( get_post_meta($attachment_id, 'msx-id') ) > 0 ) {
		//print_r('mdt_manage_attachment_src');
		//print_r($attachment_id);
		$attachment = get_post($attachment_id);
		$attr[0] = $attachment->guid;
	}/* */
		//print_r($attr);
	return $attr;
}
function mdt_manage_attachment_metadata($media_dims, $attachment_id) {
	/* */if( count( get_post_meta($attachment_id, 'msx-id') ) > 0 ) {
		//print_r('mdt_manage_attachment_metadata');
		//print_r($attachment_id);
		$attachment = get_post($attachment_id);
		
		$width = get_post_meta($attachment_id, 'mdt-media-width', TRUE);
		$height = get_post_meta($attachment_id, 'mdt-media-height', TRUE);
		
		$media_dims = array('width' => $width, 'height' => $height);
	}/* */		
		//print_r($media_dims);
	return $media_dims;
}
function mdt_manage_attachment_file($file, $attachment_id){
	/* */if( count( get_post_meta($attachment_id, 'msx-id') ) > 0 ) {
		//print_r('mdt_manage_attachment_file');
		//print_r($attachment_id);
		$attachment = get_post($attachment_id);
		$file = $attachment->guid;
	}/* */
		//print_r($file);
	return $file;
}


function mdt_product_metaboxes(){
	add_meta_box('mdt_metabox_1','msx info','mdt_get_info','product','side', 'low');
	add_meta_box('mdt_metabox_2','msx info','mdt_get_info','product_variation','side', 'low');
}
//Define the insides of the metabox
function mdt_get_info(){
	global $post;
	$msxid = get_post_meta($post->ID, 'msx-id');
	if(!isset($msxid))
		return;
	$price = get_post_meta($post->ID, 'price_res');
	?>
	<style>.mdt-info-label{display:inline-block;width:30%;}.mdt-info-content{display:inline-block;width:69%;}.mdt-info-container{padding:0.5em;}</style>
	<div class="mdt-info-container"><div class="mdt-info-label">id msx:</div><div class="mdt-info-content"><?php echo $msxid[0]; ?></div></div>
	<div class="mdt-info-container"><div class="mdt-info-label">price res:</div><div class="mdt-info-content"><?php echo isset($price[0])? $price[0] : ''; ?></div></div>"
	<?php
}
function mdt_get_price_res(){
	global $post;
}

function mdt_status_pending($order){
	MsxDropshippingTool::addOrder( $order );
}

function mdt_ajax($permissive = false){
	$viewerArgs = isset($_REQUEST['viewer'])? $_REQUEST['viewer'] : 'json';
	if($viewerArgs == 'json'){
		header('Content-Type: application/json');
	}
	$mdt = new MsxDropshippingTool(new MdtViewer( $viewerArgs ));
	$mdt->ajax();
}


function mdt_ajax_aut(){
/*    ____________________________
	 ()_    _____   ______________)
	 |                           |
	 | List of ajax calls:       |
	 |                           |
	 |	all.                     |
	()___________________________)
*/
	mdt_ajax(true);
}
function mdt_ajax_pub(){
/*    ____________________________
	 ()_    _____   ______________)
	 |   The unacceptable calls  |
	 |   array lists all the     |
	 |   forbidden method        |
	()___________________________)
*/
	$acceptableCalls = [
		'listCategories',
		'productsByCategoryId',
		'fullCatalog',
		'productList',
		'getStoredProducts',
		'autoUpdate'
	];
	if(false && !in_array($_REQUEST['mdt'], $acceptableCalls)){
		die('{"error":"mdt_ajax_pub unacceptable call"}');
	}
	mdt_ajax();
}


interface MdtComponent{
	public function toHash();
}

interface MdtStorable{
	public function saveInWoocommerce();
	public function getFromWoocommerce();
}

/**
* 
*/
class MdtEntity implements MdtComponent
{	
	public static function parseXml($str){		
		return (string) $str;
	}
	
	public function __construct($data){
		$this->data = $data;
	}
	
	protected $data = null;
	
	public function __get($name){
		if(isset($this->data[$name])){
			return $this->data[$name];
		}
	}
	
	public function __set($name, $value){
		return;
	}
	
	public function toHash(){
		$hash = [];
		foreach ($this->data as $key => $value) {
			$hash[$key] = $value instanceof MdtComponent? $value->toHash() : $value;
		}
		return $hash;
	}
}

class MdtList extends ArrayObject implements MdtComponent{
	public function keys(){
		$keys = [];
		foreach ($this as $key => $value) {
			$keys[] = $key;
		}
		return $keys;
	}
	
	public function pick($itemIdList){
		$c = get_class($this);
		$itemList = new $c();
		foreach ($itemIdList as $id) {
			$itemList[$id] = $this[$id];
			unset($this[$id]);
		}
		return $itemList;
	}
	
	protected $attr = 'ID';
		
	public function toHash(){
		$hash = [];
		foreach ($this as $key => $value) {
			$hash[$key] = $value instanceof MdtComponent? $value->toHash() : $value;
		}
		return $hash;
	}
	
	public function join($data){
		foreach ($data as $key => $value) {
			$this[$key] = $value;
		}
		return $this;
	}
	
	public static function getArray( $data, $tagParent, $attr = false ){
		$element = [];
		if($attr)
			foreach ($data->xpath($tagParent) as $value)
				$element[ MdtEntity::parseXml($value->attributes()[$attr]) ] = preg_replace( '/;$/', '', MdtEntity::parseXml($value) );
		else
			foreach ($data->xpath($tagParent) as $value)
				$element[] = MdtEntity::parseXml($value);
		return $element;
	}
}


class MdtSharedOption{
	private static $padding = 'mdt_';
	
	public static function getPadding(){
		return self::$padding;
	}
	
	private $optionName;
	
	public function getOptionName($padding = false){
		return ($padding? self::getPadding() : '').$this->optionName;
	}
	
	public function setOptionName($optionName){
		$this->optionName = $optionName;
		return $this;
	}
	
	public function getValue(){
		wp_cache_delete ( $this->getOptionName(true), 'options' );
		$opt = get_option($this->getOptionName(true), null);
		return unserialize($opt);;
	}
	
	public function setValue($value = null){
		wp_cache_delete ( $this->getOptionName(true), 'options' );
		$value = serialize($value);
		return update_option( $this->getOptionName(true), $value , false );
	}
	
	public function deleteOption(){
		wp_cache_delete ( $this->getOptionName(true), 'options' );
		return delete_option($this->getOptionName(true));
	}
	public function __construct($optionName = null)
	{
		$this->optionName = $optionName;
	}
}

if (!function_exists('curl_setopt_array')) {
   function curl_setopt_array(&$ch, $curl_options)
   {
       foreach ($curl_options as $option => $value) {
           if (!curl_setopt($ch, $option, $value)) {
               return false;
           } 
       }
       return true;
   }
}


function dlog($arg, $level = 1, $end="\n", $nokey=false){
	$STDOUT = STDOUT;
	if(!is_array($arg)){
		$arg = array($arg);
	}
	foreach ($arg as $key => $value) {
		if(!$nokey){
			fwrite($STDOUT, "$key] ");
		}
		if(!fwrite($STDOUT, var_export($value, true))){
			//print_r(var_export($value, true));
		}
		fwrite($STDOUT, $end);
	}
	fwrite($STDOUT, $end);
}

function glog(){
	return call_user_func_array('dlog', [func_get_args()]);
}
if (defined('MDT_CLI_API')) {
	
	require_once 'Catalog.php';
	require_once 'Product.php';
	require_once 'Category.php';
	require_once 'Order.php';
	require_once 'settings.php';
	
	function superLog(){
		do{
			$s = oneLog();
			sleep(1);
		}while( $s['status'] && ($s['status'] != 'stop') );
	}

	function stepLog(){
		do{
			$s = oneLog();
			$data = readline(' # ');
			if($data == 'q') return;
		}while( $s['status'] && ($s['status'] != 'stop') );
	}

	function oneLog(){
		$s = MsxDropshippingTool::getStatus();
		dlog($s);
		return $s;
	}
	
	function mdt($f, $args = []){
		global $mdt;
		return call_user_func_array([$mdt, $f], $args);
	};
	
	function unlock()
	{
		global $mdt;
		return $mdt->unlock();
	}
	
	function deleteAll()
	{
		global $mdt;
		return $mdt->deleteAll();
	}
	
	function saveAll()
	{
		global $mdt;
		return $mdt->saveAll();
	}
	
	function autoUpdate()
	{
		global $mdt;
		return $mdt->autoUpdate();
	}
	
	function autoRemove()
	{
		global $mdt;
		return $mdt->autoUpdate();
	}
	
	function updateAll()
	{
		global $mdt;
		return $mdt->updateAll();
	}
}
	

/*





$_ids = ['00400.068-213-214-215-216-217-218', '0070127.2-3']




$_ids = ['003001.06-07-08-09-10', '0010304.5-6']



$mdt->unlock()



$_ids = ['00601039','00601539']






mdt('deleteAll')







mdt('autoUpdate')



mdt('saveAll')




*/





?>