<?php

define( 'TICKET', 'preview' );

$bckdir = getcwd( );
$curdir = chdir( dirname( __FILE__ ) );


include( "includes/init.php" );



$post = new tkPost( 0 );


echo $post->handleField( "text", $_POST["previewText"] );


?>
