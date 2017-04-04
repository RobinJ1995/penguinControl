<?php
$month = idate ('m');
$day = idate ('d');
?>
@if ($month == 12 || ($month == 1 && $day < 5))
	@include ('layout.holidays.christmas')
@endif