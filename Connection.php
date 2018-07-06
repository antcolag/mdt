<?php

class MdtConnection
{
	private static $usern;
	private static $passwd;
	private static $BP;
	private static $url;
	private $action;
	private $get;
	private $post;
	private $arguments;
	
	public function getAction(){
		return $this->action;
	}
	
	public function setAction($action){
		$this->action = $action;
		return $this;
	}
	
	public function addArguments($args){
		$this->addHttpVariable('arguments', $args);
		return $this;
	}
	
	public function getArguments(){
		return $this->arguments;
	}
	
	public function addHttpGet($get){
		$this->addHttpVariable('get', $get);
		return $this;
	}
	public function addHttpPost($post){
		$this->addHttpVariable('post', $post);
		return $this;
	}
	
	private function addHttpVariable($type, $data){
		if(!isset($this->{$type})){
			$this->{$type} = [];
		}
		if (!is_array($data)) {
			$data = [$data];
		}
		foreach ($data as $key => $value) {
			$this->{$type}[$key] = $value;
		}
	}
	
	public function getResponse(){
		$opts = ["ssl" => [
			"verify_peer"      => false,
			"verify_peer_name" => false
		]];
		if(isset($this->post)){
			$opts['http'] = [
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($this->post)
			];
		}
		$context  = stream_context_create($opts);
		$url = self::$url.'?action='.$this->getAction();
		$url .= '&BP='.self::$BP.'&usern='.self::$usern.'&passwd='.self::$passwd;
		$url .= (isset($this->get) ? "&".http_build_query($this->get) : '');
		$result = file_get_contents($url, false, $context);
		if($result == 'Username o password errata'){
			throw new MdtLoginException($result);
		}
		if($result === false){
			throw new MdtRequestException( error_get_last() );
		}
		return $result;
	}
	
	public function getResponseXml(){
		$r = $this->getResponse();
		try {
			$result = new SimpleXMLElement($r);
		} catch (Exception $e) {
			print_r($r);
			throw $e;
		}
		return $result;
	}
	
	public function multipleSelfRequest($list, $listByArgments = true){
		$master = curl_multi_init();
		$curl_arr = [];
		$results = [];
		if($listByArgments){
			$par = 'arguments';
			$action = $this->getAction();
		} else {
			$par = 'action';
			$arguments = null;
		}
		if(!is_array($list)){
			$list = [$list];
		}
		foreach ($list as $value) {
			${$par} = $value;
			if($arguments === null){
				$arguments = [];
			}
			if(!is_array($arguments)){
				$arguments = [$arguments];
			}
			if(count($this->arguments))
				$arguments = array_merge($arguments,$this->arguments);
			$arguments = json_encode($arguments);
			$url = admin_url('admin-ajax.php')."?action=mdt&mdt=$action".($arguments !== []?"&arguments=$arguments" : '');
			$url .= (isset($this->get) ? "&".http_build_query($this->get) : '');
			$curl_arr[$value] = curl_init($url);
			$cookies = http_build_query($_COOKIE,'','; ');
			//curl_setopt($curl_arr[$value], CURLOPT_HEADER, 1);
			$curlSettings = [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => 1,
				CURLOPT_COOKIE => $cookies,
				CURLOPT_TIMEOUT_MS => 20000
			];
			curl_setopt_array($curl_arr[$value], $curlSettings);
			/*
			curl_setopt(, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl_arr[$value], CURLOPT_POST, 1);
			curl_setopt($curl_arr[$value], CURLOPT_COOKIE, $cookies);
			*/
			curl_multi_add_handle($master, $curl_arr[$value]);
			if(!isset($this->post))
				$this->post = [];
			if(!is_array($this->post)){
				$this->post = [$this->post];
			}
			//$this->post['arguments'] = $arguments;
			curl_setopt($curl_arr[$value], CURLOPT_POSTFIELDS, http_build_query($this->post));
		}
		do {
			curl_multi_exec($master,$running);
		} while($running > 0);
		foreach ($list as $value) {
			$r = curl_multi_getcontent( $curl_arr[$value]);
			$results[$value] = json_decode( $r, true );	
		}
		$inforead = curl_multi_info_read($master);
		if(($inforead) && ($inforead['result'] !== CURLE_OK)){
			throw new MdtRequestException( $inforead  );
		}
		return $results;
	}
	
	public function selfRequest($call, $arguments = [])
	{
		return $this->addArguments($arguments)->multipleSelfRequest($call, false);
	}
	
	public function __construct($action = '', $get = null, $post = null)
	{
		self::init();
		$this->action = $action;
		if($get != null){
			$this->get = $get;
		}
		if($post != null){
			$this->post = $post;
		}
	}
	
	private static function init(){
		if( (!isset(self::$usern)) || (!isset(self::$passwd)) || (!isset(self::$BP)) || (!isset(self::$url))){
			self::$usern  = (new MdtSharedOption('usern'))->getValue();
			self::$passwd = (new MdtSharedOption('passwd'))->getValue();
			self::$BP     = (new MdtSharedOption('BP'))->getValue();
			self::$url    = (new MdtSharedOption('url'))->getValue();
		}

		if( (!self::$usern) || (!self::$passwd) || (!self::$BP) || (!self::$url)){
			throw MdtLoginException::NoCredentialException(self::$usern, self::$passwd, self::$BP, self::$url);
		}
	}
}

?>