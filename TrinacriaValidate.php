<?php
defined('DIRECT_INCLUDE') or exit('RESTRICTED ACCESS');

class TrinacriaValidate {
    const NUMBER_DECIMAL = 'decimal';
    const NUMBER_DEC_PREC = 'dec_prec';
    const NUMBER_MIN = 'min';
    const NUMBER_MAX = 'max';
    
    const NUMBER_DECIMAL_DISABLED = false;
    const NUMBER_DECIMAL_DOT = '.';
    const NUMBER_DECIMAL_COMA = ',';
    const NUMBER_DECIMAL_COMADOT = '.,';
    
    // Import of http://pear.php.net/package/Validate
    /**
     * Validate a number
     *
     * @param string $number  Number to validate
     * @param array  $options array where:
     *     'decimal'  is the decimal char or false when decimal
     *                not allowed.
     *                i.e. ',.' to allow both ',' and '.'
     *     'dec_prec' Number of allowed decimals
     *     'min'      minimum value
     *     'max'      maximum value
     *
     * @return boolean true if valid number, false if not
     *
     * @access public
     */
    static public function number($number, $options = array()) {
        $decimal = $dec_prec = $min = $max = null;

        if(is_array($options)) {
            extract($options);
        }

        $dec_prec  = $dec_prec ? "{1,$dec_prec}" : '+';
        $dec_regex = $decimal  ? "[$decimal][0-9]$dec_prec" : '';

        if(!preg_match("|^[-+]?\s*[0-9]+($dec_regex)?\$|", $number)) {
            return false;
        }

        if($decimal != '.') {
            $number = strtr($number, $decimal, '.');
        }

        $number = (float)str_replace(' ', '', $number);

        if($min !== null && $min > $number) {
            return false;
        }

        if($max !== null && $max < $number) {
            return false;
        }

        return true;
    }
}
?>
