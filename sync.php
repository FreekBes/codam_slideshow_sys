<?php
	require_once("include/shm.php");

	header("Content-Type: text/event-stream");
	header("Cache-Control: no-cache");

	function send_json($json) {
		echo "id: " . time() . PHP_EOL . "data: ". json_encode($json) . PHP_EOL . PHP_EOL;
		ob_flush();
		flush();
	}

	while (true) {
		$media_num = shm_get_var($shm, 0x01);
		$last_time = shm_get_var($shm, 0x02);
		$obj = new stdClass();
		$obj->num = intval($media_num);
		$obj->load_time = intval($last_time);
		$obj->server_time = time();
		send_json($obj);
		sleep(1);
	}
?>