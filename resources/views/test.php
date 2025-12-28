<?php
$conn = mysqli_connect("10.9.8.95", "si_tia_24_materix", "GQrck1Fuj7I=", "si_tia_24_materix");

if (!$conn) {
    die("FAILED: " . mysqli_connect_error());
}

echo "CONNECTED!";
