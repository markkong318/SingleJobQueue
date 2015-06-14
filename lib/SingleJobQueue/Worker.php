#!/usr/bin/php
<?php

// ##### バッチ処理用 共通処理 ########################################
$dir = dirname($argv[0]);
if(!$dir){
	$dir = dirname(__FILE__);
}
chdir($dir);
$cwd = getcwd();
// ######################################## バッチ処理用 共通処理 #####

$ROOT_DIR = dirname(__FILE__).'/../../';

require_once $ROOT_DIR.'config/config.php';
require_once $ROOT_DIR . 'lib/SingleJobQueue/EchoTemplate.php';
require_once $ROOT_DIR.'lib/Native/Models/AdminJobQueue.php';

//output flush用
ob_end_flush();

EchoTemplate::printStartTime();

$m_job_queue = AdminJobQueue::getInstance();

//現在DBに実行してるプロセスを取得する
$running_job = $m_job_queue->get_where(array(
		'status' => AdminJobQueue::$STATUS_RUNNING,
));

if($running_job != null){
	//まだ実行中を確認する
	if(file_exists("/proc/{$running_job['pid']}")){
		//実行中です、終了
		echo "job {$running_job['job_id']} ({$running_job['pid']}) is still running, exit\n";
		
		EchoTemplate::printEndTime();
		
		exit;
	}else{
		//実行終了、DBを更新する
		echo "job {$running_job['job_id']} ({$running_job['pid']}) is done\n";
		
		$m_job_queue->set_where(array(
				'job_id' => $running_job['job_id'],
		), array(
				'status' => AdminJobQueue::$STATUS_DONE,
		));
	}
}

//次のジョブを取得する
$prepare_status = AdminJobQueue::$STATUS_PREPARE;
$now = date("Y-m-d H:i:s", strtotime("now"));
$sql = "SELECT * FROM {$m_job_queue->table} WHERE status = {$prepare_status} 
		AND (start_dt IS NULL OR start_dt < \"{$now}\") 
		AND delete_dt IS NULL
		ORDER BY id ASC
		LIMIT 1";
$sql_result = $m_job_queue->sql($sql);
$next_job = $sql_result[0];

//次のジョブがありません
if($next_job == null){
	echo "no next job, exit\n";
	EchoTemplate::printEndTime();
	exit;
}

//ログファイル名を生成
$log_file = $ROOT_DIR."../logs/job_queue/".$next_job['log_file'];

$command = "{$next_job['command']} > {$log_file} 2>&1 & echo $!";

echo "starting to run {$next_job['job_id']}\n";
echo "cmd is ".$command."\n";

$m_job_queue->set_where(array(
		'job_id' => $next_job['job_id'],
), array(
		'status' => AdminJobQueue::$STATUS_RUNNING,
));

//コマンドを実行
$pid = exec($command);

//コマンドエラー発生
if($pid == 0){
	echo "job {$next_job['job_id']} has pid 0 error, exit\n";
	
	$m_job_queue->set_where(array(
			'job_id' => $next_job['job_id'],
	), array(
			'status' => AdminJobQueue::$STATUS_PREPARE,
	));
	
	EchoTemplate::printEndTime();
	exit;
}

echo "job {$next_job['job_id']} ({$pid}) is running\n";

//pid情報をつける
$m_job_queue->set_where(array(
		'job_id' => $next_job['job_id'],
), array(
		'pid' => $pid,
));

EchoTemplate::printEndTime();

//output flush用
ob_start();