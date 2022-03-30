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
		$obj = new stdClass();
		$obj->server_time = microtime(true) * 1000;
		$obj->num = intval(shm_get_var($shm, 0x01));
		$obj->load_time = floatval(shm_get_var($shm, 0x02));
		$obj->media_type = shm_get_var($shm, 0x03);
		$obj->current_media = shm_get_var($shm, 0x04);
		$obj->show_until = floatval(shm_get_var($shm, 0x05));
		send_json($obj);
		sleep(1);
	}
?>