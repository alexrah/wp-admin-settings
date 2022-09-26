<?php
namespace V2mPaywall;

class AdminSettingsTest extends BaseTest {

	/**
	 * @var array
	 * */
	protected $aSettings;

	public function __construct( $name = null, array $data = [], $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );

		$this->aSettings = AdminSettings::get_stored_option();

	}

	public function testIsPaywallSettingsSet(){

		$this->assertNotEmpty( $this->aSettings, 'AdminSettings::get_stored_option() is EMPTY' );

	}

	/**
	 * @depends testIsPaywallSettingsSet
	*/
	public function testIsPaywallEnabled(){
		$this->assertTrue( $this->aSettings['enabled'],
			'AdminSettings::get_stored_option()[\'enabled\'] is NOT TRUE'
		);
	}

	/**
	 * @depends testIsPaywallSettingsSet
	 */
	public function testIsPaywallSettingsTerm(){
		$this->assertInstanceOf(string , $this->aSettings['paywall_cat_id'],
			'AdminSettings::get_stored_option()[\'paywall_cat_id\'] NOT SET or NOT STRING' );
	}

	/**
	 * @depends testIsPaywallSettingsSet
	 */
	public function testIsPaywallSettingsPaypalID(){
		$this->assertInstanceOf(string , $this->aSettings['paypal_client_id'],
			'AdminSettings::get_stored_option()[\'paypal_client_id\'] NOT SET or NOT STRING' );
	}

	/**
	 * @depends testIsPaywallSettingsSet
	 */
	public function testIsPaywallSettingsPaypalClientSecret(){
		$this->assertInstanceOf(string , $this->aSettings['paypal_client_secret'],
			'AdminSettings::get_stored_option()[\'paypal_client_secret\'] NOT SET or NOT STRING' );
	}


}