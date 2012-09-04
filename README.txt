These are utility functions for use with the DecalCMS API (http://decalcms.com/)

Usage examples: https://github.com/dgrinton/decalcms-appwidget-util/blob/master/examples.md

Function reference: https://github.com/dgrinton/decalcms-appwidget-util/blob/master/functions.txt

Decal API reference: https://docs.google.com/spreadsheet/ccc?key=0AkqTBI--vTSXdHhJV1BneTBrTUdnemhlakVDSlJTSHc#gid=0

Essentially, the workflow is this:
    
    <?php
        require('util.php');
        $c = makeACurl();
        $args = array(); //build an associative array using Decal API parameters here
        curl_setopt($c, CURLOPT_POSTFIELDS, buildQueryString($args));
        $data = curl_exec($c);
        //now $data is a string of XML that can be parsed with DomDocument or
        //simplexml etc
    ?>

A Decal AppWidget will be called in the context of a page in the DecalCMS site.

The widget will be passed in GET:
page_title - the name of the current page
dcl_logged_in - if the viewer of the page is logged in
hostname - the hostname of the site calling the widget
api_key - the Decal API key of the site calling the widget

And in POST:
data - the entire contents of the <html> node as rendered by the site

The standard mode of operation for a widget is to make some modification to the
POSTed data and then output it again. It can also discard the POSTed data
entirely and output something else.

If a widget sends a header:
    x-decalcms-standalone: true
then what the widget returns will be the final output to the browser.

If a widget sends a header:
    x-decalcms-keepheaders: true
then the headers output by the widget will be forwarded to the browser.
