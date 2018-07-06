<?php 

/**
* 
*/
class MdtCron
{
	static private function findMsgFromTime($value, $style = null)
	{
		if(!$value){
			return $value;
		}
		$value = intval($value);
		$weeks = intval($value/(60*60*24*7));
		$value = $value%(60*60*24*7);
		$days = intval($value/(60*60*24));
		$value = $value%(60*60*24);
		$hours = intval($value/(60*60));
		$value = $value%(60*60);
		$minutes = intval($value/(60));
		$value = $value%(60);
		$seconds = intval($value);
		if($style === 'array'){
			return [
				'weeks' => $weeks,
				'days' => $days,
				'hours' => $hours,
				'minutes' => $minutes,
				'seconds' => $seconds
			];
		}
		$weeks = $weeks.' '.__('weeks');
		$days = $days.' '.__('days');
		$hours = $hours.' '.__('hours');
		$minutes = $minutes.' '.__('minutes');
		$seconds = $seconds.' '.__('seconds');
		$and = __('and');
		return "$weeks, $days, $hours, $minutes $and $seconds";
	}
	
	static public function registerMdtSchedule($schedule = [])
	{
		// Adds variable time to the existing schedules.
		$time = (new MdtSharedOption('schedule'))->getValue();
		$time = $time? $time : (7 * 24 * 60 * 60);
		$schedules['mdt'] = [
			'interval' => $time,
			'display' =>  self::findMsgFromTime($time)
		];
		return $schedules;
	}
	
	static public function setScheduleFrequency($value, $start = null)
	{
		(new MdtSharedOption('schedule'))->setValue($value);
		return self::setSchedule($start);
	}
	
	static public function getScheduleFrequency($pretty = null)
	{
		return $pretty? self::findMsgFromTime(self::getScheduleFrequency(), $pretty) : (new MdtSharedOption('schedule'))->getValue();
	}
	
	static public function setSchedule($start = null)
	{
		self::clearSchedule();
		wp_schedule_event( $start? $start : time(), 'mdt', 'mdt_cron' );
		return self::getNextScheduleTime();
	}
	
	static public function clearSchedule()
	{
		return wp_clear_scheduled_hook( 'mdt_cron' );
	}
	
	static public function getNextScheduleTime($pretty = null)
	{
		if(!$pretty)
			return wp_next_scheduled( 'mdt_cron' );
		$time = self::getNextScheduleTime();
		return self::findMsgFromTime($time? $time - time() : $time, $pretty);
	}
	
	static public function setOneShotSchedule($start = null)
	{
		self::clearOneShotSchedule();
		wp_schedule_single_event( $start? $start : time(), 'mdt_cron_once', ['hasarguments'=>true] );
		return self::getNextOneShotScheduleTime();
	}
	
	static public function clearOneShotSchedule()
	{
		return wp_clear_scheduled_hook( 'mdt_cron_once', ['hasarguments'=>true] );
	}
	
	static public function getNextOneShotScheduleTime($pretty = null)
	{
		if(!$pretty)
			return wp_next_scheduled( 'mdt_cron_once', ['hasarguments'=>true] );
		$time = self::getNextOneShotScheduleTime();
		return self::findMsgFromTime($time? $time - time() : $time, $pretty);
	}
	
	private function __construct($argument)
	{
		# code...
	}
}

?>