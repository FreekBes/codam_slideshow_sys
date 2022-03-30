<?php
	require_once("include/shm.php");

	header("Content-Type: text/event-stream");
	header("Cache-Control: no-cache");
	header("Access-Control-Allow-Origin: *");

	function send_json($json) {
		echo "id: " . (microtime(true) * 1000) . PHP_EOL . "data: ". json_encode($json) . PHP_EOL . PHP_EOL;
		ob_flush();
		flush();
	}

	while (true) {
		$obj = json_decode(json_encode(unserialize(shm_get_var($shm, 0x01))));
		if ($obj === false) {
			sleep(10);
		}
		else {
			$obj->server_time = microtime(true) * 1000;
			send_json($obj);
			usleep(100000);
		}
	}
?>