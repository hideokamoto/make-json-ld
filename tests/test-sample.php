<?php
class CanGetJsonld extends WP_UnitTestCase {
const ROOT_ADDRESS = "http://wordpress.local/";

	function testRootAddress() {
		//Can Get ROOT JSON-LD ?
		$root = wp_remote_get( self::ROOT_ADDRESS . 'json-ld');
		$this->assertEquals( $root['response']['code'], 200 );
	}

	function testContextAddress(){
		$context = wp_remote_get( self::ROOT_ADDRESS . 'jsonld-context');
		$this->assertEquals( $context['response']['code'], 200 );
	}
}
