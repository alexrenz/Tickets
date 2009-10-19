<?php
/**
      
      KnowWhat
      Flexible Ticket System
      
    * @author  Alexander Renz
    * @version 1.0 alpha 3
    * @module  felox-post
    
*/

defined( 'TICKET') or die( "" );

include_once( "classTextile.php" );
$textile = new Textile( );

class tkPost extends felox
{
  
  var $table            = "posts";
  var $confFields       = Array( "threadid", "text", "author", "build" );
  var $confStaticFields = Array( "id" );
  
  var $confSortField    = "build";
  var $confSortOrder    = "desc";
  
  
/* 
      ----------------------------
      
      Init Function
      
      ----------------------------
*/

  function tkPost( $postId )
  {
    if( $postID === 0 )
    {
      // Abstract post
    }
    else
    {
      // Fetch data, if id exists
      $res = mysql_query( "
        SELECT 
          *
        FROM
          ".PRFX."posts
        WHERE
          id='".mysql_real_escape_string( $postId )."'
        ;" );
      
      if( mysql_num_rows( $res ) === 1 )
      {
        $data = mysql_fetch_assoc( $res );
        $this->dataRaw = $data;
        
        foreach( $data as $fieldName=>$fieldVal )
        {
          $this->data[$fieldName] = $this->handleField( $fieldName, $fieldVal );
        }
        
        $this->real = TRUE;
      }
      else
      {
        return false;
      }
    }
  }
  
  
/* 
      ----------------------------
      
      Data preperation functions
      
      ----------------------------
*/  

  // Prepare Fields for screen output
  function handleField( $fieldName, $fieldVal )
  {
    global $ln_no_checkout;
    
    switch( $fieldName )
    {
      case "author":
        $user = tkCbGetUserById( $fieldVal );
        if( $user && is_array( $user ) )
          return $user["name"];
        else
        {
          tkLog( "Unkown User, User id: ".$fieldVal );
          return "";
        }
        break;
          
      case "build":
        return intelliDate( $fieldVal );
        break;
        
      case "text":
        $text = $fieldVal;
#        $text = wordwrap( $text, 150, "<br />" );
#        $text = str_replace( "\n", "<br />", $text );
//         onClick=\"return window.confirm('Bist du sicher, dass du den externen Link $1$2$3 öffnen willst?')\"

        // Important for the mail function
        $text = str_replace( '\r\n', "\r\n", $text );
        
        $text = stripslashes( $text );
        
        global $textile;
        $text = htmlspecialchars( $text );
        
        // Extract all [code] lines an check them on beginning whitespaces
        
        preg_match_all( "/\[code\]([\s\S]*)\[\/code\]/U", $text, $subpatterns );
        #var_dump ($subpatterns ); exit; 
        $codeReps = Array(); $cI=0;
        // For every [code][/code] element in the post
        foreach( $subpatterns[1] as $num=>$inner )
        {
          
          $lines = explode( "\n", $inner );
          
          foreach( $lines as $key=>$line )
          {
            if( substr( $line, 0, 1 ) != " " )
              $lines[$key] = " ".$line;
          }
          
          $code = implode( "\n", $lines );
          
          
          $text = preg_replace( "/\[code\]([\s\S]*)\[\/code\]/U", "[codework".$cI."]", $text, 1 );
          $codeReps[$cI++] = $code;
          #var_dump( $lines );
        }
        #exit;
        $text = $textile->TextileThis( $text );
        $text = preg_replace( "#(http://)(www\.)?([\w\./\-\?\/%=&;\+:\#]+)#", "<a target=\"_blank\" href=\"$1$2$3\">$1$2$3</a>", $text );
        // Special replacements
        #$replace = Array( "[codework]" => "<pre><code>", "[/codework]" => "</code></pre>" );
        foreach( $codeReps as $key => $code )
          $text = str_replace( "[codework".$key."]", "<pre><code>".$code."</code></pre>", $text );
          
        #$replace = Array( "[codework]" => "<pre><code>" );
        #$text = str_replace( array_keys( $replace ), $replace, $text );
        return $text;
      
      
      default:
        return $fieldVal;
        break;
    }
  }
  
  
  // Make field vals ready for database writing
  function prepareField( $fieldName, $fieldVal )
  {
    global $thread, $tkUser;
    switch( $fieldName )
    {
      case "threadid":
        $try = $thread->dataRaw["id"];
        
        if( is_numeric( $fieldVal ) )
          return $fieldVal;
        else if( !empty( $try ) )
          return $try;
        else return 0;
        
      case "author":
        return ( empty( $this->dataRaw["author"] ) ? $tkUser["id"] : $this->dataRaw["author"] );
      
      case "threadid2":
        if( is_numeric( $fieldVal ) ) 
          return $fieldVal;
        else if( !empty( $this->dataRaw["id"] ) )
          return $this->dataRaw["id"];
        else
          return time( );
      
      case "build":
        if( is_numeric( $fieldVal ) && $fieldVal > 0 ) 
        {
          return $fieldVal;
        }
        else if( !empty( $this->dataRaw["build"] ) )
        {
          return $this->dataRaw["build"];
        }
        else
          return time( );
          
      case "text":
        #echo "prepare the text";
        #var_dump( $fieldVal );
        
        #$test = mysql_real_escape_string( $fieldVal );
        #var_dump( $test );
        #exit;
        return mysql_real_escape_string( $fieldVal );
      
      default:
        return "";
    }
  }
   
}

?>
