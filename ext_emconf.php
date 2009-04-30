<?php

########################################################################
# Extension Manager/Repository config file for ext: "pmkisac"
#
# Auto generated 30-04-2009 18:46
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'PMK Indexed Search Auto completer',
	'description' => 'AJAX based autocompleter for Indexed Search. Multiple word lookup and other features. All configurable using TypoScript. No XCLASS as it uses hook in Indexed Search. Supports Multiple JS frameworks: Mootools, JQuery & Prototype',
	'category' => 'fe',
	'author' => 'Peter Klein',
	'author_email' => 'peter@umloud.dk',
	'shy' => '',
	'dependencies' => 'indexed_search',
	'conflicts' => 'cb_indexedsearch_autocomplete,ods_autocomplete',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.3.0',
	'constraints' => array(
		'depends' => array(
			'indexed_search' => '2.0.0-0.0.0',
			'typo3' => '4.0-0.0.0',
			'php' => '4.1.0-0.0.0',
		),
		'conflicts' => array(
			'cb_indexedsearch_autocomplete' => '0.0.1-0.0.0',
			'ods_autocomplete' => '0.0.1-0.0.0',
		),
		'suggests' => array(
			't3mootools' => '1.0.0-0.0.0',
		),
	),
	'_md5_values_when_last_written' => 'a:23:{s:9:"ChangeLog";s:4:"2253";s:10:"README.txt";s:4:"ee2d";s:33:"class.tx_pmkisac_autocomplete.php";s:4:"27f5";s:25:"class.tx_pmkisac_init.php";s:4:"7463";s:12:"ext_icon.gif";s:4:"4cbf";s:17:"ext_localconf.php";s:4:"de7c";s:14:"ext_tables.php";s:4:"5655";s:14:"t3mootools.txt";s:4:"3797";s:14:"doc/manual.sxw";s:4:"3ee4";s:21:"res/Autocompleter.css";s:4:"bf58";s:21:"res/images/shadow.png";s:4:"b5b2";s:22:"res/images/spinner.gif";s:4:"0483";s:27:"res/images/spinner12x12.gif";s:4:"fa5f";s:26:"res/jquery/autocomplete.js";s:4:"0c12";s:30:"res/jquery/jquery-1.3.2.min.js";s:4:"bb38";s:29:"res/mootools/Autocompleter.js";s:4:"4216";s:42:"res/mootools/Autocompleter_uncompressed.js";s:4:"c13e";s:31:"res/mootools/mootools_v1.2.1.js";s:4:"1503";s:29:"res/prototype/autocomplete.js";s:4:"7c98";s:32:"res/template/indexed_search.tmpl";s:4:"43e7";s:30:"res/template/template_css.tmpl";s:4:"ada0";s:33:"static/autocomplete/constants.txt";s:4:"c38c";s:29:"static/autocomplete/setup.txt";s:4:"b567";}',
	'suggests' => array(
	),
);

?>