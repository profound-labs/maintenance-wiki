Maintenance Wiki
================

A pre-configured PmWiki with plugins to kickstart a maintenance wiki.

It's got Boostrap 3, EpicEditor and MultiMarkdown.

Only PHP is required. No database, PmWiki stores pages in plain-text
files.

## Setup

Clone and point your webserver to the folder:

    $ git clone https://github.com/profound-labs/maintenance-wiki.git

Set the folder permissions: the webserver needs write permissions for:

* `attach`
* `wiki.d`
* `temp_md`

Substitute `www-data` with the webserver's user group:

    $ cd maintenance-wiki
    $ chmod 775 .
    $ sudo chgrp www-data .
    $ chmod 775 wiki.d attach temp_md
    $ chmod 664 wiki.d/* attach/* temp_md/*
    $ sudo chgrp -R www-data wiki.d attach temp_md

Change your domain in `local/config.php`:

    $ScriptUrl = 'http://wiki.mysite.org/index.php';
    $PubDirUrl = 'http://wiki.mysite.org/pub';

Optionally, if you are going to need Sass + Compass for the stylesheets:

    $ bundle install

## Login

Authentication is via [PmWiki's AuthUser][]. If you have cloned this setup, the
default username and password is `admin : admin`. Visit
`/index.php?n=SiteAdmin.AuthUser&action=edit` and change it.

[PmWiki's AuthUser](http://www.pmwiki.org/wiki/PmWiki/AuthUser)

## Landing page

Add images for the carousel in `pub/images/carousel/`, and add them to
`pub/skins/landing.tmpl` as carousel items (`<div class="item">`)
instead of the placeholders.

