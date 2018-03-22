<?php
/* @var $EM_Event EM_Event */
$people = array();
$EM_Bookings = $EM_Event->get_bookings();
if( count($EM_Bookings->bookings) > 0 ){
	?>
	<ul class="event-attendees">
	<?php
	foreach( $EM_Bookings as $EM_Booking){ /* @var $EM_Booking EM_Booking */
		if($EM_Booking->booking_status == 1 && !in_array($EM_Booking->get_person()->ID, $people) ){
			$people[] = $EM_Booking->get_person()->ID;
			echo '<li>'. get_avatar($EM_Booking->get_person()->ID, 50) .'</li>';
		}elseif($EM_Booking->booking_status == 1 && $EM_Booking->is_no_user() ){
			echo '<li>'. get_avatar($EM_Booking->get_person()->ID, 50) .'</li>';
		}
	}
	?>
	</ul>
	<?php
}