<?php
class CanGetJsonld extends WP_UnitTestCase {

	function testUrlResponse(){
		if($_SERVER['HOSTNAME'] == 'wordpress.local'){
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
		require_once 'mkjsonld-content.php';
		$mkjsonld = new mkjsonldContent;
		$jsonld = mkjsonld_getJsonld($mkjsonld);
		var_dump($jsonld);
		//$this->assertTrue(true);
	}
}
