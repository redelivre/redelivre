<?php

require_once(TEMPLATEPATH . '/includes/graphic_material/CandidatePhoto.php');

$candidatePhoto = new CandidatePhoto('photo.png');

echo '<h2>Foto</h2>';

$candidatePhoto->printHtml();
