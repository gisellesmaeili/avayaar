<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Score-band + stated preference → recommended mam_instrument post IDs.
 * Edited directly in code. Find each instrument's ID by opening it in
 * wp-admin and reading the "post=123" number in the address bar.
 */
class Avayaar_Recommendations {

    public static function get_rules() {
        return array(
            'rhythm_driven' => array(
                'string' => array( 0 ), // TODO: گیتار، ویولن
                'keys'   => array( 0 ), // TODO: پیانو
                'perc'   => array( 0 ), // TODO: تنبک، دف
                'none'   => array( 0 ),
            ),
            'ear_driven' => array(
                'string' => array( 0 ),
                'keys'   => array( 0 ),
                'perc'   => array( 0 ),
                'none'   => array( 0 ),
            ),
            'balanced' => array(
                'string' => array( 0 ),
                'keys'   => array( 0 ),
                'perc'   => array( 0 ),
                'none'   => array( 0 ),
            ),
            'beginner_friendly' => array(
                'string' => array( 0 ),
                'keys'   => array( 0 ),
                'perc'   => array( 0 ),
                'none'   => array( 0 ),
            ),
        );
    }
}