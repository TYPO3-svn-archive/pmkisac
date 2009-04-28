<?php

########################################################################
# Extension Manager/Repository config file for ext: "pmkisac"
#
# Auto generated 26-04-2009 20:35
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'PMK Indexed Search Auto completer',
	'description' => 'Mootools AJAX based autocompleter for Indexed Search. Multiple word lookup and other features. All configurable using TypoScript. No XCLASS as it uses hook in Indexed Search.',
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
	'version' => '1.2.1',
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
	'_md5_values_when_last_written' => 'a:19:{s:9:"ChangeLog";s:4:"2253";s:10:"README.txt";s:4:"ee2d";s:33:"class.tx_pmkisac_autocomplete.php";s:4:"5cc8";s:25:"class.tx_pmkisac_init.php";s:4:"0b33";s:12:"ext_icon.gif";s:4:"4cbf";s:17:"ext_localconf.php";s:4:"de7c";s:14:"ext_tables.php";s:4:"5655";s:14:"t3mootools.txt";s:4:"bf5c";s:14:"doc/manual.sxw";s:4:"4120";s:21:"res/Autocompleter.css";s:4:"8484";s:20:"res/Autocompleter.js";s:4:"46f2";s:33:"res/Autocompleter_uncompressed.js";s:4:"60fe";s:20:"res/mootoolsv1.11.js";s:4:"e3b5";s:22:"res/images/spinner.gif";s:4:"0483";s:27:"res/images/spinner12x12.gif";s:4:"fa5f";s:32:"res/template/indexed_search.tmpl";s:4:"43e7";s:30:"res/template/template_css.tmpl";s:4:"ada0";s:33:"static/autocomplete/constants.txt";s:4:"d320";s:29:"static/autocomplete/setup.txt";s:4:"6dce";}',
	'suggests' => array(
	),
);

?>