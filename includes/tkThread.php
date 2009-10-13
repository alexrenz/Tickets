<?php
/**
      
      KnowWhat
      Flexible Ticket System
      
    * @author  Alexander Renz
    * @version 1.0 alpha 3
    * @module  felox-thread
    
*/

defined( 'TICKET' ) or die( "" );

class tkThread extends felox
{

  var $posts            = Array( );
  var $confFields       = Array( "frontid", "subject", "author", "status", "checkout", "visibility", "email", "build", "lastchange", "parent" );
  var $confStaticFields = Array( "frontid", "id" );
  var $table            = "threads";
  
  
  var $statusValues = Array(  "unbekannt", "neu", "in Arb.", "undef.", "langfristig", "gel&ouml;st", "zur&uuml;ckg." );
  
  var $confSortField    = "lastchange";
  var $confSortOrder    = "desc";
/* 
      ----------------------------
      
      Init Function
      
      ----------------------------
*/
  
  // Init the thread, fetch posts
  function tkThread( $frontId, $useBackendId=FALSE, $noPosts=FALSE )
  {
    if( $frontId === 0 )
    {
      // Create abstract class (for list, create etc)
    }
    else
    {
      // limit the access
      $whereA = Array( $whereAccess );
      if( $useBackendId )
        $whereA[] = "id='".mysql_real_escape_string( $frontId )."'";
      else
        $whereA[] = "frontid='".mysql_real_escape_string( $frontId )."'";
        
      $where = tkWhereString( $whereA );
      
      // Fetch data, if id exists
      $res = $this->query( "
        SELECT 
          *
        FROM
          ".PRFX."threads
        ".$where."
        ;" );
      // Valid result
      if( $this->num( $res ) === 1 )
      {
        $data = $this->assoc( $res );
        $this->dataRaw = $data;
        
        // Prepare the field values, plain was saved in dataRaw
        foreach( $data as $fieldName=>$fieldVal )
        {
          $this->data[$fieldName] = $this->handleField( $fieldName, $fieldVal );
        }
        
        // Important Variable, later decides whether we need update or insert
        $this->real = true;
        
        if( !$noPosts )
        {
        
          // Fetch posts for thread
          $sql = "
          SELECT 
            id
          FROM
            ".PRFX."posts
          WHERE
            threadid=".$data["id"]."
          ORDER BY 
            build ASC
          ;";
          $postres = $this->query( $sql );
          
          
          // Init posts, if there are some
          if( $this->num( $postres ) > 0 )
          {
            while( $post = $this->assoc( $postres ) )
            {
              $cache = new tkPost( $post["id"] );
              if( $cache ) 
                $this->posts[] = $cache;
            }
          }
        }
        else
        {
          $this->posts = Array();
        }
        
      }
      else
      {
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
      
      
      case "checkout":
        $user = tkCbGetUserById( $fieldVal );
        if( $user && is_array( $user ) )
          return $ln_checkout.$user["name"];
        else
        {
          return $ln_no_checkout;
        }
        break;
      
      case "status":
        if( !empty( $this->statusValues[$fieldVal] ) )
          return $this->highlightStatusVal( $fieldVal, $this->statusValues[$fieldVal] );
        else
          return "unkown";
        break;
      
      case "subject":
        return htmlspecialchars( $fieldVal );
        break;
        
        
      case "build":
      case "lastchange":
        return intelliDate( $fieldVal );
        break;
      
      
      default:
        return $fieldVal;
        break;
    }
  }
  
  
  // Make field vals ready for database writing
  function prepareField( $fieldName, $fieldVal )
  {
    global $tkUser;
    
    
    
    switch( $fieldName )
    {
      case "frontid":
        if( $this->real )
          return $this->dataRaw["frontid"];
          
          do {
          $try = tkGenerateRandomString( 5, 1 );
          } while( $this->checkFrontidExistance ( $try ) > 0 );
        
        return $try;
      
      case "author":
        return ( empty( $this->dataRaw["author"] ) ? $tkUser["id"] : $this->dataRaw["author"] );
        
      case "status":
        if( is_numeric( $fieldVal ) ) 
          return $fieldVal;
        else if( !empty( $this->dataRaw["status"] ) )
          return $this->dataRaw["status"];
        else
          return 1;
      
      case "checkout":
        if( is_numeric( $fieldVal ) ) 
          return $fieldVal;
        else if( !empty( $this->dataRaw["checkout"] ) )
          return $this->dataRaw["checkout"];
        else
          return 0;
      
      case "visibility":
        return 0;
      
      case "email":
        if( $fieldVal )
          return 1;
        else
          return 0;
      
      case "build":
        if( is_numeric( $fieldVal ) ) 
          return $fieldVal;
        else if( !empty( $this->dataRaw["create"] ) )
          return $this->dataRaw["create"];
        else
          return time( );
          
      case "lastchange":
        if( is_numeric( $fieldVal ) ) 
          return $fieldVal;
        else if( !empty( $this->dataRaw["lastchange"] ) )
          return $this->dataRaw["lastchange"];
        else
          return time( );
      
      case "subject":
        if( empty( $fieldVal ) )
          return mysql_real_escape_string( $this->dataRaw["subject"] );
        else
          return mysql_real_escape_string( $fieldVal );
        
      
      default:
        return "";
    }
  }
  
  
  // Convert the integer Status Value to a string
  function highlightStatusVal( $status, $string )
  {
    switch( $status )
    {
      case 2:
        $a = "color:#2BBF0D; font-size:9px;";
        break;
      
      case 1:
        #$a = "color: #2BBF0D";
        break;
      
      case 4:
        $a = "color: #A53F22";
        break;
      
    }
    
    return ''.$string.'';
  }
    

  
/* 
      ----------------------------
      
      Notify System Functions
      
      ----------------------------
*/  

  // Make the DB entries that notify the users about new threads
  function setNotify( $users, $notify = "", $email_notify = "" )
  {
    if( !is_array( $users ) )
      $users = split( ",", $users );
    
    // Notify is manually set to 0 when creating new threads
    if( $notify === "" )
      $notify = time( );
    
    if( $email_notify == "" )
      $email_notify = 0;
    
    
    foreach( $users as $user )
    {
      // don't create duplicate entries
      $res = $this->query( "
      SELECT 
        id
      FROM
      ".PRFX."threads_marks
      WHERE
        threadid='".$this->dataRaw["id"]."' AND userid='".$user."'; " );
      
      if( $this->num( $res ) > 0 )
      {
        // just update the set
        $sql = "
        UPDATE
          ".PRFX."threads_marks
        SET
          lastcheck='".$notify."',
          email_last_notify='".$email_notify."'
        WHERE
          threadid='".$this->dataRaw["id"]."' AND userid='".$user."'; ";
      }
      else
      { 
        // create new set
        $sql = "
        INSERT INTO
          ".PRFX."threads_marks
          ( threadid, userid, lastcheck, email_last_notify )
        VALUES
          ( '".$this->dataRaw["id"]."', '".$user."', '".$notify."', '".$email_notify."' ) ;";
      }
      
      $res = $this->query( $sql );
      
    }
    
    return true;
  }
  
  function sendNotify( $cause, $type )
  {
    global $tmpl, $tkConfBotMail, $tkConfSysName, $ln_eNotify_titles, $tkUser;
    
    
    // Get all users who need to be identified
    $users = Array( );
    
    // First option - all users who subscribed to the current thread
    /*if( $type == "_subscribers" )#$this->checkFrontidExistance( $type ) )
    {
      // Get the ids of the users who described to this thread
      $sql = "
      SELECT
        id, userid, email_last_notify
      FROM
       ".PRFX."threads_marks
      WHERE
        threadid='".$this->dataRaw["id"]."' AND
        email_notification LIKE '%".$cause."%'
      ;";
      
      
      
      $res = $this->query( $sql );
    
      while( $row = $this->assoc( $res ) )
      {
        // don't spam the users
        if( time() > $row["email_last_notify"] + 60 * 60 * 24 * 7 ) // maximum is: once a week
          $users[] = $row["userid"];
      }
      
    }
    
    // Second Option - all users, who want to be notified when there is a new thread
    else if( $type == "_new" )
    {
      global $confEmailNotifyUsers;
      
      $users = $confEmailNotifyUsers;
    }*/

    // add the Global Subscription Users
    #$users = Array();
    
    // We are just notifying users who want it.
    $users = globGetUsers( );
    #foreach ( $globUser as $userid )
    #{
     # if( !in_array( $userid, $users ) )
    #   $users[] = $userid;
    #}
    
    
    // Give the users for debug reasons
    #var_dump( $users );
    /*foreach( $users as $userid )
    {
      echo "-------------<br />";
      $user = tkCbGetUserById( $userid );
      echo $user["name"];
      echo " -".$user["email"]."-<br />";
    
    }
    exit;*/
    
    // Now it's time to notify the users
    foreach( $users as $userid )
    {
      // get the details for each user
      $user = tkCbGetUserById( $userid );
      
     
      // check the users mail
      if( !validateMail( $user["email"] ) )
      {
        tkLog( "bad mail for user \"".$user["name"]."\": ".$user["email"] );
        continue;
      }
      
      // Is the user even allowed to view this thread?
      if( tkCheckTags( "viewAll", $userid )
      ||( tkCheckTags( "viewOwn", $userid )    && $this->dataRaw["author"] == $userid )
       )
        $access = true;
      else
        continue;
      
      // only send the mail, if the user checked the rhead since last mail
      //   (meaning, if the email_last_notify column in threads_marks != 0, then no mail)
      $marks = $this->getMarks( $user["id"] );
      if( $marks["email_last_notify"] > 0 ) continue;
      #marker3
      
      // send a notification mail
      switch( $cause )
      {
        case "onComment":
          $eTmpl = $tmpl["email:newComment"];
          
          #$content = $this->posts["create"]->handleField( "text", $this->posts["create"]->dataRaw["text"] );
          $content = $this->posts["create"]->dataRaw["text"];
          $author = $this->posts["create"]->handleField( "author", $this->posts["create"]->dataRaw["author"] );
          break;
        case "onStatusChange":
          $eTmpl = $tmpl["email:statusChange"];
          break;
        case "onNew":
          $eTmpl = $tmpl["email:newThread"];
          
          #$content = $this->posts["create"]->handleField( "text", $this->posts["create"]->dataRaw["text"] );
          $content = $this->posts["create"]->dataRaw["text"];
          $author = $this->handleField( "author", $this->dataRaw["author"] );
          break;
        default:
          tkSendError( "System Error: unkown notify status" );
          break;
          
      }
      
      
      $lastCheckOnThread = $this->getLastCheck( $tkUser["id"], $dataRaw["id"] );
      $unreadPosts = 0;
      $allPosts = 0;
      // First unread post
      foreach( $this->posts as $post )
      {
        if( $post->dataRaw["build"] > $lastCheckOnThread )
        {
          if( !isset( $firstUnreadPost ) ) $firstUnreadPost = $post->dataRaw["id"];
          $unreadPosts++;
        }
        $allPosts++;
      }
      
      
      
      $to      = $user["email"];
      $subjval = $this->handleField( "subject", $this->dataRaw["subject"] );
      $subject = "[".$tkConfSysName."]".' \''.$subjval.'\': '.$ln_eNotify_titles[$cause];
      
      global $tkConfPath;
      
      // No Br in Mails
      $text = $content;
      #$text = str_replace( "\n", "<br />", $text );
      $text = stripslashes( $text );
      $content = $text;
      
      $params = Array( 
        "username"=>$user["name"],
        "author"=>$author,
        "subject"=>$subjval,
        "status"=>$this->dataRaw["status"],
        "content"=>$content,
        "link"=>$tkConfPath."index.php?id=".$this->dataRaw["frontid"]."&p=".$firstUnreadPost."#comment".$firstUnreadPost,
        "frontid"=>$this->dataRaw["frontid"] );
      
      $message = $tkConfNotMailTmpl;
      $message = tkMakeHtml( $eTmpl, $params );
      
      $headers = 'From: KG-Ticket-Notify <'.$tkConfBotMail . ">\r\n" .
          'Reply-To: KG-Ticket-Notify <'.$tkConfBotMail . ">\r\n" . 
          "MIME-Version: 1.0" . "\r\n";
          "Content-type: text/html; charset=ISO-8859-1" ;
          "\n\n" ;
      
      $trans_table = array (
        'ä' => 'ae',
        'ö' => 'oe',
        'ü' => 'ue',
        'Ä' => 'Ae',
        'Ö' => 'Oe',
        'Ü' => 'Ue'
      );
      $subject = str_replace( array_keys( $trans_table), $trans_table, $subject );
      
      
      // don't notify the user himself
      if( $user["id"] != $tkUser["id"] )
      {
        
        // Send the notification mail
        mailLog( $to, $subject, $message, $headers );
        $x = mail( $to, $subject, $message, $headers );
        #echo "sendmail";
         /*echo "<pre>"."Send Mail
To: $to; 
Subject: $subject; 
Message: $message"."</pre>";*/
        #exit; 
        
       
      }
      
      
      // prevent spam ( also at creation of ticket )
      $sql = "
      UPDATE 
        ".PRFX."threads_marks
      SET
        email_last_notify='".time()."'
      WHERE
        threadid='".$this->dataRaw["id"]."' AND
        userid='".$user["id"]."'
    ;";
      $this->query( $sql );
    }
    return 0;
    #exit;
    
  }
          
      
  
  
  // Get the last view of a user of a thread (value is this thread)
  function getLastCheck( $userid, $threadid = "" )
  {
    $cache = $this->getMarks( $userid, $threadid );
    return $cache["lastcheck"];
  }
  
  // Get the row with the marks of one user
  function getMarks( $userid, $threadid = "" )
  {
    #echo "user.".$userid."  thread.".$threadid."<br>";
    // If we don't have a threadid, assume they mean this thread and return it
    if( $threadid == "" )
      $threadid = $this->dataRaw["id"];
    
    // Fetch the timestamp of the last visit
    $sql = "
      SELECT 
        lastcheck, email_notification, email_last_notify
      FROM
      ".PRFX."threads_marks
      WHERE
        threadid='".$threadid."' AND userid='".$userid."'; ";
    $res = $this->query( $sql );
    #echo $sql;
    if( $this->num( $res ) > 0 )
    {
      $assoc = $this->assoc( $res );
      return $assoc;
    }
    else
    { 
      return 0;
    }
  }
  
  function countNotifies( $addWhere = "", $user = "" )
  {
    if( $user == "" )
    {
      global $tkUser;
      $user = $tkUser;
    }
    if( !empty( $addWhere ) )
      $where = tkWhereString( $addWhere );
      
    else
      $where = "";
    
    #echo $where;
      
    $sql = "
    SELECT 
      id, lastchange
    FROM
      ".PRFX."threads
    ".$where.";";
    
    #echo $sql."<br>";
    
    $res = $this->query( $sql );
    
    $count = 0;
    
    while( $aso = $this->assoc( $res ) )
    {
      #echo "lastchange: ".$aso["lastchange"]." lastcheck:".$this->getLastCheck( $user["id"], $aso["id"] )."<br>";
      if( $aso["lastchange"] > $this->getLastCheck( $user["id"], $aso["id"] ) )
        $count ++;
    }
    
    return $count;
  }
  
  function subscribe( $action, $user = "" )
  {
    if( $user == "" )
    {
      global $tkUser;
      $user = $tkUser;
    }
    
    $content = "";
    if( $action == "subscribe" )
      $content = "onComment, onStatusChange";
    else 
      $content = "";
    
    // don't create duplicate entries
      $res = $this->query( "
      SELECT 
        id
      FROM
      ".PRFX."threads_marks
      WHERE
        threadid='".$this->dataRaw["id"]."' AND userid='".$user["id"]."'; " );
      
      if( $this->num( $res ) > 0 )
      {
        // just update the set
        $sql = "
        UPDATE
          ".PRFX."threads_marks
        SET
          email_notification='".$content."'
        WHERE
          threadid='".$this->dataRaw["id"]."' AND userid='".$user["id"]."'; ";
      }
      else
      { 
        // create new set
        $sql = "
        INSERT INTO
          ".PRFX."threads_marks
          ( threadid, userid, email_notification )
        VALUES
          ( '".$this->dataRaw["id"]."', '".$user["id"]."', '".$content."' ) ;";
      }
      
      
      mysql_query( $sql );
      
  }
  
  function searchSubscription( $search = "onComment", $user = "", $threadid = "" )
  {
    if( $user == "" )
    {
      global $tkUser;
      $user = $tkUser;
    }
    if( $threadid == "" )
      $threadid = $this->dataRaw["id"];
    
    $sql = "
      SELECT 
        id, email_notification
      FROM
      ".PRFX."threads_marks
      WHERE
        threadid='".$threadid."' AND 
        userid='".$user["id"]."' AND
        email_notification LIKE '%".$search."%'; ";
    
    $res = mysql_query( $sql );
    
    if( mysql_num_rows( $res ) > 0 )
      return true;
    else
      return false;
  }
    
     
      
    
    
 
    
  
/* 
      ----------------------------
      
      Stuff
      
      ----------------------------
*/  

  function getStatusOptionsAsForm( $value="" )
  {
    $return = "";
    foreach( $this->statusValues as $key=>$val )
    {
      $return .= '
      <option value="'.$key.'" '.( $value == $key ? ' selected="selected"' : '' ).'>'.$val.'</option>';
    }
    return $return;
  }
    
}



?>
