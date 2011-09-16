What is jelix-lessphp-plugin ?
==============================

This project is a plugin for [Jelix](http://jelix.org) PHP framework. It allows you to use easily [less](http://lesscss.org/) dynamic stylesheet language in Jelix (using [lessphp](http://leafo.net/lessphp/) compiler).

This is an htmlresponse plugin.


---


Installation
============

Under Jelix default configuration, create an "htmlresponse" directory in your project's "plugins" directory.
Clone this repository in that directory.

---

Usage
=====

When including a CSS file (e.g. with addCSSLink()) you should set "stylesheet/less" for the "rel" param.

E.g. in your response :

`$this->addCSSLink($gJConfig->urlengine['basePath'].'themes/'.$gJConfig->theme.'/Css/style.css', array( 'rel' => 'stylesheet/less' ));`

Your config file must activate lessphp plugin :

    [jResponseHtml]
    plugins=lessphp

N.B. : the directories containing less files should be writable by your web server ! Indeed, compiled files will be written in that very same directory so that relative urls go on working ...



If you which to switch between lessphp and client-side less, you should just have to remove "lessphp" from your jResponseHtml's plugins and include less.js.


Config
======

You can configure lessphp's behviour regarding compilation:

    [jResponseHtml]
    ;...
    ; always|onchange|once
    lessphp_compile=always

If lessphp\_compile's value is not valid or empty, its default value is onchange.

always : compile less file on all requests
onchange : compile less file only if it has changed
once : compile less file once and never compile it again (until compiled file is removed)

