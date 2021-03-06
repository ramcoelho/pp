<?php
/*******************************************************************************
* Copyright 2013 Evaldo Barbosa
*
* This file is part of Product Puller.
*
* Product Puller is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* Product Puller is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Product Puller; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*
*******************************************************************************/

namespace Puller\Target;

use Puller\AbstractProductInfo;

class SubmarinoProductInfo extends AbstractProductInfo {
  protected function urlMask() {
		return 'http://www.submarino.com.br/produto/%d';
	}
	
	protected function getId() {
		$ret = $this->allInformation;
		
		$regex = '#<p class="sku">(.*?)</p>#is';
		$matches = array();
		preg_match_all($regex, $ret, $matches, PREG_PATTERN_ORDER);
		
		return preg_replace( "/[^0-9]+/", "", $matches[1][0] );
	}
	
	protected function getName() {
		$sIni = '<h1 class="n name fn">';
		$sFim = '</h1>';
		$ret = $this->allInformation;
		
		$regex = '#<h1 class="n name fn">(.*?)</h1>#is';
		$matches = array();
		preg_match_all($regex, $ret, $matches, PREG_PATTERN_ORDER);
		
		return strip_tags( trim($matches[1][0]));		
	}
	
	protected function getPrice() {
		$ret = $this->allInformation;
		
		$regex = '#<span class="amount">(.*?)</span>#is';
		$matches = array();
		preg_match_all($regex, $ret, $matches, PREG_PATTERN_ORDER);
		
		return trim($matches[1][1]);
	}
	
	protected function getDesc() {
		$ret = $this->allInformation;
		
		$qtdChrs = 1000;
		
		$sIni = '<div class="productDescription" id="productDescription">';
		
		$begin = strpos($ret,$sIni);
		$ret = substr( $ret, $begin, $qtdChrs );
		$ret = substr( $ret, strlen($sIni), $qtdChrs );
		
		$sIni = '<div class="box-content">';
		
		$begin = strpos($ret,$sIni);
		$ret = substr( $ret, $begin, $qtdChrs );
		$ret = substr( $ret, strlen($sIni), $qtdChrs );
		
		$sIni = '<div class="box-content">';
		
		$begin = strpos($ret,$sIni);
		$ret = substr( $ret, $begin, $qtdChrs );
		$ret = substr( $ret, strlen($sIni), $qtdChrs );
		
		$sIni = '<p>';
		
		$begin = strpos($ret,$sIni);
		$ret = substr( $ret, $begin, $qtdChrs );
		$ret = substr( $ret, strlen($sIni), $qtdChrs );
		
		$ret = trim( str_replace("\t","",$ret) );
		$ret = trim( str_replace("\n","",$ret) );
		$ret = trim( str_replace("\r","",$ret) );
		$ret = strip_tags( $ret );
		
		return $ret;
	}
	
	/**
	 * @return \ArrayIterator
	 * @see \Product\AbstractProductInfo::getDescTable()
	 */
	protected function getDescTable() {
		$info = new \ArrayIterator();
		
		$ret = $this->allInformation;
		
		$regex = '#<th class="des-tb-body ds-tb-bd-01">(.*?)</th>#is';
		$indexes = array();
		preg_match_all($regex, $ret, $indexes, PREG_PATTERN_ORDER);
		
		$regex = '#<td class="des-tb-body ds-tb-bd-02">(.*?)</td>#is';
		$values = array();
		preg_match_all($regex, $ret, $values, PREG_PATTERN_ORDER);
		
		foreach ( $indexes[1] as $key=>$val ) {
			$info->offsetSet($val, $values[1][$key]);
		}
		
		unset($indexes,$values);
		
		return $info;
	}
	
	protected function getPicture() {
		$ret = strip_tags($this->allInformation,"<img>");
		
		$info = null;
		$regex = '#<img width="250" height="250" id="imgProduto"(.*?[^/])src="(.*?[^/])"(.*?[^/])class="photo" />#is';
		$matches = array();
		preg_match_all($regex, $ret, $matches, PREG_PATTERN_ORDER);
		
		$info = $matches[2][0]; 
		
		unset($matches);
		
		return $info;
	} 
}
