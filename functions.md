###makeACurl()
- returns a curl object (as created by curl_init) pointing to the API endpoint, still needs to have POST parameters added to specify what to retrieve

###stripSize($str)
- removes one size specification from an image URL eg myimage-100x200.jpg becomes myimage.jpg
does nothing if no size specification exists
- returns string

###buildSizedUrl($str, $w, $h)
- strips a size (if there was one) and adds a size spec of $w x $h
- eg: buildSizedUrl('myimage-100x200.jpg', 200, 400) returns 'myimage-200x400.jpg'
- returns string

###escapeScripts($str)
- $str should be a HTML snippet
- adds CDATA sections to scripts in $str so that it can be parsed as XML
- returns string

###renderArea($page,$name)
- render area $name from $page, or all areas if $name not supplied
- handles contig start/end blocks
- returns a string

###clear($node)
- removes children of the node
- returns nothing

###setText($node, $text)
- clears node and adds a textnode with $text
- returns nothing

###getTags()
- returns domnodelist of tags in use by the site

###getTagged($tags, $args)
- $args is optional, if supplied should be an assoc array with key 'template' which specifies a template to restrict search to
- returns domnodelist of pages tagged with $tags

###fetchPage($title)
- returns domelement of page with $title, or the current page if $title is not supplied

###validateEmail($email)
- returns true/false to indicate if $email was a valid email address

###encodeUrlName($name)
- encode a page name for use as a /page/xxxx url
- returns a string

###buildQueryString($fields)
- takes an assoc array of $param_name => $param_value and returns a urlencoded
- string eg something=whatever&this=that
