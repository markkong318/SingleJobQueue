<?php

require_once 'lib/common.php';
require_once 'lib/Native/Models/AdminJobQueue.php';

class JobQueue{
	
	public static function enqueue($name, $command, $start_dt){
		$job_id = generateRandomString(32);
		
		$log_file = $job_id.'.log';
		
		$m_job_queue = AdminJobQueue::getInstance();
		
		$m_job_queue->add(array(
				'job_id' => $job_id,
				'name' => $name,
				'command' => $command,
				'start_dt' => $start_dt,
				'log_file' => $log_file,
				'status' => AdminJobQueue::$STATUS_PREPARE,
		));
	}
		
}