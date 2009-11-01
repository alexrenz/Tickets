<?php
/**
      
      KnowWhat
      Flexible Ticket System
      
    * @author  Alexander Renz
    * @version 1.2.08
    * @module  ticket-sendmails
    
    
*/


define( 'TICKET', 'sendmails' );
define( 'USE_CLI', true );

$bckdir = getcwd( );
$curdir = chdir( dirname( __FILE__ )."/.." );

// Searches for mails with status "toWorker" in the Database and sends them


include( "includes/init.php" );

#$mailID = (int) $argv[1];

$sql = "
SELECT
  *
FROM
  tkMails
WHERE
    status='toWorker'
";

$res = mysql_query( $sql );


echo "#########################################################\n";
echo "#########################################################\n";
echo "Time: ".date( "d.m.Y - H:i" )."\n";
echo "Mails to send: ".mysql_num_rows( $res )."\n";

while( $email = mysql_fetch_assoc( $res ) )
{
  var_dump( $email );
  sendMail( $email["subject"], $email["message"], $email["to"], $email["toName"], "Dies ist eine HTML-Email...man kann sie nur mit einem HTML-Email-View ansehen..." );
  echo "\n";
  // Mark mail as read
  $sql = "
  UPDATE
    tkMails
  SET
    status = 'sent'
  WHERE
    id = ".$email["id"]."
  ";
  
  $update = mysql_query( $sql );
}




echo "\n\n\n\n";
?>
