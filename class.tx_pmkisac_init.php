<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Peter Klein <peter@umloud.dk>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
* Plugin 'init' for the 'pmkisac' extension.
*
* @author    Peter Klein <peter@umloud.dk>
* @package    TYPO3
* @subpackage    tx_pmkisac
*/


// checks if t3mootools is loaded
if (t3lib_extMgm::isLoaded('t3mootools')) {
	require_once(t3lib_extMgm::extPath('t3mootools').'class.tx_t3mootools.php');
}

/**
 * Setup class for PMK Index Search Auto Completer
 *
 */
class tx_pmkisac_init {
	var $extKey = 'pmkisac';

	/**
	 * Hook from indexed_search
	 *
	 * @return	void
	 */
	function initialize_postProc() {
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_'.$this->extKey.'.'];
		//$conf['is_defpivars'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_indexedsearch.']['_DEFAULT_PI_VARS.'];
		return $this->setup($conf);
	}

	/**
	 * Typoscript User function
	 *
	 * @param	string		$content: ...
	 * @param	array		$conf: ...
	 * @return	void
	 */
	function addHeaderData($content,$conf) {
		return $this->setup($conf['userFunc.']);
	}

	/**
	 * Add the JavaScript and CSS stylesheet
	 *
	 * @param	array		Plugin config array.
	 * @return	void
	 */
	function setup($conf) {
		// Process config data
		$minLength = intval($conf['minLength']);
		$minLength = $minLength ? $minLength : 2;
		$maxChoices = intval($conf['maxChoices']);
		$maxChoices = $maxChoices ? $maxChoices : 10;
		$allowMulti = intval($conf['allowMulti']);
		$allowMulti = $allowMulti ? 1 : 0;
		$markQuery = intval($conf['markQuery']);
		$markQuery = ($markQuery && !$allowMulti) ? 1 : 0;
		$inheritWidth = intval($conf['inheritWidth']);
		$inheritWidth = $inheritWidth ? 1 : 0;
		$dropDownWidth = intval($conf['dropDownWidth']);
		$progressIndicator = intval($conf['progressIndicator']);
		$startingWordOnly = intval($conf['startingWordOnly']);
		$startingWordOnly = $startingWordOnly ? 1 : 0;
		$useSelection = intval($conf['useSelection']);
		$useSelection = ($useSelection && !$allowMulti && $startingWordOnly) ? 1 : 0;
		$showWordcount = intval($conf['showWordcount']);
		$showWordcount = $showWordcount ? 1 : 0;
		$autoSubmit = intval($conf['autoSubmit']);
		$autoSubmit = ($autoSubmit && !$allowMulti) ? 1 : 0;
		$cssFile = trim($conf['cssFile']);
		$cssId = trim($conf['cssId']);
		$cssId = $cssId ? $cssId : 'tx-indexedsearch-searchbox-sword';
		$sectionInputId = trim($conf['sectionInputId']);
		$sectionInputId = $sectionInputId ? $sectionInputId : 'tx-indexedsearch-selectbox-sectionsxx';
		$languageInputId = trim($conf['languageInputId']);
		$languageInputId = $languageInputId ? $languageInputId : 'tx-indexedsearch-selectbox-langxx';
		$mediaInputId = trim($conf['mediaInputId']);
		$mediaInputId = $mediaInputId ? $mediaInputId : 'tx-indexedsearch-selectbox-media';

		if (isset($GLOBALS['TSFE']->config['config']['sys_language_uid']) && !$conf['allLang']) {
			$languageid = intval($GLOBALS['TSFE']->config['config']['sys_language_uid']);
		}
		else {
			$languageid = -1;
		}

		if (defined('T3MOOTOOLS')) {
			// if t3mootools is loaded and the custom Library had been created, then include it
			tx_t3mootools::addMooJS();
		}
		else {
			// otherwise just include the predefined Mootools library
			$headerData .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/mootoolsv1.11.js"></script>';
		}

		// Include shared JS
		$headerData .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/Autocompleter_uncompressed.js"></script>';

		// Include CSS if present
		if ($cssFile) $headerData .= "\n".'<link rel="stylesheet" type="text/css" href="'.$cssFile.'" />';

		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = $headerData;

		// Include cssID specific JS
		$headerData = '<script type="text/javascript">
// Test Version2
			window.addEvent("domready", function(){
				var el = $("'.$cssId.'");
				if (el) {
					var form = el;
					for (var i=0;i<20;i++) {
						form = form.getParent();
						if (form.nodeName=="FORM") break;
					}
					var sectionpid = 0;
					var section = $("'.$sectionInputId.'");
					if ($type(section)=="element") {
						sectionpid = section.getValue();
					}
					var languageid = '.$languageid.';
					var language = $("'.$languageInputId.'");
					if ($type(language)=="element") {
						languageid = language.getValue();
					}
					var mediaid = -1;
					var media = $("'.$mediaInputId.'");
					if ($type(media)=="element") {
						mediaid = media.getValue();
					}
					var ajaxurl = "index.php?eID='.$this->extKey.'";
					'.($progressIndicator===1 ? 'var indicator = new Element("div", {"class": "autocompleter-loading", "styles": {"display": "none"}}).setHTML("").injectAfter(el);' : '').'
					var completer = new Autocompleter.Ajax.Xhtml(el, ajaxurl , {
						"postData": {id: '.$GLOBALS['TSFE']->id.',sp: sectionpid,la: languageid,me: mediaid,sw: '.$startingWordOnly.',ml: '.$minLength.',mc: '.$maxChoices.', wc: '.$showWordcount.'},
						"minLength": '.$minLength.',
						"maxChoices": '.$maxChoices.',
						"useSelection": '.$useSelection.',
						"markQuery": '.$markQuery.',
						"inheritWidth": '.$inheritWidth.',
						"dropDownWidth": '.$dropDownWidth.',
						"multi": '.$allowMulti.',
						"delimeter": " ",
						'.($autoSubmit ? '"onSelect": function() {
							if (form.nodeName=="FORM") form.submit();
						},' : '')
						.'"delay": 100,
						'.($progressIndicator? '"onRequest": function(el) {
							'.($progressIndicator===1 ? 'indicator.setStyle("display", "");' : 'el.addClass("autocompleter-loading2");').'
						},':'').'
						'.($progressIndicator? '"onComplete": function(el) {
							'.($progressIndicator===1 ? 'indicator.setStyle("display", "none");' : 'el.removeClass("autocompleter-loading2");').'
						},':'').'
						"parseChoices": function(el) {
							var value = el.getFirst().innerHTML;
							el.inputValue = value;
							this.addChoiceEvents(el).getFirst().setHTML(this.markQueryValue(value));
						}
					});

					if ($type(section)=="element") {
						section.addEvent("change", function(){
							completer.options.postData["sp"] = section.getValue();
						});
					}
					if ($type(language)=="element") {
						language.addEvent("change", function(){
							completer.options.postData["la"] = language.getValue();
						});
					}
					if ($type(media)=="element") {
						media.addEvent("change", function(){
							completer.options.postData["me"] = media.getValue();
						});
					}
				}
			});
		</script>';

		$GLOBALS['TSFE']->additionalHeaderData[$cssId] = $headerData;

		return '';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pmkisac/class.tx_pmkisac_init.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pmkisac/class.tx_pmkisac_init.php']);
}

?>
