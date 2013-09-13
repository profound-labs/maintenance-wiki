<?php if (!defined('PmWiki')) exit();

Markup("epiceditor-form", "directives", "/\\(:epiceditor:\\)/", Keep("
<div id='cheatsheet' class='well well-sm' style='display: none;'>
  <button type='button' class='close pull-right' style='font-size: 0.9em;' onclick='\$(\"#cheatsheet\").fadeToggle();'>&times; hide</button>

  <ul class='nav nav-tabs'>
    <li class='active'><a href='#' onclick='thisObj=\$(this); showMarkdownCheatsheet(thisObj);'>Markdown</a></li>
    <li><a href='#' onclick='thisObj=\$(this); showPmWikiCheatsheet(thisObj);'>PmWiki</a></li>
  </ul>

  <div id='markdown-syntax'>
    <p>Markdown can be used between <span class='mono'>(:multimarkdown:)</span> and <span class='mono'>(:multimarkdownend:)</span>. For reference, see <a href='http://daringfireball.net/projects/markdown/basics' target='_blank'>daringfireball.net</a> and the <a href='https://rawgithub.com/fletcher/human-markdown-reference/master/index.html' target='_blank'>MultiMarkdown Guide</a>.</p>
    <pre>
# Header 1

## Header 2

This is the same[^label] paragraph until a
blank line.

This is *italic*, a forced linebreak,\\\\
**and bold text**.

[^label]: Meaning identical.
    </pre>
  </div>

  <div id='pmwiki-syntax' class='hide'>
    <p>See examples of PmWiki syntax below. For reference, see the <a href='http://www.pmwiki.org/wiki/PmWiki/DocumentationIndex' target='_blank'>PmWiki docs</a>.</p>
    <pre>
! Header 1

!! Header 2

This is the same paragraph until a
blank line.

This is ''italic'', a forced linebreak,\\\\
'''and bold text'''.

Wiki-link with auto-title: [[Monastery.ThePage|+]]

Add a page to a category simply by placing a
[[Category.TheName]] link on the page.
    </pre>
  </div>
</div>

<script>
function showMarkdownCheatsheet(thisObj) {
  $(\"#markdown-syntax\").removeClass('hide');
  $(\"#pmwiki-syntax\").addClass('hide');
  thisObj.parent().parent().children().removeClass('active');
  thisObj.parent().addClass('active');
}
function showPmWikiCheatsheet(thisObj) {
  $(\"#markdown-syntax\").addClass('hide');
  $(\"#pmwiki-syntax\").removeClass('hide');
  thisObj.parent().parent().children().removeClass('active');
  thisObj.parent().addClass('active');
}
</script>

<form class='form-inline' role='form'>
  <div class='checkbox'>
    <label id='epicSwitch'>
      <input type='checkbox' checked > EpicEditor
    </label>
  </div>
  <div class='checkbox epicControl'>
    <label id='epicPreviewSwitch'>
      <input type='checkbox'> Preview Markdown
    </label>
  </div>
  <div class='checkbox epicControl'>
    <label id='epicFullscreenSwitch'>
      <input type='checkbox'> Fullscreen
    </label>
  </div>
  <button type='button' class='btn btn-info btn-xs pull-right' onclick='\$(\"#cheatsheet\").fadeToggle();'> Cheatsheet </button>
</form>
<div id='epiceditor'></div>
"));

//Markup("epiceditor-filelist", "directives", "/\\(:epiceditor-filelist:\\)/", Keep("<div class='list-group' id='localFileList' class='epicControl'></div>"));
Markup("epiceditor-filelist", "directives", "/\\(:epiceditor-filelist:\\)/", Keep(""));

$HTMLHeaderFmt['epiceditor'] = "<link href='$PubDirUrl/stylesheets/epiceditor.css' rel='stylesheet'>";

if ($action == 'edit') {
  $HTMLFooterFmt['epiceditor'] = '
<script src="'.$PubDirUrl.'/javascripts/vendor/epiceditor/js/epiceditor.min.js"></script>
<script src="'.$PubDirUrl.'/javascripts/vendor/moment.min.js"></script>
<script> var PubDirUrl = "'.$PubDirUrl.'"; var PageName = "'.$pagename.'"; </script>
<script src="'.$PubDirUrl.'/javascripts/epiceditor-load.js"></script>
';
}

