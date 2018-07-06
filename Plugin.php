<?php


class MsxDropshippingTool
{
	private $viewer;
	private static $catalog;
	private $lastResult;
	
	
	public function __construct($viewer = null)
	{
		$this->viewer = $viewer;
	}
	
	static public function listCategories(){
		return self::getCatalog()->listCategories();
	}
	
	static public function productsByCategoryId($code, $group = false){
		return self::getCatalog()->productsByCategoryId($code, $group);
	}
	
	static public function getOrder($docnum){
		require_once 'Order.php';
		return MdtOrder::getOrder($docnum);
	}
	
	static public function addOrder($order){
		require_once 'Order.php';
		return MdtOrder::addOrder($order);
	}
	
	static public function fullCatalog($group = true){
		return self::getCatalog()->fill($group);
	}
	
	static public function findProductsByIds($ids = []){
		return self::getCatalog()->findProductsByIds($ids);
	}
	
	static public function productList(){
		return self::getCatalog()->productList();
	}
	
	static public function deleteProducts($products = []){
		if(!$products instanceof MdtProductList){
			$products = new MdtProductList($products);
		}
		return $products->deleteFromWoocommerce();
	}
	
	static public function autoRemove(){
		self::initStatus('auto remove');
		$r = self::lostInWc()->deleteFromWoocommerce();
		self::unlock();
		return $r;
	}
	
	static public function saveAll(){
		self::initStatus('save products');
		$selected = self::getSelectedIds();
		$stored = self::getStoredProducts()->keys();
		$selected = count($selected)? $selected : self::productList()->keys();
		$selected = array_diff(count($selected)? $selected : self::productList()->keys(), $stored);
		return self::forAllCategories('saveInWoocommerce', $selected);
	}
	
	static public function updateAll(){
		self::initStatus('update now');
		$selected = self::getSelectedIds();
		$selected = count($selected)? $selected : self::productList()->keys();
		$stored = self::getStoredProducts()->keys();
		$selected = array_intersect($selected, $stored);
		return self::forAllCategories('saveInWoocommerce', $selected);
	}
	
	static public function deleteAll(){
		self::initStatus('delete products');
		$selected = self::getSelectedIds();
		$selidc = count($selected) && true;
		$selected = $selidc? $selected : self::getStoredProducts()->keys();
		return self::forAllCategories('deleteFromWoocommerce', $selected, $selidc);
	}
	
	static private function forAllCategories($what, $ids = null, $useIds = false){
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set('memory_limit', -1);
		$fields = isset($_REQUEST['mdtfield'])? $_REQUEST['mdtfield'] : null;
		$r = $useIds? (new MdtProductList($ids))->{$what}(null, $fields) : self::getCatalog()->forAllCategories($what, $ids, $fields);
		self::unlock();
		return $r;		
	}
	
	static public function initStatus($status){
		if(self::isStopping())
			throw new MdtStoppingException();
		if($actualStatus = self::getStatus()['status'])
			throw new Exception("plugin busy in $actualStatus");
		self::setStatus(['status' => $status, 'start' => date('H:i:s d/M/Y'), 'reqest' => $_REQUEST]);
	}
	
	static public function saveProductsOfACategory($code){
		return self::getCatalog()->productsByCategoryId($code)->saveInWoocommerce();
	}
	
	static public function deleteProductsOfACategory($code){
		return self::getCatalog()->productsByCategoryId($code)->deleteFromWoocommerce();
	}
	
	
	static public function getSelectedIds(){
		global $_ids;
		$selected = isset($_REQUEST['msxids'])? $_REQUEST['msxids'] : $_ids;
		$selected = $selected? $selected : [];
		return $selected;
	}
	
	static public function isStopping(){
		if(self::getStatus()['status'] == 'stop'){
			return true;
		}
	}
	
	static public function stop(){
		return self::setStatus(['status'=>'stop']);
	}
	
	static public function setStatus($status){
		return self::setSharedOption('status', $status);
	}
	
	static public function getStatus(){
		return self::getSharedOption('status');
	}
	
	static public function unlock(){
		return self::setStatus(['status'=>'']);
	}
	
	static public function getCatalog(){
		if(!isset(self::$catalog)){
			require_once 'Catalog.php';
			self::$catalog = new MdtCatalog();
		}
		return self::$catalog;
	}
	
	static public function getStoredProducts(){
		return self::getCatalog()->getStoredProducts();
	}
	
	static public function lostInWc(){
		return self::getCatalog()->lostInWc();
	}
	
	static public function getSharedOption($name){
		return (new MdtSharedOption($name))->getValue();
	}
	
	static public function setSharedOption($name, $value){
		(new MdtSharedOption($name))->setValue($value);
		return self::getSharedOption($name);
	}
	
	static public function setTrackingPage($value)
	{
		return self::setSharedOption('tracking', $value);
	}
	
	static public function getTrackingPage()
	{
		return self::getSharedOption('tracking');
	}
	
	static public function createTrackingPage($title)
	{
		$wp_page = wp_insert_post([
			'post_title'	=> $title,
			'post_content'	=> '[mdt_tracking]',
			'post_excerpt'	=> '',
			'post_status'	=> 'publish',
			'post_type'		=> 'page'
		]);
		return self::setTrackingPage($wp_page);
	}
	
	public function ajax(){
		error_reporting(0);
		$rmdt = isset($_REQUEST['mdt'])? $_REQUEST['mdt'] : '';
		$rmdt = urldecode($rmdt);
		$rmdt = preg_replace('/\\\/', '', $rmdt);
		$mdt = json_decode($rmdt, true);
		$mdt = $mdt? $mdt : $rmdt;
		if(!is_array($mdt)){
			$argj = isset($_REQUEST['arguments'])? $_REQUEST['arguments'] : [];
			
			$args = json_decode(str_replace('\\', '', $argj), true);
			$args = is_array($args) ? $args : $argj;
			
			$mdt = [ [$rmdt => $args] ];
			
			
		}
		$this->viewer = $this->viewer instanceof MdtViewer? $this->viewer : new MdtViewer('json');
		$this->multiExec($mdt, true);
		die();
	}
	
	public function multiExec($mdt, $print = false){
		$r = [];
		foreach ($mdt as $index => $value) {
			$k = array_keys($value)[0];
			if($value[$k] == '$')
				$value[$k] = $this->lastResult;
			if(!is_array($value[$k]))
				$value[$k] = [$value[$k]];
			$r[$index] = $this->exec($k, $value[$k]);
		}
		return $print? $this->viewer->fire( count($r) == 1 ? $r[0] : $r) : $r;
	}
	
	public function exec($method, $arguments = [], $print = false){
		$r = [];
		try {
			$r = call_user_func_array([$this, $method], $arguments);
		}
		catch (MdtException $e){
			$r = ["error" => $e->getMessage(), "type" => $e->type()];
		}
		catch (Exception $e){
			print_r($e);
			$r = ["error" => $e->getMessage(), "type" => "Error"];
		}
		$this->lastResult = $r;
		if($print){
			$this->viewer->fire($r);
		}
		return $r;
	}
	
	static public function tryToCloseConnection($msg = '')
	{
		ob_start();
		echo ("$msg");
		$size = ob_get_length();
		echo ("\r\n");
		header("Content-Length: 0");
		header("Connection: Close");
		ob_end_flush();
		ob_flush(); 
		flush();  // curl stops here, Chrome and Firefox no
		session_write_close();
	}
	
	static public function setSchedule($value = null, $start = null){
		return MdtCron::setSchedule($value, $start);
	}
	static public function getNextScheduleTime($pretty = null){
		return MdtCron::getNextScheduleTime($pretty);
	}
	static public function setScheduleFrequency($value, $start = null){
		return MdtCron::setScheduleFrequency($value, $start);
	}
	static public function getScheduleFrequency($pretty = null){
		return MdtCron::getScheduleFrequency($pretty);
	}
	static public function clearSchedule(){
		return MdtCron::clearSchedule();
	}
	static public function setOneShotSchedule($start = null){
		return MdtCron::setOneShotSchedule($start);
	}
	static public function clearOneShotSchedule(){
		return MdtCron::clearOneShotSchedule();
	}
	static public function getNextOneShotScheduleTime($pretty = null){
		return MdtCron::getNextOneShotScheduleTime($pretty);
	}
	
	
	public function autoUpdate()
	{
		self::initStatus('auto update');
		MsxDropshippingTool::tryToCloseConnection( json_encode(self::getStatus()) );
		$settings = MsxDropshippingTool::getSharedOption('updateSettings');
		$settings = $settings? $settings : [];
		$r = [];
		self::unlock();
		if(isset($settings['autoremove']) && $settings['autoremove']){
			$r['autoRemove'] = self::autoRemove();
		}
		
		$r['updateAll'] = self::updateAll();
		
		if(isset($settings['autoadd']) && $settings['autoadd']){
			$r['saveAll'] = self::saveAll();
		}
		return $r;
	}
	
}


/*

$mdt->fullCatalog()['DVDDOPP']['00600784']->toHash()
dlog(json_encode($mdt->productsByCategoryId('ABDBDSM')['00300110']->toHash()))
dlog(json_encode($mdt->fullCatalog()['DVDANAL']['00600903']->toHash()))
dlog(json_encode($mdt->productList()['00600903']->toHash()))
dlog(json_encode($mdt->productList()->saveInWoocommerce())
dlog(json_encode($mdt->productList()->deleteFromWoocommerce())



*/
?>