<?php
/**
      
      KnowWhat
      Flexible Ticket System
      
    * @author  Alexander Renz
    * @version 1.2.1
    * @module  ticket-calls
    
*/

define( 'TICKET', '1.2.05' );


$bckdir = getcwd( );
$curdir = chdir( dirname( __FILE__ ) );


include( "includes/init.php" );




/* 
      ----------------------------
      
      Create new Post
      
      ----------------------------
*/
if( $_POST['p'] === 'newPost' )
{
  // Create class, check if thread exists
  $thread = new tkThread( $_POST['threadid'] );
  if( !$thread->real )
    tkSendError( $ln_thread_not_found );
  
  // Check the rights
  if( tkCheckTags( "answerAll" ) 
  ||( tkCheckTags( "answerOwn" ) && $thread->dataRaw["author"] == $tkUser["id"] )
  )
    $access = true;
  else
    tkSendError( $ln_access_no_create );
  
  
  $thread->posts["create"] = new tkPost( 0 );  
    
  $inputs = Array( );
    
  $thread->posts["create"]->setParams( $_POST );
  $thread->posts["create"]->writeParams( );
  
  
  $params = Array (
    "lastchange" => time()
    );
  
  $thread->setParams( $params );
  $thread->writeParams( );
  

  // Send email notifies
  $thread->sendNotify( "onComment", "_subscribers" );
    
  
  Header( 'Location: '.tkMakeURL( "id=".$thread->data["frontid"], $confStripQueryVars, "newPost", "header" ) );
  
  
    
}


/* 
      ----------------------------
      
      Edit or Delete Post
      
      ----------------------------
*/

if( $_POST['p'] === 'editPost' || $_POST['p'] === 'deletePost' )
{
  $post = new tkPost( $_POST['mgPostId'] );
  
  if( !$post->real )
    tkSendError( $ln_post_not_found );
  
  
  // Use with real (backend) id
  $thread = new tkThread( $post->dataRaw['threadid'], TRUE );
  if( !$thread->real )
    tkSendError( $ln_thread_not_found );
  
  if( tkCheckTags( "manageAll" ) 
  ||( tkCheckTags( "manageOwn" ) && $post->dataRaw["author"] == $tkUser["id"] )
  )
    $access = true;
  else
    tkSendError( $ln_access_no_edit_post );
    
  switch( $_POST['p'] )
  {
    case 'editPost':
      $post->setParams( $_POST );
      $post->writeParams( );
      break;
   
    case 'deletePost':
      $post->drop();
      break;
  }
  
  
  /* don't actualize thread on edit or delete
  
  $params = Array (
    "lastchange" => time()
    );
  
  $thread->setParams( $params );
  $thread->writeParams( ); */
  
  
  // Send email notifies - same as above
  #$thread->sendNotify( "onComment", "_subscribers" );
    
  
  
  Header( 'Location: '.tkMakeURL( "id=".$thread->data["frontid"], $confStripQueryVars, "comment".$_POST['mgPostId'], "header"  ) );
  
  
    
}


/* 
      ----------------------------
      
      Subscribe / Unsubscribe
      
      ----------------------------
*/

if( $_POST['p'] === 'subscribe' || $_POST['p'] === 'unsubscribe' )
{
  
  $thread = new tkThread( $_POST['threadid'] );
  if( !$thread->real )
    tkSendError( $ln_thread_not_found );
  
  if( tkCheckTags( "viewAll" ) 
  )
    $access = true;
  else
    tkSendError( $ln_access_no_create );
    
    
  $inputs = Array( );
    
  $thread->subscribe( $_POST['p'] );
    
  
  
  Header( 'Location: '.tkMakeURL( "id=".$thread->data["frontid"], $confStripQueryVars, "", "header" ) );
  
  
    
}

/*
      ----------------------------

      Global Subscription

      ----------------------------
*/

if( is_numeric( $_GET['globalSubscription'] ) )
{

//   $thread = new tkThread( $_POST['threadid'] );
//   if( !$thread->real )
//     tkSendError( $ln_thread_not_found );
//
//   if( tkCheckTags( "viewAll" )
//   )
//     $access = true;
//   else
//     tkSendError( $ln_access_no_create );
//
//
//   $inputs = Array( );
//
//   $thread->subscribe( $_POST['p'] );

  $sql = "
    UPDATE
      users
    SET
      tkGlobalSubscription='".$_GET['globalSubscription']."'
    WHERE
      id='".$tkUser["id"]."';";



  $res = mysql_query( $sql );



  Header( 'Location: '.tkMakeURL( "msg=subscr-".$_GET['globalSubscription'], $confStripQueryVars.",id,globalSubscription", "", "header" ) );

exit;

}

/* 
      ----------------------------
      
      Create new Ticket - DB 
      
      ----------------------------
*/

if( $_POST['p'] === 'newTicket' )
{
  // Create the dummy class
  $thread = new tkThread( 0 );
  
  // Check the rights
  if( !tkCheckTags( "create" ) )
    tkSendError( $ln_access_no_create );
    
    
  $inputs = Array( );
    
  $thread->setParams( $_POST );
  $thread->writeParams( );
  
  $thread->posts["create"] = new tkPost( 0 );
  $thread->posts["create"]->setParams( $_POST );
  $thread->posts["create"]->writeParams( );
  
  // Notify the users who want it by displaying "new change"
  $thread->setNotify( $confNotifyUsers, 0 );
  
  // Send email notifies
  $thread->sendNotify( "onNew", "_new" );
  
  // The new user is aware of his thread, so we don't need to notify him
  $thread->setNotify( $tkUser["id"] );
  
  Header( 'Location: '.tkMakeURL( "id=".$thread->dataRaw["frontid"], $confStripQueryVars, "", "header" ) );
  
}


/* 
      ----------------------------
      
      Create new Ticket -  Form
      
      ----------------------------
*/

else if( $_GET['c'] === "new" )
{    
  $thread = new tkThread( 0 );
  
  if( !tkCheckTags( "create" ) )
    tkSendError( $ln_access_no_create );
    
  $params = Array( 
    "backlink"=>tkMakeURL( "", $confStripQueryVars )
    );
  $formHtml = tkMakeHtml( $tmpl["newthread"], $params );
  
  
  $params = Array(
    "newthread"=>$formHtml,
    "title"=>$ln_createNew
    );
    
  $html = tkMakeHtml( $tmpl["body"], $params );
  
  echo $html;
}

/* 
      ----------------------------
      
      Mark all as Read
      
      ----------------------------
*/

else if( $_GET['do'] === "markAllAsRead" )
{    
  $thread = new tkThread( 0 );
  
  $thread->requestList(  );
  
  $rowsHtml = "";
  while( $dataRaw = $thread->getListRow( ) )
  {
    #marker3
    #echo "thread. ".$dataRaw["id"]." .. ".$dataRaw["frontid"]."<br />";
    $tempThread = new tkThread( $dataRaw["frontid"], false, true );
    $tempThread->setNotify( $tkUser["id"] );
  }
  
  #exit;
  #die( tkMakeURL( "msg=allRead", $confStripQueryVars  ) );
  Header( 'Location: '.tkMakeURL( "msg=allRead", $confStripQueryVars, "", "header" ) );
  
}


/* 
      ----------------------------
      
      Edit Thread
      
      ----------------------------
*/

else if( !empty( $_POST['mgId'] ) )
{
  // thread class
  $thread = new tkThread( $_POST['mgId'] );
  
  
  if( tkCheckTags( "manageAll" )
  ||( tkCheckTags( "manageOwn" )    && $thread->dataRaw["author"] == $tkUser["id"] ) )
    $access = true;
  else
    tkSendError( $ln_no_access_to_thread );
  
  if( !$thread->real )
    tkSendError( $ln_thread_not_found );
    
  if( !is_array( $rightLimits["manage"] ) )
   $thread->setParams( $_POST );
  else
    $thread->setParams( $_POST, split( ",", $rightLimits["manage"] ) );
  
  $write = $thread->writeParams( );
  
  if( $write )
    Header( 'Location: '.tkMakeURL( "", "", "", "header" ) );
  else
    tkSendError( $ln_db_error );
}


/* 
      ----------------------------
      
      Status change (checkin/checkout)
      
      ----------------------------
*/

else if( !empty( $_POST['stausChangeId'] ) )
{
  // thread class
  $thread = new tkThread( $_POST['stausChangeId'] );
  
  if( tkCheckTags( "checkoutAll" )
  #||( tkCheckTags( "manageOwn" )    && $thread->dataRaw["author"] == $tkUser["id"] ) 
  )
    $access = true;
  else
    tkSendError( $ln_no_access_to_thread );
  
  if( !$thread->real )
    tkSendError( $ln_thread_not_found );
  
  $params = Array( 
    "status" => $_POST['newStatus'],
    "checkout" => ( $_POST['newStatus'] == 2 ? $tkUser["id"] : 0 ) );
  
  if( !is_array( $rightLimits["statuschange"] ) )
   $thread->setParams( $params );
  else
    $thread->setParams( $params, split( ",", $rightLimits["statuschange"] ) );
  
  $write = $thread->writeParams( );
  
  // Email notification
  #$thread->sendNotify( "onStatusChange", $_POST['stausChangeId'] );
  
  if( $write )
    Header( 'Location: '.tkMakeURL( "", "", "", "header" ) );
    #echo "";
  else
    tkSendError( $ln_db_error );
}


/* 
      ----------------------------
      
      Delete Thread
      
      ----------------------------
*/

else if( !empty( $_GET['d'] ) )
{
  // Initialize Thread var (also fetches posts)
  $thread = new tkThread( $_GET['d'] );
  
  // Does the thread exist ?
  if( !$thread->real )
    tkSendError( $ln_thread_not_found );
    
  // Is the user allowed to delete this thread ?
  if( tkCheckTags( "deleteAll" ) 
  ||( tkCheckTags( "deleteOwn" )    && $thread->dataRaw["author"] == $tkUser["id"] ) )
    $access = true;
  else
    tkSendError( $ln_no_access_to_thread );
  
  // Delete the posts
  foreach( $thread->posts as $post )
  {
    $post->drop( );
  }
  
  // Delete Thread
  $del = $thread->drop( );
  
  if( $del )
    Header( 'Location: '.tkMakeURL( "msg=del-".$_GET['d'], $confStripQueryVars, "", "header" ) );
  else
    echo $ln_db_error;
}


/* 
      ----------------------------
      
      Display single Ticket
      
      ----------------------------
*/

else if( isset( $_GET['id'] ) )
{
  // Initialize Thread var (also fetches posts)
  $thread = new tkThread( $_GET['id'] );
  
  // Does the thread exist ?
  if( !$thread->real )
    tkSendError( $ln_thread_not_found );
    
  // Is the user allowed to view this thread ?
  if( tkCheckTags( "viewAll" )
  ||( tkCheckTags( "viewOwn" )    && $thread->dataRaw["author"] == $tkUser["id"] )
  ||( tkCheckTags( "viewPublic" ) && $thread->dataRaw["visibility"] > 0 ) )
    $access = true;
  else
    tkSendError( $ln_no_access_to_thread );
  
    
  $lastCheck = $thread->getLastCheck( $tkUser["id"] );
  
  // Get the posts to the thread
  $postHtml = "";
  foreach( $thread->posts as $post )
  {
    // Manage Posts
    $params = $post->dataRaw;
    $params["count"] = $post->data["id"];
    
    if( tkCheckTags( "manageAll" ) 
    ||( tkCheckTags( "manageOwn" ) && $post->dataRaw["author"] == $tkUser["id"] ) )
      $manageHtml = tkMakeHtml( $tmpl["managePost"], $params );
    else
      $manageHtml = "";
      
    
    
    // Post Html
    $params = $post->data;
    
    // Search highlighting for the post text
    if( $envSearchActive )
    {
      $searchWords = explode( " ", $_GET["tkSearch"] );
      foreach( $searchWords as $searchWord ) {
        $preg = '#(?<!<)(/?'.str_replace( "#", "\#", preg_quote( $searchWord ) ).')(?!>)#i';
        #echo $preg; (?!>)
        $params["text"] = preg_replace( $preg, '<em class="highlight">'.$searchWord.'</em>', $params["text"] );
      }
      #echo "replace: ".$searchWord."<br />";
    }
    #var_dump( $searchWords );
    
    
    
    $params["count"] = $post->data["id"];
    $params["managePost"] = $manageHtml;

    // Colors for postings: Author of thread has one for himself
    // Every dev has one
    if( $post->dataRaw["author"] == $thread->dataRaw["author"] )
      $params["postType"] = "threadowner";
    
    // Emplyees
    if( tkCheckTags( "viewAll", $post->dataRaw["author"] ) )
      $params["postType"] = "employe";
      
    
    // Developer
    if( tkCheckTags( "manageAll", $post->dataRaw["author"] ) )
      $params["postType"] = "developer dev".$post->data["author"];
      
    // Is the post new for the user?
    if( $post->dataRaw["build"] > $lastCheck )
      $params["newForUser"] = "unread";
    else 
      $params["newForUser"] = "read";
    
    // How old is the thread?
    foreach( $tkPostGrouping as $groupKey=>$group )
    {
      if( time() - $post->dataRaw["build"] > $group["age"] )
      {
        $params["group"] = $groupKey;
        break;
      }
    }
      
    // Raw Text for Textile Edit 
    $params["text_orig"] = $post->dataRaw["text"];
    
    if( empty( $params["text"] ) ) $params["text"] = "&nbsp;";

    $postHtml .= tkMakeHtml( $tmpl["post"], $params );
    
  }
    
  
  $params = $thread->dataRaw;
  
    
  // Manage the _Thread_
  $thread->dataRaw["addStatusOptions"] = $thread->getStatusOptionsAsForm( $thread->dataRaw["status"] );
  $thread->dataRaw["subject"] = $thread->data["subject"]; // look here important point
  if( tkCheckTags( "manageAll" ) ) {
    $manageHtml = tkMakeHtml( $tmpl["manage"], $thread->dataRaw );
    $manageButtonHtml = tkMakeHtml( $tmpl["managebutton"], $thread->dataRaw );
    $params["deleteLink"] = tkMakeURL( "d=".$thread->dataRaw["frontid"], $confStripQueryVars );
    $deleteThreadHtml = tkMakeHtml( $tmpl["deleteThread"], $params ); }
  else
    $manageHtml = "";
  
  
  // Subscribe
  if( tkCheckTags( "viewAll" ) )
  {
    // Hint: need the $params from above
    if( $thread->searchSubscription() )
    {
      $params["call"] = "Emails deaktivieren";
      $params["action"] = "unsubscribe";
    }
    else
    {
      $params["call"] = "Emails aktivieren";
      $params["action"] = "subscribe";
    }
    $subscribeHtml = tkMakeHtml( $tmpl["subscribe"], $params );
  }
  else
    $subscribeHtml = "";
    
  // Status Highlighting
  switch( $thread->dataRaw["status"] ) {
    case 1: $st = "tkNew"; break;
    case 2: $st = "tkInwork"; break;
    case 5: $st = "tkSolved"; break; 
    }
    
  
  // Status change: checkin / checkout
  if( tkCheckTags( "checkoutAll" ) )
  {
    if( $thread->dataRaw["status"] == 2 && $thread->dataRaw["checkout"] == $tkUser["id"] )
    {
      // Already checked out
      $params = Array( "frontid"=>$thread->dataRaw["frontid"], "action_name"=>$ln_solved, "newstatus"=>5 );
      $checkoutHtml = tkMakeHtml( $tmpl["statusform"], $params );
      
      $params = Array( "frontid"=>$thread->dataRaw["frontid"], "action_name"=>$ln_not_solved, "newstatus"=>1 );
      $checkoutHtml .= tkMakeHtml( $tmpl["statusform"], $params );
    }
    else
    {
      // Usual checkout form
      $params = Array( "frontid"=>$thread->dataRaw["frontid"], "action_name"=>$ln_checkout, "newstatus"=>2 );
      $checkoutHtml .= tkMakeHtml( $tmpl["statusform"], $params );
    }
    
    /* $params = Array( "frontid"=>$thread->dataRaw["frontid"], "action_name"=>$ln_refuse, "newstatus"=>6 );
    $checkoutHtml .= tkMakeHtml( $tmpl["statusform"], $params ); */
    
    $params = Array( "statusform"=>$checkoutHtml );
    $checkoutHtml = tkMakeHtml( $tmpl["statuschange"], $params );
  }
  else
    $checkoutHtml = "";
  
  // New Post
  if( tkCheckTags( "answerAll" ) 
  ||( tkCheckTags( "answerOwn" ) && $thread->dataRaw["author"] == $tkUser["id"] )
  )
    $newpostHtml = tkMakeHtml( $tmpl["newpost"], $thread->data );
  else
    $checkoutHtml = "";
    
  
  $replace = $thread->data;
  
  #var_dump( $replace );exit;
  
    

  $replace["backlink"] = tkMakeURL( "", "id" );
  $replace["post"]     = $postHtml;
  $replace["manage"]   = $manageHtml;
  $replace["managebutton"]   = $manageButtonHtml;
  $replace["deleteThread"] = $deleteThreadHtml;
  $replace["statuschange"] = $checkoutHtml;
  $replace["newpost"]  = $newpostHtml;
  $replace["subscribe"]= $subscribeHtml;
  $replace["subscription"]=( $thread->searchSubscription() ? '<span style="color:orange">[Abonniert]</span>' : '');
  $replace["status_highlight"] = $st;
  
  //highlight_string( $tmpl["thread"] );
  
  $threadHtml = tkMakeHtml( $tmpl["thread"], $replace );
  
  
  $params = Array(
    "thread"    => $threadHtml,
    "post"      => $postHtml,
    "filterchoice"=>$tmplFilterChoice, 
    "title"     => $thread->data["subject"] 
    );
  $html = tkMakeHtml( $tmpl["body"], $params );
  
  echo $html;
  
  // Mark this thread as read for current user
  $thread->setNotify( $tkUser["id"] );
  
}


/* 
      ----------------------------
      
      Display Overview
      
      ----------------------------
*/

else
{
  // thread class
  $thread = new tkThread( 0 );
  
  $where = Array( );
  $addTables = Array( );
  
  // remote uses "unread" filter
  if( tkREMOTE == "fetchNewThreadNum" ) $_GET['f'] = 'unread';
  
  // Filters ?
  switch( $_GET['f'] )
  {
     case "solved":
      $where["solved"]  = "status>4";
      break;
    
    case "work":
      $where["work"]= "checkout='".$tkUser['id']."'";
      break;
      
    case "top":
      $where["current"]  = "status<=4";
      break;
    
    case "unread":
      $addTables[] = "tk_threads_marks";
      $where["unread_rightThread"] = "tk_threads.id = tk_threads_marks.threadid";
      $where["unread_rightUser"] = "tk_threads_marks.userid = ".$tkUser['id'];
      $where["unread_isUnread"] = "tk_threads_marks.lastcheck < tk_threads.lastchange";
      break;
    
    default:
      $where["all"]= " 1=1 ";
      break;
  }
  
  // Pagination Init
  if( is_numeric( $_GET["page"] ) ) { $curPage = round( $_GET["page"] ); $curPage += -1; if( $curPage < 0 ) $curPage = 0; }
  else $curPage = 0;
  
  $limit = "LIMIT ".( $curPage * $tkThreadsPerPage ) .",".$tkThreadsPerPage;
    
  
  // Display Current Filter
  /*if( !empty( $_GET['f'] ) )
  {
    $extractFilter = array_keys( $confFilters, $_GET['f'] );
    $currentFilter = $extractFilter[0];
  }*/
  
  // Just a special cat ?
  /*if( is_numeric( $_GET['p'] ) )
    $where["cat"] = "( parent='".$_GET['p']."' )";
  else
    $where["cat"] = "( parent=0 )";*/
    
// -------------------------- Search function begin -------------------------- //
  // Search Function. Map:
      // Search for the keywords in the Post table
      // Save each match in an array. array( "thradid" => 0/1/2 )
      // Limit the search to the found threads
  if( $envSearchActive )
  {
    $searchWords = explode( " ", $_GET["tkSearch"] );
    #var_dump( $searchWords );
    
    $words = Array( );
    foreach( $searchWords as $searchWord )
      $words[] = "`text` LIKE '%".mysql_real_escape_string( $searchWord )."%'";
    
    
    $search["words_simple"] = tkWhereArrayToStr( $words, "OR" );
    
    
    $searchWhere = tkWhereArrayToStr( $search );
    $searchPost = new tkPost( 0 );
    $searchPost->requestList( $searchWhere );
    
    // Go through matches and put them in the Array
    $matches = Array( );
    while( $dataRaw = $searchPost->getListRow( ) )
    {
      #echo "post ".$dataRaw["id"]."<br>";
      $matches[$dataRaw["threadid"]] = "`id` = '".$dataRaw["threadid"]."'";
    }
    
    if( count( $matches ) <= 0 )
      $matches[] = "1=2";
      
    // We are also looking for the thread subjects, 
    // so we join the post results with the threads by an "OR"
    $searchAll = Array(
      "posts"=>tkWhereArrayToStr( $matches, "OR" ),
      "subject"=>"`subject` LIKE '%".mysql_real_escape_string( $searchWord )."%'"
      );
    
    
    // Add our results to the Overview Request
    $where["search"] = tkWhereArrayToStr( $searchAll, "OR" );
    #echo "searchWhere: <pre>".$where["search"]."</pre><br />";
  }
  
  #var_dump( $where );

// -------------------------- Search function end -------------------------- //  

  // Access Management
  if( tkCheckTags( "viewAll" ) )
    $grant = 1;
    
  else if( tkCheckTags( "viewOwn,viewPublic" ) == 2  )
    $where["access"] = "( author='".$tkUser["id"]."' OR visibility>0 )";
  
  else if( tkCheckTags( "viewOwn" ) )
    $where["access"] = "( author='".$tkUser["id"]."' )";
  
  else if( tkCheckTags( "viewOwn,viewPublic" ) )
    $where["access"] = "( visibility=1 )";
  
  
  else
  tkSendError( $ln_no_rights );
   
   
   // Set up where string
  $whereStr = tkWhereArrayToStr( $where );
  
  
  
  // Request the list of Threads
  $thread->requestList( $whereStr, $limit, "lastchange", "DESC", $addTables );
  
  
  // How many unread threads for the logged in user?
  $newThreadNum = $thread->numListElements;
  
  #$newThreadNum = $thread->countNotifies( $where, $addTables );
  
  #echo "num".$newThreadNum;
  
  
  // EXIT POINT if the Host script just wanted to know, how many unread tickets we hav
  if( tkREMOTE != "fetchNewThreadNum" )
  {  
  
  
  $rowsHtml = "";
  while( $dataRaw = $thread->getListRow( ) )
  {
    $row = Array();
      
    // Form each row
    foreach( $dataRaw as $name=>$val )
      $row[$name] = $thread->handleField( $name, $val );
    
    // Delete-Link ?
    if( tkCheckTags( "deleteAll" ) )
    {
      $paramsDel = Array( "link"=>tkMakeURL( "d=".$row["frontid"], $confStripQueryVars ) );
      $row["deleteLink"] = tkMakeHtml( $tmpl["deleteLink"], $paramsDel );
    }
    
    if( $thread->searchSubscription( "onComment", "", $row["id"] ) )
    {
      $row["subscription"] = '<span style="color:orange">[Abo]</span>';
    }
    
    // Status Highlighting
    switch( $dataRaw["status"] ) {
      case 1: $st = "tkNew"; break;
      case 2: $st = "tkInwork"; break;
      case 5: $st = "tkSolved"; break; 
      }
    $row["status_highlight"] = $st;
    
    // Last post by
    // $row["lastpostby"] = end( $thread->posts ).data["author"];
    
    // Status Display
    /* if( $dataRaw["checkout"] > 0 )
      $row["progress"] = highlightStatus( $dataRaw["status"], "Bearbeiter: ".$row["checkout"] );
    else
      $row["progress"] = highlightStatus( $dataRaw["status"], $row["status"] ); */
    
    
    // is there new stuff in this thread ?
    #echo "<br>thread: ".$dataRaw["id"]."<br>";
    #echo $dataRaw["lastchange"]." > ".$thread->getLastCheck( $tkUser["id"], $dataRaw["id"] )."<br>";
    $lastCheckOnThread = $thread->getLastCheck( $tkUser["id"], $dataRaw["id"] );
    $unreadPosts = 0;
    $allPosts = 0;
    $firstUnreadPost = 0;
    
    // Run through all posts: Num of All Posts and Num of New Posts
    $tempThread = new tkThread( $dataRaw["frontid"] );
    foreach( $tempThread->posts as $post )
    {
      if( $post->dataRaw["build"] > $lastCheckOnThread )
      {
        if( $firstUnreadPost == 0 ) $firstUnreadPost = $post->dataRaw["id"];
        $unreadPosts++;
      }
      $allPosts++;
    }
    $row["allPosts"] = $allPosts." Post".( $allPosts == 1 ? "" : "s" ). "";
    
    if( $dataRaw["lastchange"] > $lastCheckOnThread )
    {
      $row["new"] = "unread";
      $row["unreadPosts"] = "ungelesen: ".$unreadPosts." Post".( $unreadPosts == 1 ? "" : "s" ). "";
      
      // Highlight Unread posts
      if( isset( $firstUnreadPost ) )
        $row["hilStatus"] = "unread";
        
      $row["frontid_highlight"] = 'color: #BF0D0D; " title="Dieses Ticket beinhaltet neue Beitr&auml;ge';
    }
    else if( $unreadOnly )
    {
      continue;
    }
    else
    {
      $row["new"] = "";
      $row["frontid_highlight"] = "color: black;";
    }
    
    
    $row["link"] = tkMakeURL( "id=".$row["frontid"], $confStripQueryVars, ( empty( $firstUnreadPost ) ?  "" :"comment".$firstUnreadPost ) );
  
  // Shorten the title, if too long
  #echo "limit:".$tkOverviewSubjectMaxChars;
  #var_dump($row["subject"]);
  if( strlen( $row["subject"] ) > $tkOverviewSubjectMaxChars )
    $row["subject"] = substr(  $row["subject"], 0, $tkOverviewSubjectMaxChars )."...";
    
    $rowsHtml .= tkMakeHtml( $tmpl["threadRow"], $row );
  }
  
  // No tickets here
  if( $thread->num( $thread->listRes ) <= 0 && 1==2)
  {
    $params = Array( "error" => $ln_no_threads );
    $errorHtml = tkMakeHtml( $tmpl["listError"], $params );
    $params = Array( "listError"=>$errorHtml, "title"=>$ln_overview );
    $html = tkMakeHtml( $tmpl["body"], $params );
    echo $html;
    exit;
  }
  
  // List Params
  $params = Array( "threadRow"=>$rowsHtml  );
  
  
  // Create List
  $listHtml = tkMakeHtml( $tmpl["list"], $params );
  
  // Insert the count of unread threads
  $params = Array( "count"=>$newThreadNum, "total"=>$thread->numTotalElements  );
  $countHtml = tkMakeHtml( $tmpl["newCount"], $params );
  
  // Display messages
  if( !empty( $_GET['msg'] ) )
  {
    $split = split( "-", $_GET['msg'] );
    
    switch( $split[0] )
    {
      case "new":
        $text = $ln_new_thread_created;
        break;
      
      case "del":
        unset( $split[0] );
        $textPre = implode( "-", $split );
        $text = $ln_thread_deleted."<b>#".htmlentities( $textPre, ENT_QUOTES )."</b>";
        break;
        
      case "subscr":
        if( $split[1] == 1 ) $text = $ln_globalSubscription_active;
        else $text = $ln_globalSubscription_inActive;
        break;
      
      case "allRead":
        $text = $ln_markedAllThreadsAsRead;
        break;
      
    }
    
    $msgParams = Array( "text"=>$text );
    $msgHtml = tkMakeHtml( $tmpl["message"], $msgParams );
  }
  else
    $msgHtml = "";
  
  // Display current filter
  if( !empty( $currentFilter ) )
  {
    $filterParams = Array( "filter"=>$currentFilter );
    $filterHtml = tkMakeHtml( $tmpl["currentFilter"], $filterParams );
  }
  else
    $filterHtml = "";
    
    
  // No threads here
  if( $thread->num( $thread->listRes ) <= 0 )
  {
    $errorParams = Array( "error"=>$ln_no_threads );
    $listHtml = tkMakeHtml( $tmpl["listError"], $errorParams );
  }
    
  
  // 'New thread' link
  if( tkCheckTags( "create" ) )
  {
    $linkParams = Array( "newlink"=>tkMakeURL( "c=new", $confStripQueryVars ) );
    $linkHtml = tkMakeHtml( $tmpl["newlinkcon"], $linkParams );
  }
  else
    $linkHtml = "";
    
    
  // Mail hint
  if( !validateMail( $tkUser["email"] ) )
  {
    $mailParams = Array(  );
    $mailHtml = tkMakeHtml( $tmpl["mailhint"], $mailParams );
  }
  else
    $mailHtml = "";
    
    
  // Page Management
  $numPages = ceil( $thread->numTotalElements / $tkThreadsPerPage );
  $paginationLinksHtml = "";
  #$paginationHtml .= "pages at all: $numPages <br>";
  #echo "pagelink:".$tmpl["pagelink"];
  for( $page = 0; $page < $numPages; $page++ )
  {
    #echo "page $page <br>";
    $pageParams = Array( 
      "link"=>tkMakeURL( "page=".($page+1), $confStripQueryVars ),
      "title"=>$page+1,
      "special"=>( $curPage == $page ? " currentPage" : "" ) );
    
    $paginationLinksHtml .= tkMakeHtml( $tmpl["pagelink"], $pageParams );
  }
  $params = Array( "pagelink"=>$paginationLinksHtml );
  $paginationHtml = tkMakeHtml( $tmpl["pagination"], $params );
  /*$nextO = $offset + $tkThreadsPerPage;
  $prevO = $offset - $tkThreadsPerPage;
  $pageParams = Array( "nextOffset"=>tkMakeURL( "offset=".( $nextO <= $thread->numTotalElements ? $nextO : $thread->numTotalElements ), $confStripQueryVars ),
                       "lastOffset"=>tkMakeURL( "offset=".( $prevO < 0 ? 0 : $prevO ), $confStripQueryVars ) );
  if( $offset + $tkThreadsPerPage < $thread->numTotalElements )
    $forwardHtml = tkMakeHtml( $tmpl["forwardlink"], $pageParams );
  if( $offset > 0 )
    $newerHtml = tkMakeHtml( $tmpl["newerlink"], $pageParams );*/
  
  
  // Form the body
  $params = Array( 
    "list"=>$listHtml, 
    "title"=>$ln_overview, 
    "newCount"=>$countHtml, 
    "message"=>$msgHtml, 
    "currentFilter"=>$filterHtml, 
    "filterchoice"=>$tmplFilterChoice, 
    "newlinkcon"=>$linkHtml,
    /*"forwardlink"=>$forwardHtml,
    "newerlink"=>$newerHtml,*/
    "pagination"=>$paginationHtml,
    "mailhint"=>$mailHtml
    );
  $html = tkMakeHtml( $tmpl["body"], $params );
  
  echo $html;
  
  } // remote
}


#$curdir = chdir( $$bckdir );

?>
