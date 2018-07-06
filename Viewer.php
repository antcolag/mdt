<?php

/**
* 
*/
class MdtViewer
{
	private $printer;
	private $settings;
	
	public function setPrinter($printer){
		$this->printer = $printer;
		return $this;
	}
	
	public function updateSettings($data){
		if (!is_array($data)) {
			$data = [$data];
		}
		foreach ($data as $key => $value) {
			$this->settings[$key] = $value;
		}
		return $this;
	}
	
	public function getSettings(){
		return $this->settings;
	}
	
	public function clearSettings(){
		$this->settings = [];
		return $this;
	}
	
	public function __construct($printer = null, $settings = [])
	{
		$printer = $printer? $printer : 'noprinter';
		$this->setPrinter($printer);
		$this->updateSettings($settings);
	}
	
	public function noprinter($arg){
		return var_export($arg, true);
	}
	
	public function json($arg){
		return json_encode($arg instanceof MdtComponent ? $arg->toHash() : $arg, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	}
	
	public function console($arg)
	{
		if(!is_array($arg)){
			$arg = array($arg);
		}
		foreach ($arg as $key => $value) {
			fwrite(STDOUT, $key."] ");
			fwrite(STDOUT, var_export($value, true)."\n");
		}
	}
	
	public function serialize($arg){
		return serialize($arg);
	}
	
	
	public function fire($arg){
		return print_r($this->{$this->printer}($arg));
	}
}