<?
/*  PHP Threads by Andrzej Wielski  */
/*    [ http://vk.com/wielski ]    */

namespace app\components\thread;

require_once('Closure.php');

Class Thread {
	private $password = 'mypassword';
	
	public function __construct(){
		if(isset($_SERVER['HTTP_PHPTHREADS']) && $_SERVER['HTTP_PHPTHREADS']){
				$closure = $_POST['PHPThreads_Run'];
				$closure = $this->strcode(base64_decode($closure), $this->password);	
				
				$session = $_POST['PHPThreads_Session'];
				$session = $this->strcode(base64_decode($session), $this->password);	
				$session = json_decode($session, true);
				
				$unserialized_closure = unserialize($closure);
				if(gettype($unserialized_closure) != 'object') return false;
				
				ob_start();
				$_SESSION = $session;
				$response = $unserialized_closure();
				$echo = ob_get_contents();
				ob_end_clean();
				
				echo json_encode(array(
					'return' => $response,
					'echo' => $echo
				));
				die();
		}
	}
	
	public function Create($func){
		if(gettype($func) != 'object'){
			echo '<!--error--><br /><b>Threads Error</b>: Thread must be a function.<br />';
			return false;
		}
		$thread =  new SuperClosure($func);
		$serialized_closure = serialize($thread);
		$this->threads[] = $serialized_closure;
	}
	
	public function Clear(){
		unset($this->threads);
	}
	
	public function Run($echo = true){
	
			if (!is_array($this->threads)) return false;
			
			$session = isset($_SESSION) ? json_encode($_SESSION) : '';
			session_write_close();
			
			//Start
			$cmh = curl_multi_init();
			$tasks = array();
			foreach ($this->threads as $i => $thread) {
				//$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
				$url = \yii\helpers\Url::to([''], true);
				$url = str_replace('http:', 'https:', $url);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['PHPThreads: true']);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, 
					'PHPThreads_Run='.urlencode(base64_encode($this->strcode($thread, $this->password))).'&PHPThreads_Session='.urlencode(base64_encode($this->strcode($session, $this->password)))
				);
				$tasks[$i] = $ch;
				curl_multi_add_handle($cmh, $ch);
			}
			
			
			$active = null;
			do {
				$mrc = curl_multi_exec($cmh, $active);
			}
			while ($mrc == CURLM_CALL_MULTI_PERFORM);
			 
			while ($active && ($mrc == CURLM_OK)) {
				if (curl_multi_select($cmh) != -1) {
					do {
						$mrc = curl_multi_exec($cmh, $active);
						$info = curl_multi_info_read($cmh);
						if ($info && $info['msg'] == CURLMSG_DONE) {
							$ch = $info['handle'];
							$url = array_search($ch, $tasks);
							
							$result = curl_multi_getcontent($ch);
							$curl_result = json_decode($result, true);
							if ($curl_result) {
								if($echo) echo $curl_result['echo'];
								$resp[$url] = $curl_result['return'];								
							} else {
								$resp[$url] = null;
							}
							
							curl_multi_remove_handle($cmh, $ch);
							curl_close($ch);
						}
					}
					while ($mrc == CURLM_CALL_MULTI_PERFORM);
				}
			}
			
			curl_multi_close($cmh);
			session_start();
			
			$this->Clear(); //Clear Threads after run
			
			if(is_array($resp)) ksort($resp);
			return $resp;
			// End
			
	}
	
	
	private function strcode($str, $passw=""){
		$salt = "DfEQn8*#^2n!9jErF";
		$len = strlen($str);
		$gamma = '';
		$n = $len>100 ? 8 : 2;
		while( strlen($gamma)<$len ){
			$gamma .= substr(pack('H*', sha1($passw.$gamma.$salt)), 0, $n);
		}
		return $str^$gamma;
	} //Encode decode string by pass
	
	
}

$Thread = new Thread();