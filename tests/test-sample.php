<?php
require_once 'mkjsonld-content.php';
class CanGetJsonld extends WP_UnitTestCase {

	private $mkjsonld;

	function __construct(){
		$this->mkjsonld = new mkjsonldContent;
	}

	function testUrlResponse(){
		if(isset($_SERVER['HOSTNAME'])){
			$this->checkRootAddress();
			$this->checkContextAddress();
		}
	}

	function checkRootAddress() {
		//Can Get ROOT JSON-LD ?
		$root = wp_remote_get( 'http://'. $_SERVER['HOSTNAME'] . '/json-ld');
		$this->assertEquals( $root['response']['code'], 200 );
	}

	function checkContextAddress(){
		$context = wp_remote_get( 'http://'.$_SERVER['HOSTNAME'] . '/jsonld-context');
		$this->assertEquals( $context['response']['code'], 200 );
	}

	function testGetJsonldContent(){
		global $wp_query;
		$wp_query->is_home = true;
		//$this->assertTrue(true);
	}

	function testGetJsonldDefaultContext(){
		$contextData = null;
		$defaultContextData =$this->mkjsonld->get_context_data($contextData);
		$this->assertTrue(is_array($defaultContextData));
		$this->assertEquals($defaultContextData["@context"], array("schema"=>"http://schema.org/"));
	}

	function testGetJsonldSingleContext(){
		$contextData[] = array(
			"type" => "schema",
			"iri"  => "http://schema.org/"
		);
		$singleContextData =$this->mkjsonld->get_context_data($contextData);
		$this->assertTrue(is_array($singleContextData));
		$this->assertEquals($singleContextData["@context"], "http://schema.org/");
	}

	function testGetJsonldManyContext(){
		$contextData[] = array(
			"type" => "schema",
			"iri"  => "http://schema.org/"
		);
		$contextData[] = array(
			"type" => "test",
			"iri"  => "http://example.com/"
		);
		$manyContextData =$this->mkjsonld->get_context_data($contextData);
		$this->assertTrue(is_array($manyContextData));
		$this->assertEquals($manyContextData["@context"][0], array(
				"schema"  => "http://schema.org/"
		));
		$this->assertEquals($manyContextData["@context"][1],array(
				"test"  => "http://example.com/"
		));
	}
}
