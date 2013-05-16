<?php
namespace Puller;

use Infra\Utils\RestRequest;

abstract class AbstractProductInfo {
  private $cacheFileName;
	private $url;
	protected $id;
	protected $name;
	protected $price;
	protected $picture;
	protected $desc;
	protected $descTable;
	protected $allInformation;
	
	function __construct($pid) {
		$this->url = sprintf(
			$this->urlMask(),
			$pid
		);
		
		$hash = md5($this->url);
		$class = explode("\\",get_class($this));
		$class = $class[ count($class)-1 ];
		$this->cacheFileName = "/tmp/{$class}-{$hash}.html";
		
		if ( file_exists( $this->cacheFileName ) ) {
			$this->allInformation = file_get_contents( $this->cacheFileName );
		} else {		
			$this->getProductFromUrl("{$this->url}");
		}
		
		$this->parse();
	}

	function getProductFromUrl($url) {
		global $restRequestProxyInfo;
		
		$req = new RestRequest( $url );
			$req->setProxyInfo($restRequestProxyInfo);
		$req->execute();
		
		$text = htmlspecialchars_decode( $req->getResponseBody() );
		$text = str_replace(
			"<!-- componente de oferta 24h -->",
			"",
			$text
		);
		
		$text = $this->getBodyScope( $text );
		
		$iBegin = strpos( $text, "<!-- Cufon" );
		$toReplace = substr( $text, $iBegin, 1000 );
		$iEnd = strpos( $toReplace,	"-->" );
		$toReplace = substr( $toReplace, 0, $iEnd+3 );
		$text = str_replace($toReplace, "", $text);
		
		$text = $this->clearScripts($text);
		$text = $this->clearScripts($text,'noscript');
		$text = $this->clearScripts($text,'style');
			
		$this->allInformation = $text;
		file_put_contents( $this->cacheFileName, $this->allInformation);
	}
	
	private function getBodyScope($html) {
		$regex = '#<body(.*?)>(.*?)</body>#is';
		
		$matches = array();
		preg_match_all($regex, $html, $matches, PREG_PATTERN_ORDER);
		return $matches[0][0];
	}
	
	protected function clearScripts( $html, $tagname=null ) {
		$regex = '#<script(.*?)>(.*?)</script>#is';
		if( !is_null($tagname) ) {
			$regex = '#<' . $tagname . '(.*?)>(.*?)</' . $tagname . '>#is';
		}
		
		$matches = array();
		preg_match_all($regex, $html, $matches, PREG_PATTERN_ORDER);
		$matches = $matches[0];
		
		foreach ( $matches as $match ) {
			$html = str_replace( $match, "", $html );
		}
		
		return $html;
	}

	function __get($key) {
		switch ( $key ) {
			case "productURL":
				return $this->url;
			case "productId":
				return $this->id;
			case "productName":
				return $this->name;
			case "productPrice":
				return $this->price;
			case "productInfo":
				return $this->desc;
			case "productTable":
				return $this->descTable;
			case "productPicture":
				return $this->picture;
		}
	}
	
	function parse() {
		$this->id = $this->getId();
		$this->name = $this->getName();
		$this->price = $this->getPrice();
		$this->desc = $this->getDesc();
		$this->descTable = $this->getDescTable();
		$this->picture = $this->getPicture();
	}
	
	function getCloseTag( $tag ) {
		$idx = strpos($tag," ");
		$tag = substr($tag,0,$idx);
		$tag = $tag . ">";
		$tag = str_replace("<","</",$tag);
		return trim( $tag );
	}
	
	abstract protected function urlMask();
	abstract protected function getId();
	abstract protected function getName();
	abstract protected function getPrice();
	abstract protected function getDesc();
	abstract protected function getDescTable();
	abstract protected function getPicture();
}