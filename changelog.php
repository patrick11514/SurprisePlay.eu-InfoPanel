<?php

/**
 * Changelog
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changelog - InfoPanel</title>
</head>
<body>
    <h3>Changelog</h3>
    <pre>
<?php
$c = file_get_contents("https://proxy.patrikmin.tech/surpriseplay.eu/infopanel/changelog.txt");
if (!empty($c)) {
    $f = fopen("changelog", "w");
    fwrite($f, $c);
    fclose($f);
    echo $c;
} else {
    if (file_exists("changelog")) {
        echo file_get_contents("changelog");
    } else {
        echo "Changelog byl smazán!";
    }
}
?>
    </pre>
</body>
</html>