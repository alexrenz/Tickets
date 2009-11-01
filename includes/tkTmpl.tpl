<<body>><!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
  <head>
    <title><<title>> - KG-Tickets</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <script src="client/jquery.min.js"></script>
    <script>


<<groupJs>>

$(document).ready(function() {
  for (var key in hiddenGroups) {
    group = hiddenGroups[key];
    $("."+group[0]).hide();
    if( $("."+group[0]).size() > 0 )
      $('#hints').append( '<div class="plainbox">'
          + ($("."+group[0]).size() / 2) + ' Posts älter als ' + group[1] + ' ausgeblendet. '
          + '<a onclick="$(\'.'+group[0]+'\').show(); $(this).parent().hide(); return false;" href="#">anzeigen</a>'
          + '</div>' );
  }
});

    </script>
  </head>
  <body>

<<mailhint>>
<div style="border: 1px solid red; background-color: #FEDCDC; color: red; padding: 30px; font-weight:bold;">Deine aktuelle Email-Adresse ist nicht g&uuml;ltig. <br /><br />Bitte gib eine g&uuml;ltige Adresse an: <a href="../admin/index.php?login=1&change=1">Email &auml;ndern</a></div>
<br />
<br />
<</mailhint>>

<<message>>
<br />
<span style="border: 3px solid #9ED08C; padding: 10px; width:400px; margin:10px; "><<text>></span>
<br />
<br />
<br />
<</message>>

<<filterButtons>>
<h1><a href="<<backURL>>" style="text-decoration:none; color: #777; font-size:16px; margin-right:3px;" title="Zur&uuml;ck zur &Uuml;bersicht">&lt;&lt;</a>
<a href="<<ticketURL>>" style="text-decoration:none; color: black;" title="<<title>>"><<title>></a></h1>

<div style="color: #D9D9D9; font-size:16px; border-bottom: 1px solid #E3EAEB; padding-bottom: 10px;"> <<currentUserName>> :: Version <<version>> :: <a href="<<newlink>>" style="color:#9ED08C;">Neues Ticket</a> ::<<globStatus>> :: <form method="get" action="<<searchURL>>" style="display:inline"><input name="tkSearch" value="<<searchValue>>" title="<<standardSearchValue>>" class="oneline<<searchActive>>" onfocus="if( this.value == this.title ) this.select();"/><input name="f" type="hidden" value="<<searchKeepFilter>>" /><<searchButton>></form></div>

<<thread>>
<div class="thread">
  <a name="manage" href="#"></a>

  <div class="headline" id="headline">
    <span class="first"><a href="#answer" title="<<author>> antworten" style="color: #000">Autor: <<author>> <img style="vertical-align:middle" src="img/comment_add.png" alt="Antworten" /></a> </span><span><<build>></span><span class="<<status_highlight>>"><<status>></span><span> In Bearbeitung: <<checkout>></span>
    
    <<managebutton>><button id="manageThreadButton" onclick="document.getElementById('manageThread').style.display = 'block'; document.getElementById('headline').style.display = 'none'; this.style.display = 'none'; ">Ticket-Daten bearbeiten</button><</managebutton>>
    
    
  <<statuschange>>
    <<statusform>>
    <form method="post" style="display:inline;" action="#manage">
      <input type="hidden" name="stausChangeId" value="<<frontid>>" />
      <input type="hidden" name="newStatus" value="<<newstatus>>" />
      <input name="chkSubmit" type="submit" value="<<action_name>>" />
    </form>
    <</statusform>>
  <</statuschange>>
  
  <<deleteThread>>
  <span><a onclick="return window.confirm('Ticket wirklich l&ouml;schen?')" href="<<deleteLink>>"><img src="img/remove.gif" border="0" alt="L&ouml;schen" title="Ticket l&ouml;schen" /></a></span>
  <</deleteThread>>
  
  </div>
  <<manage>>
 
  
  <div class="headline">
  <form method="post" id="manageThread" style="display:none;" action="">
    <span class="first"><input name="subject" value="<<subject>>" /></span>
    <span class="status"><select name="status"><<addStatusOptions>></select></span> 
    <span> In Bearbeitung: <<checkout>></span>
    <span><input type="submit" value="Speichern" /></span>
    <span><button onclick="document.getElementById('manageThreadButton').style.display = 'inline'; document.getElementById('headline').style.display = 'inline'; document.getElementById('manageThread').style.display = 'none'; return false;">Abbrechen</button></span>
  <input type="hidden" name="mgId" value="<<frontid>>" /> <br />
  </form>
  </div>
  
  <</manage>>
  <br />
  <div id="hints" style="margin:0px; padding:0px;">
  
  </div>
  
  
  <<post>>

    
    <div id="comment<<count>>" class="content <<group>>">
      <div class="text <<postType>> <<newForUser>>"><<text>><div class="uplink"><a href="#manage"><img src="img/totop.png" alt="Nach oben" title="Nach oben" /></a></div></div>
    </div>
    <div class="content" id="editComment<<count>>" style="display:none;">
    <div class="text threadowner unread">
    <form method="post" style="width:500px;" action="">
      <textarea name="text" id="commentText<<count>>" cols="80" rows="15"><<text_orig>></textarea><br />
      <input type="hidden" name="mgPostId" value="<<id>>" /> 
      <input type="hidden" name="p" value="editPost" />
       <button onclick="$('#preview<<count>> > div').load('preview.php', { 'previewText': $('#commentText<<count>>').val() } ); $('#preview<<count>>').show(); return false;">Preview</button>
      <input type="submit" value="Bearbeitung speichern" />
      <button onclick="$('#comment<<count>>').toggle(); $('#editComment<<count>>').toggle(); $('#preview<<count>>').hide(); return false; ">Abbrechen</button>
      </form>
    </div></div>
    

    <div class="manage  <<group>>">

    <a href="#answer" title="<<author>> antworten" class="<<postType>>"><<author>></a>  <em><<build>></em> <br /><br style="line-height:5px;"/>
   
   

    
    <<managePost>>
     
      
      <button id="editCommentButton<<count>>" onclick="$('#editComment<<count>>').toggle(); $('#comment<<count>>').toggle();  $('#preview<<count>>').hide();" ><img src="img/comment_edit.png" alt="Kommentar bearbeiten" title="Kommentar bearbeiten" /></button>
  
      <form onsubmit="return window.confirm('Wirklich l&ouml;schen?')" method="post" id="deleteComment<<count>>" style="display:inline;" action="">
      <input type="hidden" name="p" value="deletePost" />
      <input type="hidden" name="mgPostId" value="<<id>>" /> 
      <input type="image" src="img/comment_delete.png" alt="Kommentar l&ouml;schen"  value="L&ouml;schen"  />
      </form>
      
    <</managePost>>
    
    <a style="margin:0px; padding:0px; border:0px; margin-left:8px;" href="#answer" title="Antworten" class="<<postType>>"><img src="img/comment_add.png" alt="Antworten" /></a>  
    

    </div>

  
    <!-- Preview for Post Edit -->
    <div id="preview<<count>>" style="display:none; clar:both;" class="content preview">
      <div class="text <<postType>>"></div>
    </div>
  

  <</post>>
  
  
  <<newpost>>
 
  
   <div class="content" style="clear:both; float:none;">
    <div class="text threadowner createnew">
      <a name="answer" href="#"></a>
      <form method="post" id="newPost" style="display:inline" action="">
      <textarea id="newPostText" name="text" cols="80" rows="8"></textarea><br />
      <input type="hidden" name="threadid" value="<<frontid>>" />
      <input type="hidden" name="p" value="newPost" />
      <br />
      <button onclick="$('#preview > div').load('preview.php', { 'previewText': $('#newPostText').val() } ); $('#preview').show(); return false;">Preview</button>
      <input type="submit" name="submit" value="Speichern" />
      </form>
    <div class="uplink"><a href="#manage"><img src="img/totop.png" alt="Nach oben" title="Nach oben" /></a></div>
    </div>
  </div>
  
    <!-- Preview for new Post to Ticket -->
    <div id="preview" style="display:none; clar:both;" class="content preview">
      <div class="text unread createnew threadowner"></div>
    </div>
   
    
    
  <</newpost>>
  
  
  <br />
  <br />
  
  
</div>

<br />
<br />


<a href="<<backlink>>" class="tLink">Zur&uuml;ck</a> 
<</thread>>






<<list>>
<div id="overview">
  <<threadRow>>

<div class="<<status_highlight>> <<hilStatus>>">

  <div class="status"><a href="<<link>>"><em  class="<<status_highlight>>"><<status>></em> </a></div>
  
  <div class="subject"><a href="<<link>>"><<subject>> </a></div>
  
  <div class="info"><a href="<<link>>">von <<author>>, <<allPosts>>, letzter <<lastchange>>   <em class="unread"><<unreadPosts>></em> </a></div>


<br style="clear:both;" />
</div>  

  <</threadRow>>
</div>

    

<</list>>


<<pagination>>
  <br />
  <<pagelink>><a href="<<link>>" class="pagination<<special>>"><<title>></a><</pagelink>>
  <a style="margin-left:80px" href="?do=markAllAsRead" class="tLink">Alle als gelesen markieren</a><br />
  <br />
<</pagination>>


<<newerlink>>
<a href="<<lastOffset>>">&lt;&lt; Neuere</a>&nbsp;&nbsp;
<</newerlink>>

<<forwardlink>>
<a href="<<nextOffset>>">&Auml;ltere &gt;&gt;</a>
<</forwardlink>>


<<newlinkcon>>
<br />
<br />
<!--<a href="<<newlink>>" class="tLink">Neues Ticket erstellen</a><br />-->
<</newlinkcon>>

<<listError>>
<div style="border: 3px solid #FA3E3E; padding: 10px; width:400px; margin: 10px;"><<error>></div>
<</listError>>

<<newthread>>


<form method="post" action="">
<div class="thread">
 <div class="content" style="clear:both; float:none;">
    <div class="text threadowner createnew">
      <h3>Titel</h3>
      <input type="text" name="subject" size="50" class="oneline" /><br />
      <em>(der Titel sollte m&ouml;glichst pr&auml;zise und pr&auml;gnant sein)</em>
      <br /><br />
    </div>
  </div>
  
   <div class="content" style="clear:both; float:none;">
    <div class="text threadowner createnew">
       <h3>Beschreibung</h3>
      <textarea id="threadText" name="text" cols="80" rows="20"></textarea><br />
      <em>(bitte beschreiben Sie Ihr Problem so genau wie m&ouml;glich)</em>
      <br /><br />
      <button onclick="$('#preview > div').load('preview.php', { 'previewText': $('#threadText').val() } ); $('#preview').show(); return false;">Preview</button>
      <input type="submit" value="Abschicken" />
     <br /><br />
    </div>
  </div>
  <div id="preview" class="content" style="clear:both; float:none; display:none;">
    <div class="text threadowner createnew">
    </div>
  </div>
  
</div>

<input type="hidden" name="p" value="newTicket" />
</form>
   
<br /><br /><br />
 
<</newthread>>


<<email:statusChange>>Hallo <<username>>,

der Status im Thema "<<subject>>" hat sich zu "<<status>>" geändert.


Ticket-Notify<</email:statusChange>>



<<email:newThread>>Hallo <<username>>,<br /><br />

<<author>> hat ein neues Ticket mit dem Titel "<<subject>>" erstellt.<br /><br />

<h1><<subject>></h1>
<div style="border: 1px solid #E3EAEB; padding: 8px; margin: 10px 0px; background-color: #F4F4F4;">
<<content>>
</div>
<br /><br />
<a href="<<link>>">Zum Thema</a>
<br /><br />

Ticket-Notify<</email:newThread>>


<<email:newComment>>Hallo <<username>>,<br /><br />

<<author>> hat in dem Ticket  "<strong><<subject>></strong>" (#<<frontid>>) einen Kommentar erstellt:<br /><br />

<div style="border: 1px solid #E3EAEB; padding: 8px; margin: 10px 0px; background-color: #F4F4F4;">
<<content>>
</div>
<br /><br />
<a href="<<link>>">Zum Thema</a>
<br /><br />

Ticket-Notify<</email:newComment>>







  </body>
</html><</body>>
