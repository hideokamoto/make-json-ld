<?php
class CanGetJsonld extends WP_UnitTestCase {
const ROOT_ADDRESS = "http://wordpress.local/";

	function testRoot(){
		var_dump($_SERVER['HTTP_HOST']);
		var_dump(dirname( __DIR__ ));
		var_dump($_SERVER);
		$this->assertEquals( 200, 200 );
	}

	function testRootAddress() {
		//Can Get ROOT JSON-LD ?
		$root = wp_remote_get( self::ROOT_ADDRESS . 'json-ld');
		$this->assertEquals( $root['response']['code'], 200 );
	}

	function testContextAddress(){
		$context = wp_remote_get( self::ROOT_ADDRESS . 'jsonld-context');
		$this->assertEquals( $context['response']['code'], 200 );
	}

	function testAAA(){
		global $wp_query;
		$wp_query->is_home = true;
		require_once 'mkjsonld-content.php';
		$mkjsonld = new mkjsonldContent;
		$jsonld = mkjsonld_getJsonld($mkjsonld);
		var_dump($jsonld);
		//$this->assertTrue(true);
	}
}
