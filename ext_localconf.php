<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TYPO3_CONF_VARS['EXTCONF']['indexed_search']['pi1_hooks']['initialize_postProc'] = 'EXT:pmkisac/class.tx_pmkisac_init.php:&tx_pmkisac_init';
$TYPO3_CONF_VARS['FE']['eID_include']['pmkisac'] = 'EXT:pmkisac/class.tx_pmkisac_autocomplete.php';

// Small wrapper function for adding PMKISAC JavaScript from a TypoScript USER object. 
if (!function_exists('user_pmkisac')) {
	function user_pmkisac($content,$conf) {
		require_once(t3lib_extMgm::extPath('pmkisac').'class.tx_pmkisac_init.php');
		$pmkisac = t3lib_div::makeInstance('tx_pmkisac_init');
		$pmkisac->setup($conf['userFunc.']);
		return $content;
	}
}
?>