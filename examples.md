Fetching a page's tags and description:

    <?php
        require('util.php');
        $page = fetchPage();
        $tags = explode(',',$page->getAttribute('tags'));
        $descr = $page->getAttribute('description');
    ?>

Fetching the parent page:

    <?php
        require('util.php');
        $page = fetchPage();
        $xp = new DomXPath($page->ownerDocument);
        $parent_crumb = $xp->query('.//crumb[2]', $page)->item(0);
        if($parent_crumb) {
            $parent_page_name = $parent_crumb->getAttribute('title');
        }
        $parent_page = fetchPage($parent_page_name);
    ?>
