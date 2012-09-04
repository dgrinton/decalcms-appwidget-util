<?php
    function makeACurl() {
        if(array_key_exists('hostname',$_GET)) {
            $server = $_GET['hostname'];
        }
        else {
            die('please pass "hostname" in with GET');
        }
        if(array_key_exists('api_key',$_GET)) {
            $api_key = $_GET['api_key'];
        }
        else {
            die('please pass "api_key" in with GET');
        }
        $dst = 'http://'.$server.'/index.php?h=DecalApi&api_key='.$api_key;
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$dst);
        curl_setopt($curl,CURLOPT_HEADER, 0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER , false );
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST , false );
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_POST, 1);
        return $curl;
    }

    function stripSize($str) {
        return preg_replace('/^(.*)-\d+x\d+\.([^.]+)$/', '$1.$2', $str);
    }

    function buildSizedUrl($str, $w, $h) {
        $base = stripSize($str);
        $parts = explode('.',$base);
        $ext = array_pop($parts);
        $parts[count($parts)-1] .= '-'.$w.'x'.$h;
        $parts[] = $ext;
        return implode('.',$parts);
    }

    function escapeScripts($data) {
        $parts = preg_split('/(<\\\?\/?script[^>]*>)/', $data, null,
             PREG_SPLIT_DELIM_CAPTURE);
        $depth = 0;
        $out = '';
        foreach($parts as $p) {
            if(preg_match('/^<script/', $p)) {
                $out .= $p;
                if($depth == 0) {
                    $out .= "\n//<![CDATA[\n";
                }
                $depth += 1;
            }
            else if(preg_match('/^<\\\?\/script>/', $p)) {
                $depth -= 1;
                if($depth == 0) {
                    $out .= "\n//]]>\n";
                }
                $out .= $p;
            }
            else {
                $out .= $p;
            }
        }
        return $out;
    }

    function renderArea($page,$name='') {
        $doc = $page->ownerDocument;
        $xpath = new DomXPath($doc);
        $ret = '';
        $query = './/component';
        if($name != '') {
            $query .= '[@area="'.$name.'"]';
        }
        $comps = $xpath->query($query,$page);
        $prev_item = NULL;

        foreach($comps as $comp) {   
            if($prev_item !== NULL) {   
                //IF WE CHANGED TYPES
                if($comp->getAttribute('name') != $prev_item->getAttribute('name')) {
                    if($prev_item->getAttribute('block-end')) {
                        $ret .= $prev_item->getAttribute('block-end');
                    }
                    if($comp->getAttribute('block-start')) {
                        $ret .= $comp->getAttribute('block-start');
                    }
                }
            }
            else if($comp->getAttribute('block-start')) {
                $ret .= $comp->getAttribute('block-start');
            }

            $prev_item = $comp;

            foreach($comp->childNodes as $cn) {
                $ret .= $doc->saveXML($cn).PHP_EOL;
            }
        }

        if(isset($comp) && is_object($comp)) {
            if($comp->getAttribute('block-start')) {
                $ret .= $comp->getAttribute('block-end').PHP_EOL;
            }
        }
        $ret = str_replace('dcl_fullhostname',$_GET['hostname'],$ret);
        return $ret;
    }

    function clear($node) {
        while($node->firstChild) {
            $node->removeChild($node->firstChild);
        }
    }

    function setText($node, $text) {
        clear($node);
        $node->appendChild($node->ownerDocument->createTextNode($text));
    }

    function getTags() {
        $c = WsAppWidgetUtil::makeACurl();
        $fields_string = '&tag_cloud=1';
        if(isset($_GET['dcl_logged_in']) && $_GET['dcl_logged_in']) {
            $fields_string .= '&dcl_logged_in=1';
        }
        else {
            $fields_string .= '&dcl_logged_in=0';
        }
        curl_setopt($c,CURLOPT_POSTFIELDS,$fields_string);
        $data = curl_exec($c);
        $doc = new DomDocument();
        $doc->loadXML($data);
        $xpath = new DomXPath($doc);
        return $xpath->query('//tag');
    }

    function getTagged($tags, $args = array()) {
        $c = WsAppWidgetUtil::makeACurl();
        $fields = array(
            'tagged'=>implode(',',$tags),
        );
        if(isset($_GET['dcl_logged_in']) && $_GET['dcl_logged_in']) {
            $fields['dcl_logged_in'] = '1';
        }
        if(array_key_exists('template',$args)) {
            $fields['t'] = $args['template'];
        }
        $qs = buildQueryString($fields);
        curl_setopt($c,CURLOPT_POSTFIELDS, $qs);
        $data = curl_exec($c);
        $doc = new DomDocument();
        $doc->loadXML($data);
        $xpath = new DomXPath($doc);
        return $xpath->query('//page');
    }

    function fetchPage($title) {
        $c = WsAppWidgetUtil::makeACurl();
        $fields = array(
            'p'=>$title,
        );
        if(isset($_GET['dcl_logged_in']) && $_GET['dcl_logged_in']) {
            $fields['dcl_logged_in'] = '1';
        }
        $qs = buildQueryString($fields);
        curl_setopt($c,CURLOPT_POSTFIELDS, $qs);
        $data = curl_exec($c);
        $doc = new DomDocument();
        $doc->loadXML($data);
        $xpath = new DomXPath($doc);
        return $xpath->query('//page')->item(0);
    }

    #yanked from RS
    function validateEmail($email) {
       // First, we check that there's one @ symbol, and that the lengths are right
       if (!preg_match("#[^@]{1,64}@[^@]{1,255}#", $email)) {
         // Email invalid because wrong number of characters in one section,
         // or wrong number of @ symbols.
         return false;
       }
       // Split it into sections to make life easier
       $email_array = explode("@", $email);
       $local_array = explode(".", $email_array[0]);
       for ($i = 0; $i < sizeof($local_array); $i++) {
          if (!preg_match(">^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$>", $local_array[$i])) {
           return false;
         }
       }
       if (!preg_match("#^\[?[0-9\.]+\]?$#", $email_array[1])) { 
         // Check if domain is IP. If not, it should be valid domain name
         $domain_array = explode(".", $email_array[1]);
         if (sizeof($domain_array) < 2) {
             return false; // Not enough parts to domain
         }
         for ($i = 0; $i < sizeof($domain_array); $i++) {
           if (!preg_match("#^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$#", $domain_array[$i])) {
             return false;
           }
         }
       }
       return true;
    }

    function encodeUrlName($name) {
        $ret = $name;
        $ret = str_replace('_','-underscore-',$ret);
        $ret = str_replace('&','-and-'       ,$ret);
        $ret = str_replace(' ','_'           ,$ret);
        $ret = str_replace('/','__'          ,$ret);
        $ret = urlencode($ret);
        return $ret;        
    }

    function buildQueryString($fields) {
        $int = array();
        foreach($fields as $k => $v) {
            $int[] = $k.'='.urlencode($v);
        }
        return implode('&',$int);
    }
