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

// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('This script can not be accessed directly!');

// Include classes necessary for initializing frontend user:
// We will use tslib_fe to do that:
require_once(PATH_tslib.'class.tslib_fe.php');
require_once(PATH_t3lib.'class.t3lib_cs.php');
require_once(PATH_t3lib.'class.t3lib_userauth.php');
require_once(PATH_tslib.'class.tslib_feuserauth.php');

// We also need a couple classes for processing access information
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_tslib.'class.tslib_content.php');

class tx_pmkisac_autocomplete {
	/**
	 * Init function, setting the input vars in the global space.
	 *
	 * @return	void
	 */
	function init() {

		// Config options
		$this->sections = trim(t3lib_div::_GP('sp'));
		$this->language = intval(t3lib_div::_GP('la'));
		$this->media = intval(t3lib_div::_GP('me'));
		$this->page = intval(t3lib_div::_GP('id'));
		$this->startingWordOnly = intval(t3lib_div::_GP('sw'))?1:0;
		$this->minLength = intval(t3lib_div::_GP('ml'));
		$this->maxChoices = intval(t3lib_div::_GP('mc'));
		$this->showWordcount = intval(t3lib_div::_GP('wc'))?1:0;
		$this->mode = t3lib_div::_GP('value') ? 1 : 0;
		
		// Make new instance of TSFE object for initializing user
		// Identical to the function tslib_eidtools::initFeUser(), but setup TSFE in a global scope
		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$GLOBALS['TSFE'] = new $temp_TSFEclassName($GLOBALS['TYPO3_CONF_VARS'],$this->page,0);
		$GLOBALS['TSFE']->connectToDB();

		// Initialize FE user:
		$GLOBALS['TSFE']->initFEuser();

		// Initialize FE groups:
		$GLOBALS['TSFE']->initUserGroups();
		$GLOBALS['TSFE']->fe_user->fetchGroupData();

		// Connect to database:
		$GLOBALS['TYPO3_DB']->connectDB();

		$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');

		// Add rootLine array to TSFE, based on current page id.
		$GLOBALS['TSFE']->rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($this->page,'');

		// Create string containing ids of every accessible page in the site
		$siteIdNumbers = t3lib_div::intExplode(',',$GLOBALS['TSFE']->rootLine[0]['uid']);
		$id_list=array();
		while(list(,$rootId)=each($siteIdNumbers))	{
			$id_list[] = tslib_cObj::getTreeList($rootId,100,0,0,'','').$rootId;
		}
		$this->wholeSiteIdList = implode(',',$id_list);
	}

	/**
	 * Main function
	 *
	 * @return	void
	 */
	function main() {
		$this->content = '';
		$t1 = microtime(true);
		$word = $this->mode ? trim(t3lib_div::_GP('value')) : trim(t3lib_div::_GP('query'));
//		$this->findWords(trim(t3lib_div::_GP('value')));
		$this->findWords($word);
		$t2 = microtime(true);
		$time = sprintf('%.4f', ($t2 - $t1) );

//		$this->content.= 'Time: '.$time.' sec.';
	}

	/**
	 * Outputs the content from $this->content
	 *
	 * @return	void
	 */
	function printContent() {
		if ($this->mode) {
			// Mootools
			header('Content-type: text/html; Charset=utf-8');
			// Space at end needed, otherwise progress indicator stays active when no result is found
			echo $this->content.' ';
		}
		else {
			// JQuery & Prototype
			header('Content-type: application/json; Charset=utf-8');
			echo json_encode($this->content);
		}
	}

	/**
	 * Look up the words - Accumulates the output in $this->content
	 *
	 * @return	void
	 */
	function findWords($search_word) {

		$final_results = array();

		// Look up the word
		$wSel = "IW.baseword LIKE '".$GLOBALS['TYPO3_DB']->quoteStr($search_word, 'index_words')."%'";
		if (!$this->startingWordOnly) {
			$wSel.= " OR IW.baseword LIKE '%".$GLOBALS['TYPO3_DB']->quoteStr($search_word, 'index_words')."%'";
			$wSel.= " OR IW.baseword LIKE '%".$GLOBALS['TYPO3_DB']->quoteStr($search_word, 'index_words')."'";
			$wSel = '('.$wSel.')';
		}
		$data = $this->execPHashListQuery($wSel,' AND is_stopword=0 AND ISEC.page_id IN ('.$this->wholeSiteIdList.') '.$this->mediaTypeWhere().' '.$this->languageWhere());
		//$data = $this->execPHashListQuery($wSel,' AND is_stopword=0 '.$this->mediaTypeWhere().' '.$this->languageWhere());
		//$data = $this->execPHashListQuery($wSel,' AND is_stopword=0');
		// Build the array
		$results = array();
		$word_page_index = array();
		while ($value = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($data)) {
			if (!isset($results[$value['baseword']])) {
				$results[$value['baseword']] = 1;
			} else {
				$results[$value['baseword']]++;
			}
		}


		// Merge wordcount for words that are part of other word
		$final_results = $results;
/*
		foreach($results as $word => $count) {
			foreach($final_results as $final_word => $final_count) {
				if (strpos($word, $final_word)!== false && $word!=$final_word) {
					$final_results[$final_word]+= $count;
					unset($final_results[$word]);
					break;
				}
			}
		}
*/
		// Sort it
		arsort($final_results);

		// Reduce array to length specified in $this->maxChoices
		$final_results = array_slice($final_results, 0,$this->maxChoices);
		
		// Generate output
		if ($this->mode) {
			// Mootools
			if ($this->showWordcount) {
				foreach($final_results as $word => $count) {
					$this->content.='<li><span>'.$word.'</span> <span class="autocompleter-count">('.$count.')</span></li>';
				}
			}
			else {
				foreach($final_results as $word => $count) {
					$this->content.='<li><span>'.$word.'</span></li>';
				}
			}
		}
		else {
			// Prototype & JQuery
			$this->content['query'] = $search_word;
			$suggestions = array();
			$data = array();
			foreach($final_results as $word => $count) {
				$suggestions[] =$word;
				$data[] =$count;
			}
			$this->content['suggestions'] = $suggestions;
			$this->content['data'] = $data;
		}
	}


	/**
	 * Returns a query which selects the search-word from the word/rel tables.
	 *
	 * @param	string		WHERE clause selecting the word from phash
	 * @param	string		Additional AND clause in the end of the query.
	 * @return	pointer		SQL result pointer
	 */
	function execPHashListQuery($wordSel,$plusQ='')	{
		return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'IR.phash, IW.baseword, IP.gr_list,ISEC.phash_t3',
					'index_words IW,
						index_phash IP,
						index_rel IR,
						index_grlist IG,
						index_section ISEC',
					$wordSel.'
						AND IW.wid=IR.wid
						AND ISEC.phash = IR.phash
						AND ISEC.phash = IP.phash
						AND ISEC.phash_t3 = IG.phash
						AND IG.gr_list = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($GLOBALS['TSFE']->gr_list, 'index_grlist').'
						'.$this->sectionTableWhere().'
						'.$plusQ
				);
	}

	/**
	 * Returns AND statement for selection of langauge
	 *
	 * @return	string		AND statement for selection of langauge
	 */
	function languageWhere()	{
		if ($this->language>=0)	{	// -1 is the same as ALL language.
			return 'AND IP.sys_language_uid='.$this->language;
		}
	}

	/**
	 * Returns AND statement for selection of media type
	 *
	 * @return	string		AND statement for selection of media type
	 */
	function mediaTypeWhere()	{
		switch((string)$this->media)	{
			case '0':		// '0' => 'Kun TYPO3 sider',
				$out = 'AND IP.item_type='.$GLOBALS['TYPO3_DB']->fullQuoteStr('0', 'index_phash');
			break;
			case '-2':		// All external documents
				$out = 'AND IP.item_type!='.$GLOBALS['TYPO3_DB']->fullQuoteStr('0', 'index_phash');
			break;
			case '-1':	// All content
				$out='';
			break;
			default:
				$out = 'AND IP.item_type='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->media, 'index_phash');
			break;
		}
		return $out;
	}
	
	/**
	 * Returns AND statement for selection of section in database. (rootlevel 0-2 + page_id)
	 *
	 * @return	string		AND clause for selection of section in database.
	 */
	function sectionTableWhere()	{
		$out = $this->wholeSiteIdList<0 ? 'AND ISEC.rl0 IN ('.$this->wholeSiteIdList.')': '';
		$match = '';
		if (substr($this->sections,0,4)=='rl1_')	{
			$list = implode(',',t3lib_div::intExplode(',',substr($this->sections,4)));
			$out.= 'AND ISEC.rl1 IN ('.$list.')';
			$match = TRUE;
		} elseif (substr($this->sections,0,4)=='rl2_')	{
			$list = implode(',',t3lib_div::intExplode(',',substr($this->sections,4)));
			$out.= 'AND ISEC.rl2 IN ('.$list.')';
			$match = TRUE;
		} elseif (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['indexed_search']['addRootLineFields']))	{
				// Traversing user configured fields to see if any of those are used to limit search to a section:
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['indexed_search']['addRootLineFields'] as $fieldName => $rootLineLevel)	{
				if (substr($this->sections,0,strlen($fieldName)+1)==$fieldName.'_')	{
					$list = implode(',',t3lib_div::intExplode(',',substr($this->sections,strlen($fieldName)+1)));
					$out.= 'AND ISEC.'.$fieldName.' IN ('.$list.')';
					$match = TRUE;
					break;
				}
			}
		}
		// If no match above, test the static types:
		if (!$match)	{
			switch((string)$this->sections)	{
				case '-1':		// '-1' => 'Only this page',
					$out.= ' AND ISEC.page_id='.$this->page;
				break;
				case '-2':		// '-2' => 'Top + level 1',
					$out.= ' AND ISEC.rl2=0';
				break;
				case '-3':		// '-3' => 'Level 2 and out',
					$out.= ' AND ISEC.rl2>0';
				break;
			}
		}

		return $out;
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pmkisac/class.tx_pmkisac_autocomplete.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pmkisac/class.tx_pmkisac_autocomplete.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_pmkisac_autocomplete');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>
