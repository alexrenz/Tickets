<?php
/**
      
      KnowWhat
      Flexible Ticket System
      
    * @author  Alexander Renz
    * @version 1.2.1
    * @module  ticket-init
    
*/

defined( 'TICKET' ) or die( "" );

header("Content-Type: text/html; charset=utf-8"); 



// Magic Quotes Handling
function stripslashes_nested($v)
{
  if (is_array($v)) {
    return array_map('stripslashes_nested', $v);
  } else {
    return stripslashes($v);
  }
}

if (get_magic_quotes_gpc()) 
{
  $_GET = stripslashes_nested($_GET);
  $_POST = stripslashes_nested($_POST);
  $_COOKIES = stripslashes_nested($_COOKIES);
}

/* 
      ----------------------------
      
      Function Library
      
      ----------------------------
*/

include( "includes/lib.php" );


/* 
      ----------------------------
      
      Settings
      
      ----------------------------
*/

$confStripQueryVars = "d,c,msg,id,offset,do,x,y";
define( 'uStrip', $confStripQueryVars );

$confFilters = Array( 
  "Gel&ouml;st"=>"solved",
  "Workdesk"=>"work",
  "Aktuell"=>"top",
  "Alle"=>""
  );
  
/* 
      ----------------------------
      
      Language
      
      ----------------------------
*/

$ln                     = "de";
$ln_thread_not_found    = "Die angegebene ID ist ung&uuml;ltig.";
$ln_post_not_found      = $ln_thread_not_found;
$ln_overview            = "Ticket-&Uuml;bersicht";
$ln_checkout            = "working employe: ";
$ln_no_checkout         = "--";
$ln_no_access_to_thread = "Sie haben keinen Zugriff auf dieses Ticket.";
$ln_no_login            = "Sie m&uuml;ssen sich einloggen, um diesen Bereich einzusehen.";
$ln_error_heading       = "Es ist ein Fehler aufgetreten";
$ln_to_mainpage         = "Zur Startseite";
$ln_no_threads          = "Keine Tickets gefunden.";
$ln_createNew           = "Neues Ticket";
$ln_db_update_error     = "Es ist ein Fehler beim Aktualisieren der Datenbank aufgetreten. Die Aktion kann nicht fortgef&uuml;hrt werden.";
$ln_db_error            = "Es ist ein technischer Fehler aufgetreten";
$ln_access_no_create    = "Sie d&uuml;rfen keine Tickets erstellen.";
$ln_solved              = "Gel&ouml;st";
$ln_not_solved          = "Nicht gel&ouml;st";
$ln_checkout            = "Developer: Checkout";
$ln_new_thread_created  = "Das Ticket wurde erstellt.";
$ln_thread_deleted      = "Folgendes Ticket wurde gel&ouml;scht: ";
$ln_refuse              = "Zur&uuml;ckweisen";
$ln_no_rights           = "F&uuml;r Sie gibt es hier keinerlei Tickets.";
$ln_invalid_mail        = "Sie haben keine g&uuml;ltige Mailadresse angegeben.";
$ln_eNotify_titles      = Array( "onStatusChange"=>"Status aktualisisert", "onComment"=>"Neuer Kommentar erstellt", "onNew"=>"Neues Ticket erstellt" );
$tkConfNotMailTmpl      = "wrong var";
$ln_access_no_edit_post = "Es ist Ihnen nicht erlaubt, diesen Post zu bearbeiten.";
$ln_globalSubscription_active = "Globale Email-Benachrichtigungen sind jetzt atkviert. Das bedeutet, dass Sie nun bei jedem neuen Ereignis eine Email erhalten.";
$ln_globalSubscription_inActive = "Globale Email-Benachrightigungen sind jetzt deaktiviert. Sie erhalten nun also nur noch Emails in von Ihnen abonnierten Themen.";
$ln_globalSubscription_makeActive = "Globale Email-Benachrichtigungen sind nicht aktiviert. Klicken Sie hier, um sie zu aktivieren.";
$ln_globalSubscription_makeInActive = "Globale Email-Benachrichtigugen sind aktiviert. Klicken Sie hier, um sie auszuschalten.";
$ln_markedAllThreadsAsRead = "Alle Tickets als gelesen markiert.";
$ln_searchValueStandard =  "Suche..";
/* 
      ----------------------------
      
      Integration Commands
      
      ----------------------------
*/

include( "includes/integ.php" );



// delimit rights
$rightLimits = Array( 
  "statuschange" => "status,checkout"
  ); 







/* 
      ----------------------------
      
      Templating
      
      ----------------------------
*/

// inner 
// ...
// outer

$tkTmplEl = Array( 
    "newlinkcon",
    "forwardlink",
    "mailhint",
    "newerlink",
    "pagelink",
    "pagination",
    "newCount", 
    "message", 
    "currentFilter", 
    "managePost", 
    "post", 
    "subscribe",
    "newpost", 
    "manage", 
    "managebutton",
    "deleteThread",
    "statusform", 
    "statuschange", 
    "thread", 
    "deleteLink", 
    "threadRow", 
    "listError", 
    "list", 
    "newthread", 
    "navlink", 
    "filterchoice", 
    "header", 
    "email:newComment", 
    "email:statusChange", 
    "email:newThread", 
    "body" );

Header("Content-Type: text/html; charset=UTF-8");

$tkTmpl = file_get_contents( "includes/tkTmpl.tpl" );

$tmpl = tkGetTemplate( $tkTmpl );

$confProtectVars = Array( );

  
$tmplFilterChoice = "";
foreach( $confFilters as $name=>$filter )
{
  $split = split( "::", $filter );
  if( count( $split ) > 1 && !tkCheckTags( $split[1] ) )
    continue;
  else
    $filter = $split[0];
  
  if( !empty( $filter ) )
    $link = tkMakeURL( "f=".$filter, uStrip.",f" );
  else
    $link = tkMakeURL( "", uStrip.",f" );
  
  if( $_GET["f"] == $filter ) $current = " current";
  else $current = "";
  if( $filter == "" ) $filter="aktuell";
  $tmplFilterChoice .= '<a href="'.$link.'" title="'.$name.'" class="filterButton '.$filter.$current.'">'.$name.'</a>&nbsp;&nbsp;&nbsp;';
}

$topmenuHtml = getTopAdminMenuHtml();

if( globStatus( $tkUser["id"] ) )
  $globStatusHtml =  ' <a title="'.$ln_globalSubscription_makeInActive.'" href="'.tkMakeUrl( "globalSubscription=0", "default" ).'" style="color: orange; font-size:16px">aktiv</a>';
else
  $globStatusHtml = ' <a title="'.$ln_globalSubscription_makeActive.'" href="'.tkMakeUrl( "globalSubscription=1", "default" ).'" style="color: #D9D9D9; font-size:16px">inaktiv</a>';




#var_dump( $_POST );


$tkPostGrouping = Array(
  "4months" => Array( 
      "title" => "4 Monate",
      "age"   => 4 * 30 * 24 * 60 * 60,
      "show"  => false ),
  "1month" => Array(
    "title" => "1 Monat",
    "age"   => 1 * 30 * 24 * 60 * 60,
    "show"  => false ),
  "2weeks" => Array(
    "title" => "2 Wochen",
    "age"   => 14 * 24 * 60 * 60,
    "show"  => false ),
  "1week" => Array(
    "title" => "1 Woche",
    "age"   => 7 * 24 * 60 * 60,
    "show"  => false )

);

$gJs = "var hiddenGroups = new Array();";
$i=0;
foreach( $tkPostGrouping as $groupKey=>$group )
  $gJs .= "
  hiddenGroups[".$i++."]=new Array('".$groupKey."', '".$group["title"]."');";
  

// Search Button (Start / Stop)
if( isset( $_GET["tkSearch"] ) && !empty( $_GET["tkSearch"] ) && strlen( $_GET["tkSearch"] ) > 1 )
{
  // ------------ Official: Search is active:
  $envSearchActive = true;
  
  
  
  $searchValue = htmlspecialchars( $_GET["tkSearch"] );
  
  $searchButton = '<a href="'.tkMakeURL( "", $confStripQueryVars.",tkSearch,page"  ).'"><img src="img/stop_search.png" alt="Suche beenden" title="Die Suche beenden" /style="vertical-align:middle"></a>';
}
else
{
  $searchValue = $ln_searchValueStandard;
#  $searchButton = '<input type="image" src="img/start_search.png" alt="Suche starten" title="Suche starten" style="vertical-align:middle"/>';
  $searchButton = "";
  if( isset( $_GET['id'] ) ) $searchButton .= '<input type="hidden" name="id" value="'.$_GET["id"].'" />';
}

  


$tmplGlobalReplacements = Array( 
  "currentUserName" => $tkUser["name"], 
  "topAdminMenu" => $topmenuHtml, 
  "globStatus"=>$globStatusHtml, 
  "overviewURL"=>tkMakeURL( "", $confStripQueryVars ),
  "backURL" => ( empty( $_GET["id"] ) ? "../admin/index.php?login=1" : tkMakeURL( "", $confStripQueryVars ) ),
  "ticketURL"=>( empty( $_GET["id"] ) ? tkMakeURL( "", $confStripQueryVars.",page" ) : tkMakeURL( "id=".$_GET["id"], $confStripQueryVars ) ),
  "version"=>TICKET, 
  "newlink"=>tkMakeURL( "c=new", $confStripQueryVars ), 
  "searchValue"=> $searchValue, 
  "standardSearchValue"=>$ln_searchValueStandard,
  "searchActive"=>( $envSearchActive ? " searchActive" : " searchPassive" ),
  "searchURL"=>tkMakeURL( "" ),
  "searchButton"=>"&nbsp;&nbsp;".$searchButton,
  "searchKeepFilter"=>$_GET["f"],
  "filterButtons"=>$tmplFilterChoice,
  "groupJs"=>$gJs
  //"stopSearch"=>tkMakeURL( "", "tkSearch"  ),
  );

// Page Management
$tkThreadsPerPage = 15;

$tkOverviewSubjectMaxChars = 50;
      

/* 
      ----------------------------
      
      Core Engine
      
      ----------------------------
*/

// Abstraction Engine
include( "includes/felox.php" );

// Threads
include( "includes/tkThread.php" );

// Posts
include( "includes/tkPost.php" );

?>
