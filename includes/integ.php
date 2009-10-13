<?php
/**
      
      KnowWhat
      Flexible Ticket System
      
    * @author  Alexander Renz
    * @version 1.0 alpha 3
    * @module  ticket-integ
    
*/

defined( 'TICKET' ) or die( "" );


/* 
      ----------------------------
      
      Host Config (incl.Lib )
      
      ----------------------------
*/

include_once( "../inc/config.php" );


/* 
      ----------------------------
      
      Configuration
      
      ----------------------------
*/

$tkConfSysName = "KG Tickets";
$tkConfBotMail = "notify@kgs.name";

$tkConfPath    = $sitepath."tickets/";


/* 
      ----------------------------
      
      Database
      
      ----------------------------
*/

define( "PRFX", "tk_" );

// Everything else is done in the host script


/* 
      ----------------------------
      
      Callback Functions 
      (to integrate this in different environments)
      
      ----------------------------
*/

function tkCbgetUserById( $userId="" )
{
  // if userid empty, assume they want the current user
  if( $userId == "" )
    $userId = get_user_id( $_SESSION["session_username"] );
  else if( !is_numeric( $userId ) ) 
    $userId = get_user_id( $userId );
  
  
  $sql = "
  SELECT
    username, tkRightTags, email
  FROM 
    users
  WHERE
    id='".$userId."';";
  
  $res = mysql_query( $sql );
  
  if( mysql_num_rows( $res ) != 1 )
    return false;
  else
  {
    $aso = mysql_fetch_assoc( $res );
    $return = Array( "id"=>$userId, "name"=>$aso["username"], "email"=>$aso["email"], 
        "tags"=>tkTransformTagString( $aso["tkRightTags"] ) );
    return $return;
  }
  
  
}

/* 
      ----------------------------
      
      Right Management
      
      ----------------------------
*/
session_start( );

$tkUser = tkCbGetUserById( );

// Valid Login?
if( !isset( $tkUser["id"] ) || $tkUser["id"] <= 0 )
{
  $jump = basename( $_SERVER["REQUEST_URI"] );
  if( empty( $jump ) || $jump == "tickets" )
    $jump = "index.php";
  $_SESSION["tkJump"] = $jump;
  
  #die( $jump );
  Header( 'Location: '.$adminpath );
}

// Callback : User notifies
$confNotifyUsers = Array( );


$res = mysql_query( "
SELECT
  id
FROM 
  users
;" );

while( $aso = mysql_fetch_assoc( $res ) )
{
  $confNotifyUsers[] = $aso["id"];
}
  
#$confEmailNotifyUsers = $confNotifyUsers; #Array( 10, 14 );
$confEmailNotifyUsers = Array( );

?>
