<?php

class OutboundTest extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        Outbound::init('APIKEY');
    }

    /**
     * @expectedException Exception
     */
    public function testInitEnforcement() {
        Outbound::reset();
        Outbound::identify(1);
    }

    /**
     * @expectedException OutboundDataException
     */
    public function testUserIdForIdentify() {
        Outbound::identify(array('user_id'));
    }

    /**
     * @expectedException OutboundDataException
     */
    public function testUserIdForTrack() {
        Outbound::track(array('user_id'), 'event');
    }

    /**
     * @expectedException OutboundDataException
     */
    public function testEventNameForTrack() {
        Outbound::track('user_id', 9);
    }

    /**
     * @expectedException OutboundDataException
     */
    public function testPlatformForRegister() {
        Outbound::register_token('platform', 'user_id', 'token');
    }

    /**
     * @expectedException OutboundDataException
     */
    public function testUserIdForRegister() {
        Outbound::register_token(Outbound::APNS, array('user_id'), 'token');
    }

    /**
     * @expectedException OutboundDataException
     */
    public function testTokenForRegister() {
        Outbound::register_token(Outbound::APNS, 'user_id', 9);
    }
}
