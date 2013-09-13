<?php if (!defined('PmWiki')) exit();
##  This is a sample config.php file.  To use this file, copy it to
##  local/config.php, then edit it for whatever customizations you want.
##  Also, be sure to take a look at http://www.pmwiki.org/wiki/Cookbook
##  for more details on the types of customizations that can be added
##  to PmWiki.

## For testing
#error_reporting(E_ALL);
#error_reporting(0);

## set a unique PHP session name to avoid conflicts with other wikis on the same domain
## Customize this to for your site, and change it in .htaccess too.
session_name('MAINTENANCEWIKI');

## =========================
## PmWiki Core Configuration
## =========================

##  $WikiTitle is the name that appears in the browser's title bar.
$WikiTitle = 'Maintenance Wiki';

##  $ScriptUrl is your preferred URL for accessing wiki pages
##  $PubDirUrl is the URL for the pub directory.

## Set these to your domain:

$ScriptUrl = 'http://wiki.mysite.org/index.php';
$PubDirUrl = 'http://wiki.mysite.org/pub';

## Override URLs if we are on localhost
## Point your local server's root to wiki's folder directly.

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $ScriptUrl = 'http://localhost/index.php';
    $PubDirUrl = 'http://localhost/pub';
}

##  If you want to use URLs of the form .../pmwiki.php/Group/PageName
##  instead of .../pmwiki.php?p=Group.PageName, try setting
##  $EnablePathInfo below.  Note that this doesn't work in all environments,
##  it depends on your webserver and PHP configuration.  You might also
##  want to check http://www.pmwiki.org/wiki/Cookbook/CleanUrls more
##  details about this setting and other ways to create nicer-looking urls.

## Set only if you have /Group/PageName style URLs working with your
## webserver already.

$EnablePathInfo = 0;

## Enable for development to see http:/localhost/?action=ruleset info.
## Disable for production.
$EnableDiag = 0;

$EnableStopWatch = 0;
#$HTMLFooterFmt['stopwatch'] = 'function:StopWatchHTML 1';

$EnableRedirectQuiet = 1;

## If you want to change pagename validation.
## Use with caution, changing the default might break cookbook recepies.
#$GroupPattern = '[[:upper:]][\\w]*(?:-\\w+)*';
#$NamePattern = '[[:upper:]\\d][\\w]*(?:-\\w+)*';

$EnableDrafts = 1;
$EditTemplatesFmt = '{$Group}.Template';

## Max Includes
## Overriding the default 50.
$MaxIncludes = 500;

## $PageLogoUrl is the URL for a logo image -- you can change this
## to your own logo if you wish.
#$PageLogoUrl = "$PubDirUrl/images/bootstrap/ico/favicon.png";

## If you want to have a custom skin, then set $Skin to the name
## of the directory (in pub/skins/) that contains your skin files.
## See PmWiki.Skins and Cookbook.Skins.
$Skin = 'bootstrap-navbar-pmwiki';

## Unicode (UTF-8) allows the display of all languages and all alphabets.
## Highly recommended for new wikis.
include_once("scripts/xlpage-utf-8.php");

## If you're running a publicly available site and allow anyone to
## edit without requiring a password, you probably want to put some
## blocklists in place to avoid wikispam.  See PmWiki.Blocklist.
# $EnableBlocklist = 1;                    # enable manual blocklists
# $EnableBlocklist = 10;                   # enable automatic blocklists

##  PmWiki comes with graphical user interface buttons for editing;
##  to enable these buttons, set $EnableGUIButtons to 1.  
# $EnableGUIButtons = 1;

##  To enable markup syntax from the Creole common wiki markup language
##  (http://www.wikicreole.org/), include it here:
# include_once("scripts/creole.php");

##  Some sites may want leading spaces on markup lines to indicate
##  "preformatted text blocks", set $EnableWSPre=1 if you want to do
##  this.  Setting it to a higher number increases the number of
##  space characters required on a line to count as "preformatted text".
# $EnableWSPre = 1;   # lines beginning with space are preformatted (default)
# $EnableWSPre = 4;   # lines with 4 or more spaces are preformatted
# $EnableWSPre = 0;   # disabled

## Uploads
## -------

##  If you want uploads enabled on your system, set $EnableUpload=1.
##  You'll also need to set a default upload password, or else set
##  passwords on individual groups and pages.  For more information
##  see PmWiki.UploadsAdmin.
$EnableUpload = 1;
$UploadPermAdd = 0;

## See 'Passwords and Permissions' section in this file to set the upload
## password.

$UploadDir = 'attach';
$UploadPrefixFmt = '/$Group/$Name';

##  Setting $EnableDiag turns on the ?action=diag and ?action=phpinfo
##  actions, which often helps others to remotely troubleshoot 
##  various configuration and execution problems.
# $EnableDiag = 1;                         # enable remote diagnostics

##  By default, PmWiki doesn't allow browsers to cache pages.  Setting
##  $EnableIMSCaching=1; will re-enable browser caches in a somewhat
##  smart manner.  Note that you may want to have caching disabled while
##  adjusting configuration files or layout templates.
# $EnableIMSCaching = 1;                   # allow browser caching

##  Set $SpaceWikiWords if you want WikiWords to automatically 
##  have spaces before each sequence of capital letters.
# $SpaceWikiWords = 1;                     # turn on WikiWord spacing

##  Set $EnableWikiWords if you want to allow WikiWord links.
##  For more options with WikiWords, see scripts/wikiwords.php .
# $EnableWikiWords = 1;                    # enable WikiWord links

##  $DiffKeepDays specifies the minimum number of days to keep a page's
##  revision history.  The default is 3650 (approximately 10 years).
# $DiffKeepDays=30;                        # keep page history at least 30 days

## By default, viewers are prevented from seeing the existence
## of read-protected pages in search results and page listings,
## but this can be slow as PmWiki has to check the permissions
## of each page.  Setting $EnablePageListProtect to zero will
## speed things up considerably, but it will also mean that
## viewers may learn of the existence of read-protected pages.
## (It does not enable them to access the contents of the pages.)
# $EnablePageListProtect = 0;

##  The refcount.php script enables ?action=refcount, which helps to
##  find missing and orphaned pages.  See PmWiki.RefCount.
if ($action == 'refcount') include_once("scripts/refcount.php");

##  The feeds.php script enables ?action=rss, ?action=atom, ?action=rdf,
##  and ?action=dc, for generation of syndication feeds in various formats.
# if ($action == 'rss')  include_once("scripts/feeds.php");  # RSS 2.0
# if ($action == 'atom') include_once("scripts/feeds.php");  # Atom 1.0
# if ($action == 'dc')   include_once("scripts/feeds.php");  # Dublin Core
# if ($action == 'rdf')  include_once("scripts/feeds.php");  # RSS 1.0

##  In the 2.2.0-beta series, {$var} page variables were absolute, but now
##  relative page variables provide greater flexibility and are recommended.
##  (If you're starting a new site, it's best to leave this setting alone.)
$EnableRelativePageVars = 1; # 1=relative; 0=absolute

##  By default, pages in the Category group are manually created.
##  Uncomment the following line to have blank category pages
##  automatically created whenever a link to a non-existent
##  category page is saved.  (The page is created only if
##  the author has edit permissions to the Category group.)
# $AutoCreate['/^Category\\./'] = array('ctime' => $Now);

##  If you want to have to approve links to external sites before they
##  are turned into links, uncomment the line below.  See PmWiki.UrlApprovals.
##  Also, setting $UnapprovedLinkCountMax limits the number of unapproved
##  links that are allowed in a page (useful to control wikispam).
# $UnapprovedLinkCountMax = 10;
# include_once("scripts/urlapprove.php");

##  The following lines make additional editing buttons appear in the
##  edit page for subheadings, lists, tables, etc.
# $GUIButtons['h2'] = array(400, '\\n!! ', '\\n', '$[Heading]',
#                     '$GUIButtonDirUrlFmt/h2.gif"$[Heading]"');
# $GUIButtons['h3'] = array(402, '\\n!!! ', '\\n', '$[Subheading]',
#                     '$GUIButtonDirUrlFmt/h3.gif"$[Subheading]"');
# $GUIButtons['indent'] = array(500, '\\n->', '\\n', '$[Indented text]',
#                     '$GUIButtonDirUrlFmt/indent.gif"$[Indented text]"');
# $GUIButtons['outdent'] = array(510, '\\n-<', '\\n', '$[Hanging indent]',
#                     '$GUIButtonDirUrlFmt/outdent.gif"$[Hanging indent]"');
# $GUIButtons['ol'] = array(520, '\\n# ', '\\n', '$[Ordered list]',
#                     '$GUIButtonDirUrlFmt/ol.gif"$[Ordered (numbered) list]"');
# $GUIButtons['ul'] = array(530, '\\n* ', '\\n', '$[Unordered list]',
#                     '$GUIButtonDirUrlFmt/ul.gif"$[Unordered (bullet) list]"');
# $GUIButtons['hr'] = array(540, '\\n----\\n', '', '',
#                     '$GUIButtonDirUrlFmt/hr.gif"$[Horizontal rule]"');
# $GUIButtons['table'] = array(600,
#                       '||border=1 width=80%\\n||!Hdr ||!Hdr ||!Hdr ||\\n||     ||     ||     ||\\n||     ||     ||     ||\\n', '', '', 
#                     '$GUIButtonDirUrlFmt/table.gif"$[Table]"');

## =========================
## Passwords and permissions
## =========================

## You'll probably want to set an administrative password that you
## can use to get into password-protected pages.  Also, by default 
## the "attr" passwords for the PmWiki and Main groups are locked, so
## an admin password is a good way to unlock those.  See PmWiki.Passwords
## and PmWiki.PasswordsAdmin.

## If you want to store encrypted passwords here, use this page:
## http://www.pmwiki.org/wiki/PmWiki/PasswordsAdmin?action=crypt
## Use it on your domain if you alread have a working PmWiki.

## After setting the site-wide admin password here, go to
## http://yoursite.com/index.php?n=SiteAdmin.AuthUser?action=edit
## enter the password without a username, then setup a user who belongs to the
## admin group.

$DefaultPasswords['admin'] = array(
#  crypt('youradminpassword'),
  '@admins'
);

$DefaultPasswords['read'] = array('@members', '@editors', '@admins');

#$DefaultPasswords['attr'] = crypt('yourattrpassword');
$DefaultPasswords['edit'] = array('@editors', '@admins');
$DefaultPasswords['publish'] = array('@editors', '@admins');
$DefaultPasswords['upload'] = array('@editors', '@admins');
$DefaultPasswords['rename'] = array('@editors', '@admins');

## Prevents non authorized users from viewing comment source email address
$HandleAuth['source'] = 'edit';
$HandleAuth['diff'] = 'edit';

## ==============
## Authentication
## ==============

## Require authors to provide a name when they edit pages
$EnablePostAuthorRequired = 1;

## AuthUser
## --------
## http://www.pmwiki.org/wiki/PmWiki/AuthUser
include_once("$FarmD/scripts/authuser.php");

## Default page

$DefaultGroup = 'Main';

if ($AuthId) {
  $DefaultPage = 'Main.HomePage';
} else {
  $DefaultPage = 'Landing.HomePage';
}


## ============
## Custom Skins
## ============

## Printing
//$ActionSkin['print'] = 'bootstrap-print';
Markup("printbutton", "directives", "/\\(:printbutton:\\)/", Keep("<button type='button' class='btn btn-default' onclick='window.print(); return false;'>Print</button>"));

## Allow skins to be set with a GET ?skin= parameter, but limit what skins to 
## accept this way.

$allowed_skins = array('pmwiki', 'bootstrap-navbar-pmwiki',);

if (isset($_GET['skin'])) {
    if (in_array($_GET['skin'], $allowed_skins)) {
        $Skin = $_GET['skin'];
    }
}

## =================
## Cookbook Recepies
## =================

## RenamePage
## ----------
## http://www.pmwiki.org/wiki/Cookbook/RenamePage

## RenamePage depends on either renamehelper.php or MarkupExtensions, but
## don't use both. Uncomment renamehelper.php if you use MarkupExtensions.

## NOTE: It is recommened to use renamehelper.php unless you actually need what
## MarkupExtensions includes.

if ($action == 'rename' || $action == 'postrename' || $action == 'links' ) {
    include_once("$FarmD/cookbook/renamehelper.php");
    include_once("$FarmD/cookbook/rename.php");
}

## PowerTools
## ----------
## http://www.pmwiki.org/wiki/Cookbook/PowerTools
include_once("$FarmD/cookbook/powertools.php");

## TextExtract
## -----------
## http://www.pmwiki.org/wiki/Cookbook/TextExtract
include_once("$FarmD/cookbook/extract.php");

// these are used in the skin template, otherwise PmWiki would try to interpret the $$ variables
//$SearchParamHeader = '{$$matchcnt} találat {$$pagecnt} oldalon, {$$listcnt} oldalból. Keresett kifejezés: {$$pattern}';
//$SearchParamPhead = '[[{$$source}|+]] -- {$$pmatchnum} találat';

## NewPageBox Plus
## ---------------
## http://www.pmwiki.org/wiki/Cookbook/NewPageBoxPlus
include_once("$FarmD/cookbook/newpageboxplus.php");

## MultiMarkdown
## -------------
include_once("$FarmD/cookbook/multimarkdown.php");

## EpicEditor
## ----------
include_once("$FarmD/cookbook/epiceditor.php");

## ==========================
## Custom Markup Declarations
## ==========================

##  PmWiki allows a great deal of flexibility for creating custom markup.
##  To add support for '*bold*' and '~italic~' markup (the single quotes
##  are part of the markup), uncomment the following lines. 
##  (See PmWiki.CustomMarkup and the Cookbook for details and examples.)
# Markup("'~", "inline", "/'~(.*?)~'/", "<i>$1</i>");        # '~italic~'
# Markup("'*", "inline", "/'\\*(.*?)\\*'/", "<b>$1</b>");    # '*bold*'

Markup("landing-welcome", "directives", "/\(:landing-welcome:\)/", Keep('<button type="button" class="btn btn-primary"><a href="'.$ScriptUrl.'?n='.$DefaultPage.'">Main</a></button>'));

