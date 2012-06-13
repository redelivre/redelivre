<?php

require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/CandidatePhoto.php');

$candidatePhoto = new CandidatePhoto('photo.png');

echo '<h2>Foto</h2>';

$candidatePhoto->printHtml();
