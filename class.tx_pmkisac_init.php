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
		$this->config = $this->getConfig($conf);
		switch ($this->config['framework']) {
			case 'jquery':
				$this->setupJQuery();
			break;
			case 'prototype':
				$this->setupPrototype();
			break;
			default:
				$this->setupMootools();
			break;
		}
	}
	
	/**
	 * Add the Mootools JavaScript and CSS stylesheet
	 *
	 * @param	void
	 * @return	void
	 */
	function setupMootools() {
		if (defined('T3MOOTOOLS')) {
			// if t3mootools is loaded and the custom Library had been created, then include it
			tx_t3mootools::addMooJS();
		}
		else {
			// otherwise just include the predefined Mootools library
			$headerData .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/mootools/mootools_v1.2.1.js"></script>';
		}
		
		// Include shared JS
		$headerData .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/mootools/Autocompleter.js"></script>';
		
		// Include CSS if present
		if ($this->config['cssFile']) $headerData .= "\n".'<link rel="stylesheet" type="text/css" href="'.$this->config['cssFile'].'" />';
		
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = $headerData;
		
		// Include cssID specific JS
		$headerData = '<script type="text/javascript">
/* <![CDATA[ */
window.addEvent("domready", function(){
	var el = $("'.$this->config['cssId'].'");
	if (el) {
		var form = el;
		for (var i=0;i<20;i++) {
			form = form.getParent();
			if (form.nodeName=="FORM") break;
		}
		var sectionpid = 0;
		var section = $("'.$this->config['sectionInputId'].'");
		if ($type(section)=="element") {
			sectionpid = section.getValue();
		}
		var languageid = '.$this->config['languageid'].';
		var language = $("'.$this->config['languageInputId'].'");
		if ($type(language)=="element") {
			languageid = language.getValue();
		}
		var mediaid = -1;
		var media = $("'.$this->config['mediaInputId'].'");
		if ($type(media)=="element") {
			mediaid = media.getValue();
		}
		var ajaxurl = "index.php?eID='.$this->extKey.'";
		var completer = new Autocompleter.Request.HTML(el, ajaxurl , {
			"postData": {id: '.$GLOBALS['TSFE']->id.',sp: sectionpid,la: languageid,me: mediaid,sw: '.$this->config['startingWordOnly'].',ml: '.$this->config['minLength'].',mc: '.$this->config['maxChoices'].', wc: '.$this->config['showWordcount'].'},
			"minLength": '.$this->config['minLength'].',
			"maxChoices": '.$this->config['maxChoices'].',
			"useSelection": '.$this->config['useSelection'].',
			"markQuery": '.$this->config['markQuery'].',
			"inheritWidth": '.$this->config['inheritWidth'].',
			"dropDownWidth": '.$this->config['dropDownWidth'].',
			"multi": '.$this->config['allowMulti'].',
			"delimeter": " ",
			'.($this->config['autoSubmit'] ? '"onSelect": function() {
				if (form.nodeName=="FORM") form.submit();
			},' : '')
			.'"delay": 100,
			'.($this->config['progressIndicator'] ? '"indicatorClass": "autocompleter-loading",':'').'
			"injectChoice": function(choice) {
				var text = choice.getFirst();
				var value = text.innerHTML;
				choice.inputValue = value;
				text.set("html", this.markQueryValue(value));
				this.addChoiceEvents(choice);
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
/* ]]> */
</script>';
		$GLOBALS['TSFE']->additionalHeaderData[$this->config['cssId']] = $headerData;
	}

	/**
	 * Add the Prototype JavaScript and CSS stylesheet
	 *
	 * @param	void
	 * @return	void
	 */
	function setupPrototype() {
		// Process config data
		
		$headerData = '';
		// Include the prototype library
		if ($this->config['includeFramework']) {
			$headerData .= '<script type="text/javascript" src="typo3/contrib/prototype/prototype.js"></script>';
		}
		
		// Include shared JS
		$headerData .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/prototype/autocomplete.js"></script>';
		
		$cssFile = t3lib_extMgm::siteRelPath($this->extKey) . 'res/prototype/styles.css';
		// Include CSS if present
		if ($this->config['cssFile']) $headerData .= "\n".'<link rel="stylesheet" type="text/css" href="'.$this->config['cssFile'].'" />';
		
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = $headerData;
		
		// Include cssID specific JS
		$headerData = '<script type="text/javascript">
/* <![CDATA[ */
document.observe("dom:loaded", function() {
	var el = $("'.$this->config['cssId'].'");
	if (el) {
		var form = el;
		for (var i=0;i<20;i++) {
			form = form.getOffsetParent();
			if (form.nodeName=="FORM") break;
		}
		var sectionpid = 0;
		var section = $("'.$this->config['sectionInputId'].'");
		if (section) {
			sectionpid = section.value;
		}
		var languageid = '.$this->config['languageid'].';
		var language = $("'.$this->config['languageInputId'].'");
		if (language) {
			languageid = language.value;
		}
		var mediaid = -1;
		var media = $("'.$this->config['mediaInputId'].'");
		if (media) {
			mediaid = media.value;
		}
		new Autocomplete("'.$this->config['cssId'].'", { 
		    serviceUrl:"index.php?eID='.$this->extKey.'&id='.$GLOBALS['TSFE']->id.'&sp="+sectionpid+"&la="+languageid+"&me="+mediaid+"&sw='.$this->config['startingWordOnly'].'&ml='.$this->config['minLength'].'&mc='.$this->config['maxChoices'].'&wc='.$this->config['showWordcount'].'",
		    minChars:'.$this->config['minLength'].', 
		    maxHeight:400,
			autoSubmit: '.$this->config['autoSubmit'].',
			spinner: '.$this->config['progressIndicator'].',
		    width:'.$this->config['dropDownWidth'].',
		    // callback function:
		    onSelect: function(value, data){
		        //alert("You selected: " + value + ", " + data);
		    }
		});
	}
});
  
/* ]]> */
</script>';
		$GLOBALS['TSFE']->additionalHeaderData[$this->config['cssId']] = $headerData;
	}
	
	/**
	 * Add the JQuery JavaScript and CSS stylesheet
	 *
	 * @param	void
	 * @return	void
	 */
	function setupJQuery() {
		// Process config data
		
		$headerData = '';
		// Include the jQuery library
		if ($this->config['includeFramework']) {
			$headerData .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/jquery/jquery-1.3.2.min.js"></script>';
		}
		// Include shared JS
		$headerData .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/jquery/autocomplete.js"></script>';
		
		$cssFile = t3lib_extMgm::siteRelPath($this->extKey) . 'res/jquery/styles.css';
		// Include CSS if present
		if ($this->config['cssFile']) $headerData .= "\n".'<link rel="stylesheet" type="text/css" href="'.$this->config['cssFile'].'" />';
		
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = $headerData;
		
		// Include cssID specific JS
		$headerData = '<script type="text/javascript">
/* <![CDATA[ */
$(document).ready(function() {
	var el = $("'.$this->config['cssId'].'");
	if (el) {
		var form = el;
		for (var i=0;i<20;i++) {
			form = form.parent();
			if (form.nodeName=="FORM") break;
		}
		var sectionpid = 0;
		var section = $("'.$this->config['sectionInputId'].'");
		if (section) {
			sectionpid = section.value;
		}
		var languageid = '.$this->config['languageid'].';
		var language = $("'.$this->config['languageInputId'].'");
		if (language) {
			languageid = language.value;
		}
		var mediaid = -1;
		var media = $("'.$this->config['mediaInputId'].'");
		if (media) {
			mediaid = media.value;
		}
		 $("#'.$this->config['cssId'].'").autocomplete({ 
		    serviceUrl:"index.php?eID='.$this->extKey.'&id='.$GLOBALS['TSFE']->id.'&sp="+sectionpid+"&la="+languageid+"&me="+mediaid+"&sw='.$this->config['startingWordOnly'].'&ml='.$this->config['minLength'].'&mc='.$this->config['maxChoices'].'&wc='.$this->config['showWordcount'].'",
		    minChars:'.$this->config['minLength'].', 
		    maxHeight:400,
			autoSubmit: '.$this->config['autoSubmit'].',
			spinner: '.$this->config['progressIndicator'].',
		    width:'.$this->config['dropDownWidth'].',
		    // callback function:
		    onSelect: function(value, data){
		        //alert("You selected: " + value + ", " + data);
		    }
		});
	}
});
  
/* ]]> */
</script>';
		$GLOBALS['TSFE']->additionalHeaderData[$this->config['cssId']] = $headerData;
	}
	
	/**
	 * Get plugin config options
	 *
	 * @param	array		$conf: Plugin conf array.
	 * @return	array		Plugin config options
	 */
	function getConfig($conf) {
		// Process config data
		$minLength = intval($conf['minLength']);
		$config['minLength'] = $minLength ? $minLength : 2;
		
		$maxChoices = intval($conf['maxChoices']);
		$config['maxChoices'] = $maxChoices ? $maxChoices : 10;
		
		$allowMulti = intval($conf['allowMulti']);
		$config['allowMulti'] = $allowMulti ? 1 : 0;
		
		$markQuery = intval($conf['markQuery']);
		$config['markQuery'] = ($markQuery && !$config['allowMulti']) ? 1 : 0;
		
		$inheritWidth = intval($conf['inheritWidth']);
		$config['inheritWidth'] = $inheritWidth ? 1 : 0;
		
		$config['dropDownWidth'] = intval($conf['dropDownWidth']);
		
		$config['progressIndicator'] = intval($conf['progressIndicator']);
		
		$startingWordOnly = intval($conf['startingWordOnly']);
		$config['startingWordOnly'] = $startingWordOnly ? 1 : 0;
		
		$useSelection = intval($conf['useSelection']);
		$config['useSelection'] = ($useSelection && !$config['allowMulti'] && $config['startingWordOnly']) ? 1 : 0;
		
		$showWordcount = intval($conf['showWordcount']);
		$config['showWordcount'] = $showWordcount ? 1 : 0;
		
		$autoSubmit = intval($conf['autoSubmit']);
		$config['autoSubmit'] = ($autoSubmit && !$config['allowMulti']) ? 1 : 0;
		
		$config['cssFile'] = trim($conf['cssFile']);
		
		$cssId = trim($conf['cssId']);
		$config['cssId'] = $cssId ? $cssId : 'tx-indexedsearch-searchbox-sword';
		
		$sectionInputId = trim($conf['sectionInputId']);
		$config['sectionInputId'] = $sectionInputId ? $sectionInputId : 'tx-indexedsearch-selectbox-sectionsxx';
		
		$languageInputId = trim($conf['languageInputId']);
		$config['languageInputId'] = $languageInputId ? $languageInputId : 'tx-indexedsearch-selectbox-langxx';
		
		$config['languageid'] = (isset($GLOBALS['TSFE']->config['config']['sys_language_uid']) && !$conf['allLang']) ? intval($GLOBALS['TSFE']->config['config']['sys_language_uid']) : -1;
		
		$mediaInputId = trim($conf['mediaInputId']);
		$config['mediaInputId'] = $mediaInputId ? $mediaInputId : 'tx-indexedsearch-selectbox-media';
		
		$includeFramework = intval($conf['includeFramework']);
		$config['includeFramework'] = $includeFramework ? 1 : 0;
		
		$framework = strtolower(trim($conf['framework']));
		$config['framework'] = t3lib_div::inList('mootools,jquery,prototype',$framework) ? $framework : 'mootools';
		
		return $config;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pmkisac/class.tx_pmkisac_init.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pmkisac/class.tx_pmkisac_init.php']);
}

?>
