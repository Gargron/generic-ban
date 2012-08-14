<?php

/**
 * @author Eugen Rochko <gargron@gmail.com>
 */

class Ban
{
	/**
	 * An active data store instance
	 *
	 * @var Redis
	 */

	private static $_store;

	/**
	 * Check if the user is banned by ID. Then
	 * also check if the user's IP is banned.
	 *
	 * @param  string
	 * @param  integer
	 * @return boolean
	 */

	public static function is($ip, $id = null)
	{
		return !!((! is_null($id) && self::store()->exists('ban:id:' . $id)) || self::store()->exists('ban:ip:' . $ip));
	}

	/**
	 * Get a list of IDs of the user's alternate
	 * accounts.
	 *
	 * @param  integer
	 * @return array
	 */

	public static function alts($id)
	{
		$ips = self::store()->zRange('alt:id:' . $id, 0, -1);
		$ids = array();

		foreach($ips as $ip)
		{
			$ids = array_merge($ids, self::store()->zRange('alt:ip:' . $ip, 0, -1));
		}

		$ids = array_unique($ids);
		$ids = array_map(function($item) { return (int) $item; }, $ids);
		$ids = array_slice($ids, 1);

		return $ids;
	}

	/**
	 * Get a list of the user's IPs.
	 *
	 * @param  integer
	 * @return array
	 */

	public static function ips($id)
	{
		return self::store()->zRange('alt:id:' . $id, 0, -1);
	}

	/**
	 * Connect user to IPs, IPs to user,
	 * user to alts, alts to user.
	 *
	 * @param  integer
	 * @param  string
	 * @return void
	 */

	public static function track($id, $ip)
	{
		self::store()->zAdd('alt:ip:' . $ip, time(), $id);
		self::store()->zAdd('alt:id:' . $id, time(), $ip);
	}

	/**
	 * Ban a user
	 *
	 * @param  integer
	 * @param  mixed
	 * @param  integer Whether ban should expire. 0 means no, otherwise seconds
	 * @param  boolean
	 * @return void
	 */

	public static function make($id, $data, $expire = 0, $with_alts = false)
	{
		self::_make($id, $data, $expire);

		foreach(self::ips($id) as $ip)
		{
			self::store()->set('ban:ip:' . $ip, json_encode($data));

			if($expire > 0)
			{
				self::store()->expire('ban:ip:' . $ip, $expire);
			}
		}

		if($with_alts)
		{
			foreach(self::alts($id) as $alt_id)
			{
				self::_make($alt_id, $data, $expire);
			}
		}
	}

	/**
	 * Ban a user. No alts handling code. Private.
	 *
	 * @param  integer
	 * @param  mixed
	 * @param  integer
	 * @return void
	 */

	private static function _make($id, $data, $expire)
	{
		self::store()->set('ban:id:' . $id, json_encode($data));

		if($expire > 0)
		{
			self::store()->expire('ban:id:' . $id, $expire);
		}
	}

	/**
	 * Unban a user
	 *
	 * @param  integer
	 * @return void
	 */

	public static function undo($id)
	{
		self::store()->del('ban:id:' . $id);
	}

	/**
	 * Data store access singleton
	 *
	 * @return Redis
	 */

	private static function store()
	{
		if(is_null(self::$_store))
		{
			self::$_store = new Redis;
			self::$_store->connect('127.0.0.1', 6379);
			self::$_store->select(9);
		}
		
		return self::$_store;
	}
}