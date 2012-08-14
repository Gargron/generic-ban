<?php

require_once 'PHPUnit/Autoload.php';
require_once 'Ban.php';

class BanTest extends PHPUnit_Framework_TestCase
{
	public function testIs()
	{
		Ban::make(2, array());

		$this->assertTrue(Ban::is('0.0.0.0', 2));
	}

	public function testIsNot()
	{
		Ban::undo(2);

		$this->assertFalse(Ban::is('0.0.0.0', 2));
	}

	public function testAlts()
	{
		Ban::track(1, '0.0.0.1');
		Ban::track(3, '0.0.0.1');
		Ban::track(5, '0.0.0.1');

		$this->assertEquals(array(3, 5), Ban::alts(1));
	}

	public function testIPs()
	{
		Ban::track(4, '0.0.0.2');
		Ban::track(4, '0.0.0.3');

		$this->assertEquals(array('0.0.0.2', '0.0.0.3'), Ban::ips(4));
	}

	public function testIsWhenAlt()
	{
		Ban::track(6, '0.0.0.4');
		Ban::track(7, '0.0.0.4');

		Ban::make(6, array(), 0, true);

		$this->assertTrue(Ban::is('0.0.0.4', 6));
		$this->assertTrue(Ban::is('0.0.0.4', 7));
	}

	public function testIsWhenOnlyIP()
	{
		Ban::track(8, '0.0.0.5');

		Ban::make(8, array());

		$this->assertTrue(Ban::is('0.0.0.5'));
	}

	public function testIsWhenOnlyIPnot()
	{
		Ban::undo(8);

		$this->assertFalse(Ban::is('0.0.0.5'));
	}
}