<?php

/**
 * Class for file based caching
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

        $filename = $this->build_file_name($key);

        if(file_exists($filename)){

            $file_data = json_decode(file_get_contents($filename));

            if(isset($file_data["ttl"]) && isset($file_data["data"])){

                if(time() < $file_data["ttl"]){

                    $data = $file_data["data"];

                }

            }

        }

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

        $filename = $this->build_file_name($key);

        if(!isset($ttl)){
            $ttl = $WC["CACHE_SETTINGS"]["default_ttl"];
        }

        $file_data = array(
            "ttl"  => time() + $ttl,
            "data" => $data
        );

        $success = file_put_contents($filename, $file_data);

        return (bool)$success;
    }


    /**
     * Clears all cached items
     *
     * @access  public
     * @param   string      $dir    Optional dir used in recursion
     * @return  void
     *
     */
    public function clear($dir="") {

        global $WC;

        if(empty($dir)) $dir = $WC["CACHE_SETTINGS"]["dir"];

        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if($entry!="." && $entry!=".."){

                $filename = $dir."/$entry";

                if(is_dir($filename)){
                    $this->clear($filename);
                }

                unlink($filename);
            }
        }
        $d->close();

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

        $fp = @fopen($WC["CACHE_SETTINGS"]["dir"]."/test.txt", "w");
        if($fp){
            $success = true;
            unlink($WC["CACHE_SETTINGS"]["dir"]."/test.txt");
        }

        return $success;

    }


    /**
     * Builds the file name for the cache file
     *
     * @access  private
     * @param   string      $key    The key to build the file name
     * @return  string
     *
     */
    private function build_file_name($key) {

        global $WC;

        $filename = $WC["CACHE_SETTINGS"]["dir"]."/".preg_replace('![^a-z0-9]!i', "_", $key).".php";

        return $filename;
    }

}

?>
