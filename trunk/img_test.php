<?php
$img = imagecreate(200, 200);
// Allocation de couleurs
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
// Dessin d'un cercle noir
imagearc($img, 100, 100, 150, 150, 0, 360, $black);
// Affichage au navigateur
header("Content−type: image/png");
imagepng($img);
// Libération de la mémoire
imagedestroy($img);
?>