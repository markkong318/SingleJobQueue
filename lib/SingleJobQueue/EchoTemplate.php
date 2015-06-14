<?php
class EchoTemplate{
	
	public static function printStartTime(){
		$now = date("Y-m-d H:i:s", strtotime("now"));
		echo("----------start time: ".$now."----------\n");
	}
	
	public static function printEndTime(){
		$now = date("Y-m-d H:i:s", strtotime("now"));
		echo("----------end time: ".$now."----------\n");
	}
}