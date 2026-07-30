<?php
$month = idate ('m');
$day = idate ('d');
?>
{{-- Off unless penguin.holiday_theme is on. This used to fire unconditionally, so every
     install inherited a northern-hemisphere Christmas and a snowfall effect in December --}}
@if (config ('penguin.holiday_theme') && ($month == 12 || ($month == 1 && $day < 5)))
	@include ('layout.holidays.christmas')
@endif
