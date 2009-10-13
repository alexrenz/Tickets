<?php

include_once( "includes/classTextile.php" );
$textile = new Textile( );

echo $textile->TextileThis( $_POST["previewText"] );


?>
