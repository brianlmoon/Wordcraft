<?php

/**
 * Class for Memcache caching
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */


class WCCache {

    /**
     * Holds the Memcache object
     *
     * @var object
     */
    private $MC;


    /**
     * Constructor for the class.  Creates the Memcache class.
     */
    public function __construct() {

        global $WC;

        if(!function_exists("memcache_connect")){
            trigger_error("The pecl/memcache extension is not installed on this server.  The Memcache caching object can not be used.", E_USER_ERROR);
        }

        $this->MC = new Memcache();

        foreach($WC["CACHE_SETTINGS"]["servers"] as $server){

            $this->MC->addServer($server[0], $server[1]);
        }

        $this->local_prefix = md5(__FILE__);

    }

    /**
     * Gets an item from cache.  Returns false on failure, or some other valid
     * type on success.
     *
     * @access  public
     * @param   string      $key    The key for the cache item
     * @return  mixed
     *
     */
    public function get($key) {

        $data = false;

        $prefix = $this->get_prefix();

        $data = $this->MC->get($key);

        return $data;
    }


    /**
     * Puts an item into the cache.
     *
     * @access  public
     * @param   string      $key    The key for the cached item
     * @param   mixed       $data   The variable to be stored
     * @param   int         $ttl    Time in seconds for the cache to live
     * @return  bool
     *
     */
    public function set($key, $data, $ttl=null) {

        global $WC;

        $success = false;

        if(!isset($ttl)){
            $ttl = $WC["CACHE_SETTINGS"]["default_ttl"];
        }

        $success = $this->MC->set($key, $data, 0, $ttl);

        return (bool)$success;
    }


    /**
     * Clears all cached items
     *
     * @access  public
     * @return  void
     *
     */
    public function clear() {

        global $WC;

        $this->set_cache_prefix();

    }


    /**
     * Verifies if the cache is enabled and working
     *
     * @access  public
     * @return  bool
     *
     */
    public function verify() {

        global $WC;

        $success = false;

        $rand = md5(microtime());

        $success = $this->MC->set($rand, "1");

        if($success){
            $this->MC->del($rand);
        }

        return $success;

    }


    /**
     * Gets the prefix for keys stored by this object
     *
     * @access  private
     * @return  string
     *
     */
    private function prefix() {

        global $WC;

        $prefix = $this->local_prefix;

        $cache_prefix = $this->MC->get($prefix."_cache_prefix");

        if($cache_prefix===false){
            $cache_prefix = $this->set_cache_prefix();
        }

        $prefix.="_$cache_prefix";

        return $prefix;
    }


    /**
     * Sets the cache prefix for versioning the cache
     *
     * @access  private
     * @return  string
     *
     */
    private function set_cache_prefix() {

        global $WC;

        $prefix = microtime(true);

        $this->MC->set($this->local_prefix."_cache_prefix", $prefix);

        return $prefix;
    }

}

?>
