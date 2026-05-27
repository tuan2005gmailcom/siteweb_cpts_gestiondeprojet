<?php
$hash = '$2y$12$1UBw.KhgLEvcOBtZqPJzbOJ.Oz3UKIOvkYoTI2ZV3FU.htDyjNlD2';

if (password_verify("12345678", $hash)) {
    echo "Mot de passe correct";
} else {
    echo "Mot de passe incorrect";
}
?>