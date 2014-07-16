<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         GNU GPL V2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package         Publisher
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @author          The SmartFactory <www.smartfactory.ca>
 * @author          Marius Scurtescu <mariuss@romanians.bc.ca>
 * @version         $Id$
 */

include_once dirname(__DIR__) . '/admin_header.php';
$myts = MyTextSanitizer::getInstance();

$importFromModuleName = "News " . @$_POST['news_version'];

$scriptname = "news.php";

$op = 'start';

if (isset($_POST['op']) && ($_POST['op'] == 'go')) {
    $op = $_POST['op'];
}

if ($op == 'start') {

    PublisherUtils::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    PublisherUtils::openCollapsableBar('newsimport', 'newsimporticon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_INFO);

    $result = $xoopsDB->query("SELECT COUNT(*) FROM " . $xoopsDB->prefix("topics"));
    list ($totalCat) = $xoopsDB->fetchRow($result);

    if ($totalCat == 0) {
        echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . _AM_PUBLISHER_IMPORT_NO_CATEGORY . "</span>";
    } else {
        include_once XOOPS_ROOT_PATH . '/class/xoopstree.php';

        $result = $xoopsDB->query("SELECT COUNT(*) FROM " . $xoopsDB->prefix("stories"));
        list ($totalArticles) = $xoopsDB->fetchRow($result);

        if ($totalArticles == 0) {
            echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND_NO_ITEMS, $importFromModuleName, $totalArticles) . "</span>";
        } else {
            echo "<span style=\"color: #567; margin: 3px 0 12px 0; font-size: small; display: block; \">" . sprintf(_AM_PUBLISHER_IMPORT_MODULE_FOUND, $importFromModuleName, $totalArticles, $totalCat) . "</span>";

            $form = new XoopsThemeForm(_AM_PUBLISHER_IMPORT_SETTINGS, 'import_form', PUBLISHER_ADMIN_URL . "/import/$scriptname");

            // Categories to be imported
            $sql = "SELECT cat.topic_id, cat.topic_pid, cat.topic_title, COUNT(art.storyid) FROM " . $xoopsDB->prefix("topics") . " AS cat INNER JOIN " . $xoopsDB->prefix("stories") . " AS art ON cat.topic_id=art.topicid GROUP BY art.topicid";

            $result = $xoopsDB->query($sql);
            $cat_cbox_options = array();

            while (list ($cid, $pid, $cat_title, $art_count) = $xoopsDB->fetchRow($result)) {
                $cat_title = $myts->displayTarea($cat_title);
                $cat_cbox_options[$cid] = "$cat_title ($art_count)";
            }

            $cat_label = new XoopsFormLabel(_AM_PUBLISHER_IMPORT_CATEGORIES, implode("<br />", $cat_cbox_options));
            $cat_label->setDescription(_AM_PUBLISHER_IMPORT_CATEGORIES_DSC);
            $form->addElement($cat_label);

            // Publisher parent category
            $mytree = new XoopsTree($xoopsDB->prefix("publisher_categories"), "categoryid", "parentid");
            ob_start();
            $mytree->makeMySelBox("name", "weight", $preset_id = 0, $none = 1, $sel_name = "parent_category");

            $parent_cat_sel = new XoopsFormLabel(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY, ob_get_contents());
            $parent_cat_sel->setDescription(_AM_PUBLISHER_IMPORT_PARENT_CATEGORY_DSC);
            $form->addElement($parent_cat_sel);
            ob_end_clean();

            $form->addElement(new XoopsFormHidden('op', 'go'));
            $form->addElement(new XoopsFormButton ('', 'import', _AM_PUBLISHER_IMPORT, 'submit'));

            $form->addElement(new XoopsFormHidden('from_module_version', $_POST['news_version']));

            $form->display();
        }
    }

    PublisherUtils::closeCollapsableBar('newsimport', 'newsimporticon');
    $xoops->footer();
}

if ($op == 'go') {
    PublisherUtils::cpHeader();
    //publisher_adminMenu(-1, _AM_PUBLISHER_IMPORT);
    PublisherUtils::openCollapsableBar('newsimportgo', 'newsimportgoicon', sprintf(_AM_PUBLISHER_IMPORT_FROM, $importFromModuleName), _AM_PUBLISHER_IMPORT_RESULT);

    $module_handler = xoops_gethandler('module');
    $moduleObj = $module_handler->getByDirname('news');
    $news_module_id = $moduleObj->getVar('mid');

    $gperm_handler = $xoops->getHandlerGroupperm();

    $cnt_imported_cat = 0;
    $cnt_imported_articles = 0;

    $parentId = $_POST['parent_category'];

    $sql = "SELECT * FROM " . $xoopsDB->prefix('topics');

    $resultCat = $xoopsDB->query($sql);

    $newCatArray = array();
    $newArticleArray = array();

    $oldToNew = array();
    while ($arrCat = $xoopsDB->fetchArray($resultCat)) {

        $newCat = array();
        $newCat['oldid'] = $arrCat['topic_id'];
        $newCat['oldpid'] = $arrCat['topic_pid'];

        $categoryObj = $publisher->getCategoryHandler()->create();

        $categoryObj->setVar('parentid', $arrCat['topic_pid']);
        $categoryObj->setVar('weight', 0);
        $categoryObj->setVar('name', $arrCat['topic_title']);
        $categoryObj->setVar('description', $arrCat['topic_description']);

        // Category image
        if (($arrCat['topic_imgurl'] != 'blank.gif') && ($arrCat['topic_imgurl'] != '')) {
            if (copy(XOOPS_ROOT_PATH . "/modules/news/images/topics/" . $arrCat['topic_imgurl'], XOOPS_ROOT_PATH . "/uploads/publisher/images/category/" . $arrCat['topic_imgurl'])) {
                $categoryObj->setVar('image', $arrCat['topic_imgurl']);
            }
        }

        if (!$publisher->getCategoryHandler()->insert($categoryObj)) {
            echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_ERROR, $arrCat['topic_title']) . "<br/>";
            continue;
        }

        $newCat['newid'] = $categoryObj->getVar('categoryid');
        $cnt_imported_cat++;

        echo sprintf(_AM_PUBLISHER_IMPORT_CATEGORY_SUCCESS, $categoryObj->getVar('name')) . "<br\>";

        $sql = "SELECT * FROM " . $xoopsDB->prefix('stories') . " WHERE topicid=" . $arrCat['topic_id'];
        $resultArticles = $xoopsDB->query($sql);
        while ($arrArticle = $xoopsDB->fetchArray($resultArticles)) {
            // insert article
            $itemObj = $publisher->getItemHandler()->create();

            $itemObj->setVar('categoryid', $categoryObj->getVar('categoryid'));
            $itemObj->setVar('title', $arrArticle['title']);
            $itemObj->setVar('uid', $arrArticle['uid']);
            $itemObj->setVar('summary', $arrArticle['hometext']);
            $itemObj->setVar('body', $arrArticle['bodytext']);
            $itemObj->setVar('counter', $arrArticle['counter']);
            $itemObj->setVar('datesub', $arrArticle['created']);
            $itemObj->setVar('dohtml', !$arrArticle['nohtml']);
            $itemObj->setVar('dosmiley', !$arrArticle['nosmiley']);
            $itemObj->setVar('weight', 0);
            $itemObj->setVar('status', _PUBLISHER_STATUS_PUBLISHED);

            $itemObj->setVar('rating', $arrArticle['rating']);
            $itemObj->setVar('votes', $arrArticle['votes']);
            $itemObj->setVar('comments', $arrArticle['comments']);
            $itemObj->setVar('meta_keywords', $arrArticle['keywords']);
            $itemObj->setVar('meta_description', $arrArticle['description']);

            /*
             // HTML Wrap
             if ($arrArticle['htmlpage']) {
             $pagewrap_filename = XOOPS_ROOT_PATH . "/modules/wfsection/html/" .$arrArticle['htmlpage'];
             if (XoopsLoad::fileExists($pagewrap_filename)) {
             if (copy($pagewrap_filename, XOOPS_ROOT_PATH . "/uploads/publisher/content/" . $arrArticle['htmlpage'])) {
             $itemObj->setVar('body', "[pagewrap=" . $arrArticle['htmlpage'] . "]");
             echo sprintf("&nbsp;&nbsp;&nbsp;&nbsp;" . _AM_PUBLISHER_IMPORT_ARTICLE_WRAP, $arrArticle['htmlpage']) . "<br/>";
             }
             }
             }
             */

            if (!$itemObj->store()) {
                echo sprintf("  " . _AM_PUBLISHER_IMPORT_ARTICLE_ERROR, $arrArticle['title']) . "<br/>";
                continue;
            } else {
                /*
                 // Linkes files
                 $sql = "SELECT * FROM ".$xoopsDB->prefix("wfs_files")." WHERE articleid=" . $arrArticle['articleid'];
                 $resultFiles = $xoopsDB->query ($sql);
                 $allowed_mimetypes = '';
                 while ($arrFile = $xoopsDB->fetchArray ($resultFiles)) {

                 $filename = XOOPS_ROOT_PATH . "/modules/wfsection/cache/uploaded/" . $arrFile['filerealname'];
                 if (XoopsLoad::fileExists($filename)) {
                 if (copy($filename, XOOPS_ROOT_PATH . "/uploads/publisher/" . $arrFile['filerealname'])) {
                 $fileObj = $publisher_file_handler->create();
                 $fileObj->setVar('name', $arrFile['fileshowname']);
                 $fileObj->setVar('description', $arrFile['filedescript']);
                 $fileObj->setVar('status', _PUBLISHER_STATUS_FILE_ACTIVE);
                 $fileObj->setVar('uid', $arrArticle['uid']);
                 $fileObj->setVar('itemid', $itemObj->getVar('itemid'));
                 $fileObj->setVar('mimetype', $arrFile['minetype']);
                 $fileObj->setVar('datesub', $arrFile['date']);
                 $fileObj->setVar('counter', $arrFile['counter']);
                 $fileObj->setVar('filename', $arrFile['filerealname']);

                 if ($fileObj->store($allowed_mimetypes, true, false)) {
                 echo "&nbsp;&nbsp;&nbsp;&nbsp;"  . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE_FILE, $arrFile['filerealname']) . "<br />";
                 }
                 }
                 }
                 }
                 */
                $newArticleArray[$arrArticle['storyid']] = $itemObj->getVar('itemid');
                echo "&nbsp;&nbsp;" . sprintf(_AM_PUBLISHER_IMPORTED_ARTICLE, $itemObj->title()) . "<br />";
                $cnt_imported_articles++;
            }
        }


        // Saving category permissions
        $groupsIds = $gperm_handler->getGroupIds('news_view', $arrCat['topic_id'], $news_module_id);
        PublisherUtils::saveCategoryPermissions($groupsIds, $categoryObj->getVar('categoryid'), 'category_read');
        $groupsIds = $gperm_handler->getGroupIds('news_submit', $arrCat['topic_id'], $news_module_id);
        PublisherUtils::saveCategoryPermissions($groupsIds, $categoryObj->getVar('categoryid'), 'item_submit');

        $newCatArray[$newCat['oldid']] = $newCat;
        unset($newCat);
        echo "<br/>";
    }

    // Looping through cat to change the parentid to the new parentid
    foreach ($newCatArray as $oldid => $newCat) {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('categoryid', $newCat['newid']));
        $oldpid = $newCat['oldpid'];
        if ($oldpid == 0) {
            $newpid = $parentId;
        } else {
            $newpid = $newCatArray[$oldpid]['newid'];
        }
        $publisher->getCategoryHandler()->updateAll('parentid', $newpid, $criteria);
        unset($criteria);
    }

    // Looping through the comments to link them to the new articles and module
    echo _AM_PUBLISHER_IMPORT_COMMENTS . "<br />";

    $publisher_module_id = $publisher->getModule()->mid();

    $comment_handler = xoops_gethandler('comment');
    $criteria = new CriteriaCompo();
    $criteria->add(new Criteria('com_modid', $news_module_id));
    $comments = $comment_handler->getObjects($criteria);
    foreach ($comments as $comment) {
        $comment->setVar('com_itemid', $newArticleArray[$comment->getVar('com_itemid')]);
        $comment->setVar('com_modid', $publisher_module_id);
        $comment->setNew();
        if (!$comment_handler->insert($comment)) {
            echo "&nbsp;&nbsp;" . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT_ERROR, $comment->getVar('com_title')) . "<br />";
        } else {
            echo "&nbsp;&nbsp;" . sprintf(_AM_PUBLISHER_IMPORTED_COMMENT, $comment->getVar('com_title')) . "<br />";
        }

    }

    echo "<br/><br/>Done.<br/>";
    echo sprintf(_AM_PUBLISHER_IMPORTED_CATEGORIES, $cnt_imported_cat) . "<br/>";
    echo sprintf(_AM_PUBLISHER_IMPORTED_ARTICLES, $cnt_imported_articles) . "<br/>";
    echo "<br/><a href='" . PUBLISHER_URL . "/'>" . _AM_PUBLISHER_IMPORT_GOTOMODULE . "</a><br/>";

    PublisherUtils::closeCollapsableBar('newsimportgo', 'newsimportgoicon');
    $xoops->footer();
}

?>
