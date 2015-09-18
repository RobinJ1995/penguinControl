<?php

function error_send_data ($subject, $message, $data)
{
	$headers = 'From: sin@sinners.be' . "\r\n" .
		   'Content-type: text/plain'. "\r\n" .
		   'CC: r0446734@student.thomasmore.be' . "\r\n";

	return mail ('sin@sinners.be', $subject, $message . PHP_EOL . PHP_EOL . 'Data:' . PHP_EOL . '-----' . PHP_EOL . print_r ($data, true), $headers);
}