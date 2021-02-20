<?php

/*
 * Plugin Name: Entry Cover
 * Version: 0.1
 * Plugin URI: #
 * Author: Francisco Arocas
 * Author URI: https://www.franciscoarocas.com
 * Description: Show a cover on each blog post
 */


DEFINE("PLUGIN_NAME", "entrycover");
DEFINE("COVER_FOLDER", FP_CONTENT . "covers/");
DEFINE("NO_COVER", "--");
DEFINE("RESPONSIVE_IMAGE", "width: 100%; height: auto");


/* Load cover options */
function loadSettings() {
	return plugin_getoptions(PLUGIN_NAME);
}


DEFINE("PLUGIN_SETTINGS", loadSettings());


/* Get all images uploaded by user. Also add No Cover option*/
function getImagesFileName() {
	$indexer = new fs_filelister(IMAGES_DIR); // get all available images
	$imageslist = $indexer->getList();
	sort($imageslist);
	array_unshift($imageslist, NO_COVER);
	return $imageslist;
}


/* Called after load cover file. It gets the image src from cover file data json*/
function getImageSrcFromCoverFile($coverFileContent) {
	if($coverFileContent != null) {
		$coverFileContent = json_decode($coverFileContent);
		if($coverFileContent->imageFileName) {
			return $coverFileContent->imageFileName;
		}
	}
	return null;
}

/* Generate the cover file NAME. Important: Generate the name of the file */
function generateCoverFileName($entryID) {
	return "cover" . substr($entryID, 5, strlen($entryID));
}

function generateFullImageLink($imageName) {
	return BLOG_BASEURL . FP_CONTENT . "images/" . $imageName;
}

/* Show entry cover widget in the entry editor */
function pluginEntryCoverEditor() {
	global $_FP_SMARTY;
	$imagesList = getImagesFileName();
	$_FP_SMARTY->assign('images_list', $imagesList);

	$entryID = $_FP_SMARTY->_tpl_vars['id'];
	$coverFileContent = null;

	if($entryID) {
		$coverID = generateCoverFileName($entryID);
		$coverFileContent = loadCover($coverID);
	}

	$imgSrc = getImageSrcFromCoverFile($coverFileContent);
	showEntryLegend($imagesList, $imgSrc);
}


/* Show the label with select in the entry editor */
function showEntryLegend($imageslist, $coverImage) {
	if(count($imageslist) == 1) {
		echo "Please upload an image in the Upload Page, before choose a cover\n";
	}

	echo "<script>";
	echo "const entryCoverImagesPath = \"" . BLOG_BASEURL . FP_CONTENT . "images/" . "\";";
	echo "const entryCoverNoCoverOption = \"" . NO_COVER . "\";</script>";
	echo "<script src=\"" . plugin_getdir(PLUGIN_NAME) . "res/" . PLUGIN_NAME . ".js\"></script>";
	echo "<fieldset id=\"entryCoverFieldSet\">\n";
	echo "<legend>Entry Cover</legend>";
	echo "<select name=\"flags[entryCover]\" id=\"entryCoverSelect\" style=\"width:100%;\" onchange=\"entryCoverSelectChange()\">";

	foreach($imageslist as $image) {
		echo "<option value=\"" . $image . "\"" . ($coverImage == $image ? " selected" : "") . ">" . $image . "</option>";
	}

	echo "</select><p></p><div id=\"entryCoverImage\">";
	if($coverImage) {
		echo "<img src=\"" . FP_CONTENT . "images/" . $coverImage . "\" style=\"" . RESPONSIVE_IMAGE . "\">";
	}
	echo "</div></fieldset>\n";
}


/* Check if Cover folder exits in the fp-content directory */
function checkCoverFolder() {
	if(!is_dir(COVER_FOLDER)) {
		fs_mkdir(COVER_FOLDER);
	}
}


/* Save a Cover.txt file. Its contain the entry id (in namefile) and the img src*/
function saveCover($coverID, $coverFileName = null) {
	$fileData = array();
	$fileData['imageFileName'] = $coverFileName;
	io_write_file(COVER_FOLDER . $coverID . ".txt", json_encode($fileData));
}


/* Load cover with entryID (Example: entry12931-21342)*/
function loadCover($coverID) {
	return io_load_file(COVER_FOLDER . $coverID . ".txt");
}

// Save entry cover image filename 
// $coverName = string with cover filename
function editorSaveCover($id, $entry) {
	if(isset($entry['categories'])) {
		$entryCoverFlag = null;
		foreach($entry['categories'] as $flag) {
			$matches;
			if(preg_match('/^entryCover(.*)$/', $flag, $matches)) {
				$entryCoverFlag = $matches[1];
				$entryCoverFlag = substr($entryCoverFlag, 1, strlen($entryCoverFlag) - 2);
			}
		}
		if(!$entryCoverFlag) return;
		$coverID = generateCoverFileName($id);
		if($entryCoverFlag != NO_COVER) {
			saveCover($coverID, $entryCoverFlag);
		} else {
			saveCover($coverID);
		}
	}
}

// Create smarty function to show the cover in the template

global $smarty;

$smarty->register_function('cover', 'showEntryCoverImageSrc');

/* Return the img src */
function showEntryCoverImageSrc($params, &$smarty) {
	if(isset(PLUGIN_SETTINGS['allowTag']) && PLUGIN_SETTINGS['allowTag']) return null; // If we allow [cover] tag in entries, we dont show the cover in the theme, just in the entry content
	$entryID = $smarty->_tpl_vars['id'];
	$coverID = generateCoverFileName($entryID);
	$coverData = loadCover($coverID);
	$coverSrc = getImageSrcFromCoverFile($coverData);
	if($coverSrc != null) return generateFullImageLink($coverSrc);
	return null;
}

// must be admin area and must be right panel and must be right action

if (class_exists('AdminPanelAction')) {

	include (plugin_getdir(PLUGIN_NAME) . '/panels/panel.entrycover.php');
}

add_filter('simple_edit_form', 'pluginEntryCoverEditor', 1);

add_filter('publish_post', 'editorSaveCover', 0, 2);


// COVER TAG PLUGIN (BBCODE)
// https://wiki.flatpress.org/doc:plugins:bbcode:tips

add_filter('init', 'entrycoverCustombbcodeTags');
 
// here you define a function. In this case we're creating an acronym tag
 
function entrycoverCustombbcodeTags() {
        $bbcode = plugin_bbcode_init();
        $bbcode->addCode (
                    'cover',  // tag name: this will go between square brackets
                    'callback_replace', // type of action: we'll use a callback function
                    'coverTagBBcode', // name of the callback function
                    array(), 
                    'inline', // type of the tag, inline or block, etc
                    array ( 'block', 'inline'), // type of elements in which you can use this tag
                    array ()); // type of elements where this tag CAN'T go (in this case, none, so it can go everywhere)

}
 
// $content is the text between the two tags, i.e. [tag]CONTAINED TEXT[/tag] $content='CONTAINED TEXT'
// $attributes is an associative array where keys are the tag properties. default is the [tagname=value] property
 
function coverTagBBcode($action, $attributes, $content, $params, $node_object) {
	global $smarty;
	if(isset($smarty->_tpl_vars) && isset($smarty->_tpl_vars['id']) && isset(PLUGIN_SETTINGS['allowTag']) && PLUGIN_SETTINGS['allowTag']) {
		$entryID = generateCoverFileName($smarty->_tpl_vars['id']);
		$coverData = loadCover($entryID);
		$imageSrc = getImageSrcFromCoverFile($coverData);
		if($imageSrc != null) {
			return "<img src = \"" . generateFullImageLink($imageSrc) . "\" style=\"" . RESPONSIVE_IMAGE . "\">";
		}
	}
	return " ";
}