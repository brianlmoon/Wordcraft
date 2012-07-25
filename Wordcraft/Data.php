<?php

namespace Wordcraft;

/**
 * This is a library of static functions to assit in data manipulation and
 * validation.
 *
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   1997-Present
 * @package     Wordcraft
 *
 */

class Wordcraft_Data {

    /**
     * Validates a string representation of datetime and returns a unix timestamp
     *
     * @param   type    $var    desctription
     * @return  mixed
     *
     */
    public static function validate_time($datetime) {
        $int_time = $datetime + 0;
        if("$int_time" != "$datetime"){
            $int_time = strtotime($datetime);
        }
        return $int_time;
    }

    /**
     * Creates a LIKE clause for a query
     *
     * @param   $fields     Fields to be searched
     * @param   $search     The search string provided by the user
     * @return  string
     *
     */
    function create_like_string($fields, $search) {

        $tokens = self::tokenize_terms($search);

        $clauses = array();

        foreach($tokens as $token){

            // look for parentheses OR lists like (foo,bar) means foo OR bar
            if(preg_match('!\((.+?)\)!', $token, $match)){

                $sub_token = explode(",", $match[1]);

            } else {

                $sub_token = array($token);
            }

            $tok_clauses = array();

            foreach($sub_token as $sub){

                $sub = trim($sub);

                if($sub[0]=="-"){
                    $sub = substr($sub, 1);
                    $cond = "NOT LIKE";
                    $op = "AND";
                } else {
                    $cond = "LIKE";
                    $op = "OR";
                }

                if(preg_match('!"(.+?)"!', $sub, $match)){
                    $sub = $match[1];
                }

                $sub = mysql_escape_string($sub);

                foreach($fields as $field){

                    // if the term already has wildcarding, don't add more
                    if(strpos($sub, "%") === false){

                        if($sub[0] == "^"){
                            $sub = substr($sub, 1);
                        } else {
                            $sub = "%$sub";
                        }

                        if($sub[strlen($sub)-1] == '$'){
                            $sub = substr($sub, 0, -1);
                        } else {
                            $sub = "$sub%";
                        }

                    }

                    $tok_clauses[] = "$field $cond '$sub'";
                }

            }

            $clauses[] = "(".implode(" {$op} ", $tok_clauses).")";
        }

        return implode(" AND\n", $clauses);
    }


    /**
     * Tokenizes a string into an array of terms including negation and quoting
     *
     * @param   string  $search_string  The string to tokenize
     * @return  array
     *
     */
    public static function tokenize_terms( $search_string ) {
        // surround with spaces so matching is easier
        $search_string = " $search_string ";

        $paren_terms = array();
        if ( strstr( $search_string, '(' ) ) {
            // now pull out all grouped terms eg. (nano mini)
            preg_match_all( '/ ([+\-~]*\(.+?\)) /', $search_string, $tokenArray1 );
            $search_string = preg_replace( '/ [+\-~]*\(.+?\) /', ' ', $search_string );
            $paren_terms = $tokenArray1[1];
        }

        $quoted_terms = array();
        if ( strstr( $search_string, '"' ) ) {
            // first pull out all the double quoted strings (e.g. '"iMac DV" or -"iMac DV"')
            preg_match_all( '/ ([+\-~]*".+?") /', $search_string, $tokenArray0 );
            $search_string = preg_replace( '/ [+\-~]*".+?" /', ' ', $search_string );
            $quoted_terms = $tokenArray0[1];
        }

        // finally pull out the rest words in the string
        $norm_terms = preg_split( "/\s+/", $search_string, 0, PREG_SPLIT_NO_EMPTY );

        // merge them all together and return
        return array_merge( $quoted_terms, $paren_terms, $norm_terms );

    } // end of tokenizeTerms()

}

?>
