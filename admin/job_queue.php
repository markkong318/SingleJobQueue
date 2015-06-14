<?php
require_once 'lib/ctrl_web.php';
require_once 'admin/lib/auth.php';
require_once 'lib/Native/Models/AdminJobQueue.php';
require_once 'lib/Class/SshSwitch.php';
include 'lib/PhpSecLib/Crypt/RSA.php';
include 'lib/PhpSecLib/Net/SSH2.php';
include 'lib/PhpSecLib/Net/SFTP.php';

if(isset($_GET['b'])){
	if($_GET['page_action'] == 'get_jobs'){
		$m_queue = AdminJobQueue::getInstance();
		
		$sql = "SELECT * FROM {$m_queue->table} WHERE delete_dt IS NULL ORDER BY id DESC";
		
		$jobs = $m_queue->sql($sql);
		
		echo json_encode($jobs);
		exit;
	}else if($_GET['page_action'] == 'get_log'){
		
		$filename = $_GET['filename'];
			
		if(SshSwitch::get_ssh_name() == 'api_honban'){
			$config = SshSwitch::get_ssh_config();
			
			$key = new Crypt_RSA();
			$key->loadKey(file_get_contents(APP_ROOT_DIR."lib/SSH/Key/".$config['cron_key']));
			
			$sftp = new Net_SFTP($config['cron_server']);				
			if (!$sftp->login($config['cron_user'], $key)) {
				exit('SSH Login Failed'."\n");
			}
			
			$sftp->get($config['cron_www']."/logs/job_queue/".$filename, '/tmp/'.$filename);
			$sftp->disconnect();
			
			$filepath = '/tmp/'.$filename;
			
		}else{
			$config = SshSwitch::get_ssh_config();
			
			$key = new Crypt_RSA();
			$key->loadKey(file_get_contents(APP_ROOT_DIR."lib/SSH/Key/".$config['ssh_key']));
			
			$sftp = new Net_SFTP($config['ssh_server']);				
			if (!$sftp->login($config['ssh_user'], $key)) {
				exit('SSH Login Failed'."\n");
			}
			
			$sftp->get($config['ssh_www']."/logs/job_queue/".$filename, '/tmp/'.$filename);
			$sftp->disconnect();
			
			$filepath = '/tmp/'.$filename;

		}
		
		if(!file_exists($filepath)){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($filepath));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filepath));
		readfile($filepath);
		
		exit;
	}else if($_GET['page_action'] == 'job_model'){
		$id = $_GET['id'];
		
		if($_SERVER['REQUEST_METHOD'] == 'DELETE'){
			$m_queue = AdminJobQueue::getInstance();
			$m_queue->delete($id);
			
			echo json_encode(array());
			exit;
		}
	}else if($_GET['page_action'] == 'job_detail_model'){
		$id = $_GET['id'];
		
		if($_SERVER['REQUEST_METHOD'] == 'GET'){
			$m_queue = AdminJobQueue::getInstance();
			
			$job = $m_queue->get($id);
			
			echo json_encode($job);
			exit;
		}
	}else if($_GET['page_action'] == 'job_page_model'){
		$current_page = $_GET['currentPage'];
		$items_on_page = $_GET['itemsOnPage'];
		
		$m_queue = AdminJobQueue::getInstance();
		
		$limit_start = ($current_page - 1) * $items_on_page;
		$limit_count = $items_on_page;
		
		$sql = "SELECT * FROM {$m_queue->table} WHERE delete_dt IS NULL ORDER BY id DESC LIMIT {$limit_start}, {$limit_count}";
		$jobs = $m_queue->sql($sql);
		
		$sql = "SELECT count(*) FROM {$m_queue->table} WHERE delete_dt IS NULL ORDER BY id DESC";
		$sql_result =  $m_queue->sql($sql);
		
		$items = $sql_result[0]['count(*)'];
		
		$resp_array = array();
		$resp_array['currentPage'] = $current_page;
		$resp_array['itemsOnPage'] = $items_on_page;
		$resp_array['items'] = $items;
		$resp_array['jobs'] = $jobs;
		$resp_array['last_update']= date("Y-m-d H:i:s", strtotime("now"));
		
		echo json_encode($resp_array);
		exit;
	}
}

require_once APP_ADMIN_VIEW_DIR.basename($_SERVER["SCRIPT_NAME"]);
