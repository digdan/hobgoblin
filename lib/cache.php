<?php
/**
 * Parts of this code are based on and/or adapted from original work by Iain Cambridge (http://icambridge.me/)
 */

interface CacheBase
{
	public function getCache($VarName);
	public function setCache($VarName,$VarValue,$TimeLimt = Cache::CACHE_FIVE_MINUTES);
	public function deleteCache($VarName);
	public function clearCache();
}

/**
 * No_Cache: Implements a dummy class to handle when no cache is configured, or the cache type is set to 'none'
 */
class No_Cache implements CacheBase {

	/**
	 * Dummy method: Returns false for all requests.
	 * @param $VarName string
	 * @return false
	 */
	public function getCache($VarName)
	{
		return FALSE;
	}

	/**
	 * Dummy method: Returns false for all requests.
	 * @param $VarName string
	 * @param $VarValue mixed
	 * @param $TimeLimit int the amount of time before it expires (defaults to Cache::CACHE_FIVE_MINUTES)
	 * @return false
	 */
	public function setCache($VarName, $VarValue, $TimeLimit = Cache::CACHE_FIVE_MINUTES)
	{
		return FALSE;
	}

	/**
	 * Dummy method: Returns false for all requests.
	 * @param $VarName string
	 * @return false
	 */
	public function deleteCache($VarName)
	{
		return FALSE;
	}

	/**
	 * Dummy method: Returns false for all requests.
	 * @return false
	 */
	public function clearCache()
	{
		return FALSE;
	}

}

/**
 * APC_Cache
 */
class APC_Cache implements CacheBase {

	/**
	 * Returns the cached variable or
	 * false if it doesn't exist.
	 * @param $VarName string
	 * @return mixed
	 */
	public function getCache($VarName)
	{
		return apc_fetch($VarName);
	}

	/**
	 * Sets a variable to the cache.
	 * Returns true if successful and
	 * false if fails.
	 * @param $VarName string
	 * @param $VarValue mixed
	 * @param $TimeLimit int the amount of time before it expires
	 * @return bool
	 */
	public function setCache($VarName,$VarValue,$TimeLimit = Cache::CACHE_FIVE_MINUTES)
	{
		return apc_store($VarName,$VarValue,$TimeLimit);
	}

	/**
	 * Deletes a variable from the cache.
	 * Returns true if successful and false
	 * if fails.
	 * @param $VarName string
	 * @return bool
	 */
	public function deleteCache($VarName)
	{
		return apc_delete($VarName);
	}

	/**
	 * Clears the cache of the all the
	 * variables in it. Returns true if
	 * successful and false if it fails.
	 * @return bool
	 */
	public function clearCache()
	{
		return apc_clear_cache();
	}

}

/**
 * eAccelerator_Cache
 */
class eAccelerator_Cache implements CacheBase {

	/**
	 * Returns the cached variable or
	 * false if it doesn't exist.
	 * @param $VarName string
	 * @return mixed
	 */
	public function getCache($VarName)
	{
		$VarValue = eaccelerator_get($VarName);
		return ($VarValue == NULL) ? false : $VarValue;
	}

	/**
	 * Sets a variable to the cache.
	 * Returns true if successful and
	 * false if fails.
	 * @param $VarName string
	 * @param $VarValue mixed
	 * @param $TimeLimit int the amount of time before it expires
	 * @return bool
	 */
	public function setCache($VarName,$VarValue,$TimeLimit = Cache::CACHE_FIVE_MINUTES)
	{
		return eaccelerator_put($VarName,$VarValue,$TimeLimit);
	}

	/**
	 * Deletes a variable from the cache.
	 * Returns true if successful and false
	 * if fails.
	 * @param $VarName string
	 * @return bool
	 */
	public function deleteCache($VarName)
	{
		return eaccelerator_rm($VarName);
	}

	/**
	 * Clears the cache of the all the
	 * variables in it. Returns true if
	 * successful and false if it fails.
	 * @return bool
	 */
	public function clearCache()
	{
		return eaccelerator_clear();
	}

}

/**
 * XCache_Cache
 */
class XCache_Cache implements CacheBase {

	/**
	 * Returns the cached variable or
	 * false if it doesn't exist.
	 * @param $VarName string
	 * @return mixed
	 */
	public function getCache($VarName)
	{
		return ( $VarValue = xcache_get($VarName) ) ? $VarValue : false;
	}

	/**
	 * Sets a variable to the cache.
	 * Returns true if successful and
	 * false if fails.
	 * @param $VarName string
	 * @param $VarValue mixed
	 * @param $TimeLimit int the amount of time before it expires
	 * @return bool
	 */
	public function setCache($VarName,$VarValue,$TimeLimit = Cache::CACHE_FIVE_MINUTES)
	{
		return ( xcache_set($VarName,$VarValue,$TimeLimit) ) ? true : false;
	}

	/**
	 * Deletes a variable from the cache.
	 * Returns true if successful and false
	 * if fails.
	 * @param $VarName string
	 * @return bool
	 */
	public function deleteCache($VarName)
	{
		return ( xcache_unset($VarName) ) ? true : false;
	}

	/**
	 * Clears the cache of the all the
	 * variables in it. Returns true if
	 * successful and false if it fails.
	 * @return bool
	 */
	public function clearCache()
	{
		for ($i = 0, $c = xcache_count(XC_TYPE_VAR); $i < $c; $i ++) {
			xcache_clear_cache(XC_TYPE_VAR, $i);
		}
		return TRUE;
	}

}


/**
 * File_Cache
 */
class File_Cache implements CacheBase {

	protected $cache_folder = './cache/';

	/**
	 * Sets the location of the file cache folder.
	 * @param $foldername string
	 */
	public function setCacheFolder($foldername)
	{
		$this->cache_folder = $foldername;
	}

	/**
	 * Returns the cached variable or
	 * false if it doesn't exist.
	 * @param $VarName string
	 * @return mixed
	 */
	public function getCache($VarName)
	{
		$filename = $this->getFileName($VarName);
		if (!file_exists($filename)) return false;
		$h = fopen($filename,'r');

		if (!$h) return false;

		// Getting a shared lock
		flock($h,LOCK_SH);

		$data = file_get_contents($filename);

		fclose($h);

		$data = @unserialize($data);

		if (!$data) {

			// If unserializing somehow didn't work out, we'll delete the file
			unlink($filename);
			return false;

		}

		if (time() > $data[0]) {

			// Unlinking when the file was expired
			unlink($filename);
			return false;

		}

		return $data[1];
	}

	/**
	 * Sets a variable to the cache.
	 * Returns true if successful and
	 * false if fails.
	 * @param $VarName string
	 * @param $VarValue mixed
	 * @param $TimeLimit int the amount of time before it expires
	 * @return bool
	 */
	public function setCache($VarName, $VarValue, $TimeLimt = Cache::CACHE_FIVE_MINUTES)
	{
		// Opening the file in read/write mode
		$h = fopen($this->getFileName($VarName),'a+');
		if (!$h) throw new Exception('Could not write to cache');

		flock($h,LOCK_EX); // exclusive lock, will get released when the file is closed

		fseek($h,0); // go to the start of the file

		// truncate the file
		ftruncate($h,0);

		// Serializing along with the TTL
		$data = serialize(array(time()+$TimeLimt, $VarValue));
		if (fwrite($h,$data)===false)
		{
			throw new Exception('Could not write to cache');
		}
		fclose($h);

		return true;
	}

	/**
	 * Deletes a variable from the cache.
	 * Returns true if successful and false
	 * if fails.
	 * @param $VarName string
	 * @return bool
	 */
	public function deleteCache($VarName)
	{
		$filename = $this->getFileName($VarName);
		if (file_exists($filename))
		{
			return unlink($filename);
		} else {
			return false;
		}
	}

	/**
	 * Clears the cache of the all the
	 * variables in it. Returns true if
	 * successful and false if it fails.
	 * @return bool
	 */
	public function clearCache()
	{
		$handle = opendir($this->cache_folder);
		while( false !== ($file = readdir($handle)) )
		{
			if ($file != "." && $file != "..")
			{
				if (is_dir($this->cache_folder.$file))
				{
					//purge ($dir.$file."/");
					//rmdir($dir.$file);
				} else {
					unlink($this->cache_folder.$file);
				}
			}
		}
		closedir($handle);

		return true;
	}

	private function getFileName($VarName)
	{
		return $this->cache_folder . md5($VarName) . '.cache';
	}
}


/*********************************************************************************
DEFINE THE CORE UE CLASS USED IN THE APPLICATION
 *********************************************************************************/
class Cache
{
	const CACHE_ONE_DAY = 86400;
	const CACHE_ONE_HOUR = 3600;
	const CACHE_HALF_HOUR = 1800;
	const CACHE_FIVE_MINUTES = 300;

	// Hold an instance of the selected caching class
	private static $cache = null;

	// Hold an instance of this class
	private static $instance;

	// A private constructor; prevents direct creation of object
	private function __construct() { }

	// The singleton method
	public static function getInstance()
	{
		if(!isset(self::$instance))
		{
			// Factory method
			switch(CACHE_TYPE)
			{
				case 'apc': self::$cache = new APC_Cache; break;
				case 'eaccelerator': self::$cache = new eAccelerator_Cache; break;
				case 'xcache': self::$cache = new XCache_Cache; break;
				case 'file': self::$cache = new File_Cache; break;

				case 'none':
				default:
					self::$cache = new No_Cache;
					break;
			}

			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public function set_cache_folder($foldername)
	{
		if(is_object(self::$cache) && getclass(self::$cache) == 'File_Cache')
		{
			self::$cache->setCacheFolder($foldername);
		}
	}

	// Prevent users from cloning the instance
	public function __clone()
	{
		throw new Exception('Clone is not allowed.');
	}

	public function getCache($VarName) { return self::$cache->getCache($VarName); }
	public function setCache($VarName, $VarValue, $TimeLimt = Cache::CACHE_FIVE_MINUTES) { return self::$cache->setCache($VarName, $VarValue, $TimeLimt); }
	public function deleteCache($VarName) { return self::$cache->deleteCache($VarName); }
	public function clearCache() { return self::$cache->clear(); }

}
