<?php
$cryptedPass = password_hash("123", PASSWORD_BCRYPT);

echo $cryptedPass;

if(password_verify("123",$cryptedPass))
    echo "Accesso effettuato";
else
    echo "Accesso NEGATO";
echo "<br>";
echo substr(hash("md5", uniqid()),-6);
?>