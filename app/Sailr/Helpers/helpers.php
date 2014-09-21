<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 21/09/2014
 * Time: 10:23 PM
 */

/**
 * Returns if the variable is an array or follows the Traversable (inc. Iteratable) interface
 * @param $var
 * @return bool
 */
function is_iterable($var) {
    return (is_array($var) || $var instanceof Traversable);
}