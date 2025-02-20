<?php
if (!defined('MODX_BASE_PATH') || strpos(str_replace('\\', '/', __FILE__), MODX_BASE_PATH) !== 0) exit;
/*
 * Title: Tagging
 * Purpose:
 *  	Collection of parameters, functions, and classes that expand
 *  	Ditto's functionality to include tagging
*/

// ---------------------------------------------------
// Tagging Parameters
// ---------------------------------------------------

$landing = $tagDocumentID ?? $modx->documentObject['id'];
/*
	Param: tagDocumentID

	Purpose:
 	ID for tag links to point to

	Options:
	Any MODX document with a Ditto call setup to receive the tags

	Default:
	Current MODX Document
*/
$source = $tagData ?? '';
/*
	Param: tagData

	Purpose:
 	Field to get the tags from

	Options:
	Comma separated list of MODX fields or TVs

	Default:
	[NULL]
*/
$caseSensitive = $caseSensitive ?? 0;
/*
	Param: caseSensitive

	Purpose:
 	Determine whether or not tag matching and duplicate tag removal are case sensitive

	Options:
	0 - off
	1 - on

	Default:
	0 - off
*/
$mode = $tagMode ?? 'onlyTags';
/*
	Param: tagMode

	Purpose:
 	Filtering method to remove tags

	Options:
	onlyAllTags - show documents that have all of the tags
	onlyTags - show documents that have any of the tags
	removeAllTags - remove documents that have all of the tags
	removeTags - documents that have any of the tags

	Default:
	"onlyTags"
*/
$delimiter = $tagDelimiter ?? ' ';
/*
	Param: tagDelimiter

	Purpose:
 	Delimiter that splits each tag in the tagData source

	Options:
	Any character not included in the tags themselves

	Default:
	" " - space
*/
$displayDelimiter = $tagDisplayDelimiter ?? $delimiter;
/*
	Param: tagDisplayDelimiter

	Purpose:
 	What separates the tags in [+tagLinks+]

	Options:
	Any character

	Default:
	&tagDelimiter
*/
$sort = $tagSort ?? 1;
/*
	Param: tagSort

	Purpose:
 	Sort the tags alphanumerically

	Options:
	0 - off
	1 - on

	Default:
	1 - on
*/
$displayMode = $tagDisplayMode ?? 1;
/*
	Param: tagDisplayMode

	Purpose:
 	How to display the tags in [+tagLinks+]

	Options:
	1 - string of links &tagDisplayDelimiter separated
	2 - ul/li list

	Note:
	Output of individual items can be customized by <tplTagLinks>

	Default:
	1 - string of links &tagDisplayDelimiter separated
*/
$givenTags = !empty($tags) ? trim($tags) : false;
/*
	Param: tags

	Purpose:
 	Allow the user to provide initial tags to be filtered

	Options:
	Any valid tags separated by <tagDelimiter>

	Default:
	[NULL]
*/
$templateInstance = new template();
$tplTagLinks = !empty($tplTagLinks) ? $templateInstance->fetch($tplTagLinks) : false;
/*
	Param: tplTagLinks

	Purpose:
 	Define a custom template for the tagLinks placeholder

	Options:
	- Any valid chunk name
	- Code via @CODE
	- File via @FILE

	Default:
	(code)
	<a href="[+url+]" class="ditto_tag" rel="tag">[+tag+]</a>
*/
$callback = !empty($tagCallback) ? trim($tagCallback) : false;
/*
	Param: tagCallback

	Purpose:
 	Allow the user to modify both where the tags come from and how they are parsed.

	Options:
	Any valid function name

	Default:
	[NULL]

	Notes:
	The function should expect to receive the following three parameters:
	tagData - the provided source of the tags
	resource - the resource array for the document being parsed
	array - return the results in an array if true
*/

// ---------------------------------------------------
// Tagging Class
// ---------------------------------------------------
if (!class_exists('tagging')) {
    class tagging
    {
        var $delimiter, $source, $landing, $mode, $format, $givenTags, $caseSensitive, $displayDelimiter, $sort, $displayMode, $tpl, $callback;

        function __construct($delimiter, $source, $mode, $landing, $givenTags, $format, $caseSensitive, $displayDelimiter, $callback, $sort, $displayMode, $tpl)
        {
            $this->delimiter = $delimiter;
            $this->source = $this->parseTagData($source);
            $this->mode = $mode;
            $this->landing = $landing;
            $this->format = $format;
            $this->givenTags = $this->prepGivenTags($givenTags);
            $this->caseSensitive = $caseSensitive;
            $this->displayDelimiter = $displayDelimiter;
            $this->sort = $sort;
            $this->displayMode = $displayMode;
            $this->tpl = $tpl;
            $this->callback = $callback;
        }

        function prepGivenTags($givenTags)
        {
            global $dittoID;

            $getTags = getv($dittoID . 'tags') ? trim(getv($dittoID . 'tags')) : false;
            // Get tags from the getv array

            $tags1 = [];
            $tags2 = [];

            if ($getTags !== false) {
                $tags1 = explode($this->delimiter, $getTags);
            }

            if ($givenTags !== false) {
                $tags2 = explode($this->delimiter, $givenTags);
            }

            $kTags = [];
            $tags = array_merge($tags1, $tags2);
            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    if ($this->caseSensitive) {
                        $kTags[trim($tag)] = trim($tag);
                    } else {
                        $kTags[strtolower(trim($tag))] = trim($tag);
                    }
                }
            }
            return $kTags;
        }

        function tagFilter($value)
        {
            if ($this->caseSensitive == false) {
                $documentTags = array_values(array_flip($this->givenTags));
                $filterTags = array_values(array_flip($this->combineTags($this->source, $value, true)));
            } else {
                $documentTags = $this->givenTags;
                $filterTags = $this->combineTags($this->source, $value, true);
            }
            $compare = array_intersect($filterTags, $documentTags);
            $commonTags = count($compare);
            $totalTags = count((array)$filterTags);
            $docTags = count($documentTags);
            $unset = 1;

            switch ($this->mode) {
                case 'onlyAllTags' :
                    if ($commonTags != $docTags)
                        $unset = 0;
                    break;
                case 'removeAllTags' :
                    if ($commonTags == $docTags)
                        $unset = 0;
                    break;
                case 'onlyTags' :
                    if ($commonTags > $totalTags || $commonTags == 0)
                        $unset = 0;
                    break;
                case 'removeTags' :
                    if ($commonTags <= $totalTags && $commonTags != 0)
                        $unset = 0;
                    break;
            }
            return $unset;
        }

        function makeLinks($resource)
        {
            return $this->tagLinks($this->combineTags($this->source, $resource, true), $this->delimiter, $this->landing, $this->format);
        }

        function parseTagData($tagData, $names = [])
        {
            return explode(',', $tagData);
        }

        function combineTags($tagData, $resource, $array = false)
        {
            if ($this->callback !== false) {
                return call_user_func_array($this->callback, array('tagData' => $tagData, 'resource' => $resource, 'array' => $array));
            }
            $tags = [];
            foreach ($tagData as $source) {
                if (!empty($resource[$source])) {
                    $tags[] = $resource[$source];
                }
            }
            $kTags = [];
            $tags = explode($this->delimiter, implode($this->delimiter, $tags));
            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    if ($this->caseSensitive) {
                        $kTags[trim($tag)] = trim($tag);
                    } else {
                        $kTags[strtolower(trim($tag))] = trim($tag);
                    }
                }
            }
            return ($array == true) ? $kTags : implode($this->delimiter, $kTags);
        }

        function tagLinks($tags, $tagDelimiter, $tagID = false, $format = 'html')
        {
            global $ditto_lang, $modx, $templates;
            if (!$tags && $format === 'html') {
                return $ditto_lang['none'];
            }

            if (!$tags && ($format === 'rss' || $format === 'xml' || $format === 'xml')) {
                return sprintf('<category>%s</category>', $ditto_lang['none']);
            }

            $output = '';
            if ($this->sort) {
                ksort($tags);
            }

            // set templates array
            $tplRss = "\r\n" . '				<category>[+tag+]</category>';
            $tpl = ($this->tpl == false) ? '<a href="[+url+]" class="ditto_tag" rel="tag">[+tag+]</a>' : $this->tpl;

            $tpl = (($format === 'rss' || $format === 'xml' || $format === 'atom') && $templates['user'] == false) ? $tplRss : $tpl;

            if ($this->displayMode == 1) {
                foreach ($tags as $tag) {
                    $tagDocID = (!$tagID) ? $modx->documentObject['id'] : $tagID;
                    $url = ditto::buildURL("tags={$tag}&start=0", $tagDocID);
                    $output .= template::replace(array('url' => $url, 'tag' => $tag), $tpl);
                    $output .= ($format !== 'rss' && $format !== 'xml' && $format !== 'atom') ? $this->displayDelimiter : '';
                }
            } else if ($format !== 'rss' && $format !== 'xml' && $format !== 'atom' && $this->displayMode == 2) {
                $tagList = [];
                foreach ($tags as $tag) {
                    $tagDocID = (!$tagID) ? $modx->documentObject['id'] : $tagID;
                    $url = ditto::buildURL("tags={$tag}&start=0", $tagDocID);
                    $tagList[] = template::replace(array('url' => $url, 'tag' => $tag), $tpl);
                }
                $output = $this->makeList($tagList, $ulroot = 'ditto_tag_list', $ulprefix = 'ditto_tag_', $type = '', $ordered = false, $tablevel = 0);
            }

            return ($format !== 'rss' && $format !== 'xml' && $format !== 'atom') ? substr($output, 0, -1 * strlen($this->displayDelimiter)) : $output;
        }

        function makeList($array, $ulroot = 'root', $ulprefix = 'sub_', $type = '', $ordered = false, $tablevel = 0)
        {
            // first find out whether the value passed is an array
            if (!is_array($array)) return '<ul><li>Bad list</li></ul>';

            $tabs = '';
            for ($i = 0; $i < $tablevel; $i++) {
                $tabs .= "\t";
            }

            $tag = ($ordered == true) ? 'ol' : 'ul';

            if (!empty($type)) $typestr = " style='list-style-type: {$type}'";
            else              $typestr = '';

            $listhtml = "{$tabs}<{$tag} class='{$ulroot}'{$typestr}>\n";
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $line = $this->makeList($value, "{$ulprefix}{$ulroot}", $ulprefix, $type, $ordered, $tablevel + 2);
                    $listhtml .= "{$tabs}\t<li>{$key}\n{$line}{$tabs}\t</li>\n";
                } else {
                    $listhtml .= "{$tabs}\t<li>{$value}</li>\n";
                }
            }
            $listhtml = "{$tabs}</{$tag}>\n";
            return $listhtml;
        }
    }
}

// ---------------------------------------------------
// Tagging Parameters
// ---------------------------------------------------

$tags = new tagging($delimiter, $source, $mode, $landing, $givenTags, $format, $caseSensitive, $displayDelimiter, $callback, $sort, $displayMode, $tplTagLinks);

if ($tags->givenTags) {
    $filters['custom']['tagging'] = array($source, array($tags, 'tagFilter'));
    // set tagging custom filter
}

//generate TagList
$modx->setPlaceholder($dittoID . 'tagLinks', $tags->tagLinks($tags->givenTags, $delimiter, $landing, $format));
/*
	Placeholder: tagLinks

	Content:
	Nice 'n beautiful tag list with links pointing to <tagDocumentID>
*/
// set raw tags placeholder
$modx->setPlaceholder($dittoID . 'tags', implode($delimiter, $tags->givenTags));
/*
	Placeholder: tags

	Content:
	Raw tags separated by <tagDelimiter>
*/
// set tagging placeholder
$placeholders['tagLinks'] = array(array($source, '*'), array($tags, 'makeLinks'));
