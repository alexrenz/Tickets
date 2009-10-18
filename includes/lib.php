<?php
/**
      
      KnowWhat
      Flexible Ticket System
      
    * @author  Alexander Renz
    * @version 1.0 alpha 3
    * @module  ticket-library
    
*/

defined( 'TICKET' ) or die( "" );



/* 
      ----------------------------
      
      System Functions
      
      ----------------------------
*/  

function tkSendError( $errorMsg )
{
  global $ln_error_heading, $ln_to_mainpage;
  
  tkLog( $errorMsg );
  
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
  <head>
    <title>'.$ln_error_heading.'</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
  </head>
  <body>
    <h4>'.$ln_error_heading.'</h4>
    '.$errorMsg.'
    <br />
    <br />
    <a href="'.tkMakeURL( "", "_all" ).'">'.$ln_to_mainpage.'</a>
  </body>
</html>';
  exit;
}

function tkCheckTags( $string, $userid="" )
{
  if( empty( $userid ) )
  {
    global $tkUser;
    $user = $tkUser;
  }
  else
  {
    $user = tkCbgetUserById( $userid );
  }
  
  $tags = split( ",", $string );
  $count = 0;
  foreach( $tags as $searchTag )
    if( in_array( $searchTag, $user["tags"] ) ) 
      $count++;
  
  // None of the tags was found in user tag list
    return $count;
}

function tkLog( $text )
{
  #echo $text."<br>";
  return 0;
  
  // this is not how it should be like
  
  $file = fopen( "log.txt", "a+" );
  
  
  fwrite( $file, ( date( "Y-m-d,H:i", time( ) ) ).$text."\n" );
  
  fclose( $file );
  
  return 0;
}

function tkWhereString( $argraw )
{
  $string = "";
  $and = "AND";
  
  // prevent empty elements
  $arg = Array( );
  foreach( $argraw as $el )
  {
    if( !empty( $el ) )
      $arg[] = $el;
  }
  
  foreach( $arg as $el )
  {
    if( end( $arg ) === $el )
      $and = "";
    
    $string .= "
    ".$el." ".$and;
  }
  
  return "WHERE ".$string;
}

function tkGenerateRandomString( $length, $charset = 0 )
{
  // charset
  if( $charset === 0 )
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!\"§$%&/()=?{[]}+*~#'-_";
  else
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
  
  $genmax = strlen( $chars ) - 1;
  
  srand( (double) time() );
  $str = "";
  for( $i = 0; $i < $length; $i++ )
  {
    $x = rand( 0, $genmax );
    $c = substr( $chars, $x, 1 );
    
    $str .= $c;
  }
  
  return $str;
}

function tkPrepareStringForDb( $text )
{
  $text = strip_tags( $text );
  $text = mysql_real_escape_string( $text );
  #$text = htmlentities( $text );
  /* $text = str_replace("ü", "&uuml;", $text);
  $text = str_replace("ä", "&auml;", $text);
  $text = str_replace("ö", "&ouml;", $text);
  $text = str_replace("ß", "&szlig;", $text);
  $text = str_replace("Ü", "&Uuml;", $text);
  $text = str_replace("Ä", "&Auml;", $text);
  $text = str_replace("Ö", "&Ouml;", $text);  */
  
  return $text;

}
/* 
      ----------------------------
      
      Template / Navigation Functions
      
      ----------------------------
*/

function tkGetTemplate( $tmpl )
{
/**

  @module  templating-prepare
  @version 0.8 - introduce advanced templating
  
*/  
  global $tkTmplEl;
  
  
  $assoc = Array( );
  
  foreach( $tkTmplEl as $el )
  {
    $preg = "/<<(?:\[(.+)\])?".$el.">>([\w\W]*)<<\/".$el.">>/";
    
    #echo "\n\n".$preg."\n<br><br>";
    preg_match( $preg, $tmpl, $subs );
    
    // advanced templating: interprete template
    /* if( !empty( $subs[1] ) && tkInterpreteCommand( $subs[1] ) )
    {
      // get the command
      $line = split( " ", $subs[1] );
      
      
          
    } */
    
    $assoc[$el] = $subs[2];
    
    #da( $subs );
    $tmpl = preg_replace( $preg, "<<$el>>", $tmpl );
    #highlight_string( $tmpl );
    #echo "<hr>";
  }
  
  return $assoc;
}


function tkInterpreteCommand( $command )  
{
  // get the command
  $line = split( " ", $command );
  switch( $line[0] )
  {
    case "if":
      tkInterpreteCondition( $line[1] );
      break;
  }
      
}

function tkInterpreteCondition( $c )
{
  global $confProtectVars;
  $confProtectVars = Array( );
  $hello = TRUE;
  echo "condition: ".$c."<br>";
  $c = trim( $c );
  $c = preg_replace( "/ /", "", $c );
  
  
  if( substr( $c, 0, 1 ) == "(" && substr( $c, strlen( $c ) - 1, 1 ) == ")" )
    $c = substr( $c, 1, strlen( $c ) - 2 );
  
  $and = split( "&&", $c );
  #$or = split( "\|\|", $c );
  
  if( count( $and ) > 1 )
  {
    $work = false;
    foreach( $and as $el )
    {
      if( !tkInterpreteCondition( $el ) )
        return false;
    }
  }
  else if( count( $or ) > 1 )
  {
    $work = false;
    foreach( $or as $el )
    {
      if( tkInterpreteCondition( $el ) )
        return true;
    }
  }
  else
  {
    // real interpreting of one condition
    
    // Variable
    if( preg_match( '/^\$([a-zA-Z][a-zA-Z0-9]*)$/', $c, $subs ) )
    {
      echo "interpreting variable: $c";
      #global ${$subs[1]};
      if( !in_array( $subs[1], $confProtectVars ) )
      {  echo ( ${$subs[1]} ? "TRUE" : "FALSE" )."<br>"; return ${$subs[1]}; }
      else
      {  echo  "FALSE" ."<br>";return false;  }
    }
    // Function 
    else if( preg_match( "/^\$([a-zA-Z][a-zA-Z0-9]*)(\([^\)]*\))$/", $c, $subs ) )
    {
      echo "interpreting function: $c";
      
      if( !function_exists( $subs[1] ) )
        return false;
      else
        return true;
        
    }
    else
      die( "sackgasse: $c" );
  }
}
  


function tkMakeHtml( $tmpl, &$prm )
{
/**

  @module  templating-make
  @version 0.8.1 - new allowed char in tags: ":"
           0.8   - introduce advanced templating
                 
  
*/  
  
  // Replace all the elements
  foreach( $prm as $search=>$replace )
  {
    //echo "replace $search by $replace <br>";
    $tmpl = str_replace( "<<".$search.">>", $replace, $tmpl );
  }
  
    #echo "replace $search by $replace <br>";
  
  // Replace all standard tags
  global $tmplGlobalReplacements;
  foreach( $tmplGlobalReplacements as $search=>$replace )
  {
    #echo "replace $search by $replace <br>";
    $tmpl = str_replace( "<<".$search.">>", $replace, $tmpl );
  }
  
  $tmpl = preg_replace( "/<<[\w:]+>>/", "", $tmpl );
  
  return $tmpl;
}

// makeURL( "var1=val1&var2=val2", "id,add,idiot")
function tkMakeURL( $paramstring, $remove = "", $anchor = "", $type="html" )
{
/**

  @module  linking-make
  @version 0.3 - added variables override the remove param
  
*/  
  
  // In Header Forwarding, & shouldn't be escaped - we would be stuck in id=SKEON&amp;tkSearch=here
  if( $type == "header" ) $amp = "&";
  else $amp = "&amp;";
  
  $qs = Array( );
  $rmv = split( ",", $remove );
  
  // Additional in tk
  if( $remove === "_all" )
  {
    $way = Array( $paramstring );
  }
  else
  {
    $way = Array( $_SERVER["QUERY_STRING"], $paramstring );
  }
  
  $count = 0;
  foreach( $way as $str )
  { 
      $count ++;
      $chk = split( "&", $str );
      foreach( $chk as $el )
      {
        $cache = split( "=", $el );
        //  valid param?          -    make shure it isn't on rmv   -  added params override rmv, important for standard rmv
        if( count( $cache ) === 2 && ( !in_array( $cache[0], $rmv ) || $count > count( $_SERVER["QUERY_STRING"] ) ) )
        {
          $qs[$cache[0]] = $cache[1];
        }
      }
  }
  $qf = Array( );
  foreach( $qs as $key=>$val )
  {
    $qf[] = $key."=".$val;
  }
  
  
  $url = $_SERVER["PHP_SELF"];
  if( count( $qf ) > 0 )
    $url .= "?".$qf[0];
  
  for( $x=1; $x<count( $qf ); $x++ )
    $url .= $amp.$qf[$x];
  
  // Add an anchor
  if( !empty( $anchor ) )
    $url .= "#".$anchor;
  
  return $url;
}

/* 
      ----------------------------
      
      Additional
      
      ----------------------------
*/

function da( $array )
{
  foreach( $array as $key=>$el )
  {
    if( is_array( $el ) )
      da( $el );
    else
    {
      echo "<h4>$key</h4>";
      highlight_string( $el );
      echo "<hr>";
    }
  }
}


function tkWhereArrayToStr( $array, $sep = "AND" )
{
  $sep = " ".$sep." ";
  $whereStr = "";
  foreach( $array as $clause )
  {
  if( $clause == end( $array ) )
    $sep = "";
  $whereStr .= "( ".$clause." )".$sep;
  }
  
  return $whereStr;
}



function intelliDate( $stamp )
{
  $now = time( );
  
  $days = Array(
    "Sonntag",
    "Montag",
    "Dienstag",
    "Mittwoch",
    "Donnerstag",
    "Freitag",
    "Samstag" );
    
  
  if( date( "dmY", $stamp ) === date( "dmY", $now ) )
    return date( "H:i", $stamp )."";
    
  if( date( "dmY", $stamp ) === date( "dmY", $now-60*60*24 ) )
    return "gestern"/*. date( "H:i", $stamp ) */;
  
  if( $stamp > $now-60*60*24*5 )
    return $days[date( "w", $stamp )];
    
  return date( "d.m.Y", $stamp );
  
}


function tkTransformTagString( $string )
{
  //for a string with all the tags :
  //  return split( ",", $string );
  // Nowadays, we are using a role [developer, client]
  switch( $string )
  {
    case "developer":
      return Array( "viewAll", "answerAll", "manageAll", "checkoutAll", "create", "deleteAll" );
    case "employe":
      return Array( "viewAll", "answerAll", "checkoutAll", "create", "manageOwn" );
    case "client":
      return Array( "viewOwn", "answerOwn", "manageOwn", "create", "deleteOwn" );
  }
}


function validateMail( $email )
{
  if( eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email ) )
    return true;
  else
    return false;
}

function mailLog( $to, $subject, $message, $headers )
{
  $sql = "
  INSERT INTO 
    tkMails 
    (`to`, subject, message, headers) 
  VALUES 
    ( '".mysql_real_escape_string( $to )."',
      '".mysql_real_escape_string( $subject )."',
      '".mysql_real_escape_string( $message )."',
      '".mysql_real_escape_string( $headers )."' )
  ;";
  
  $res = mysql_query( $sql );
}

/*
      ----------------------------

      Global Subscription functions

      ----------------------------
*/

  function globGetUsers( )
  {
    $sql = "
      SELECT
        id
      FROM
        users
      WHERE
        tkGlobalSubscription=1";

    $res = mysql_query( $sql );

    $globUsers = Array();
    while( $row = mysql_fetch_assoc( $res ) )
    {
      #echo "addGlobuser: ".$row["id"]."<br />";
      $globUsers[] = $row["id"];
    }

    return $globUsers;
  }

  function globStatus( $userID )
  {
    $sql = "
      SELECT
        id
      FROM
        users
      WHERE
        id=".mysql_real_escape_string( $userID )."
      AND
        tkGlobalSubscription=1
        ";
    $res = mysql_query( $sql );

    return mysql_num_rows( $res ) > 0;
  }
?>
