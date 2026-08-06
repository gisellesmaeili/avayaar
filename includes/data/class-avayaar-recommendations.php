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
                'string' => array( 209, 207, 190, 188, 169, 167, 165, 160 ), // TODO: گیتار، ویولن
                'keys'   => array( 162 ), // TODO: پیانو
                'perc'   => array( 224, 186, 184, 182, 180, 178 ), // TODO: تنبک، دف
                'none'   => array( 0 ),
            ),
            'ear_driven' => array(
                'string' => array( 209, 207, 190, 188, 169, 167, 165, 160 ),
                'keys'   => array( 162 ),
                'perc'   => array( 224, 186, 184, 182, 180, 178 ),
                'none'   => array( 0 ),
            ),
            'balanced' => array(
                'string' => array( 209, 207, 190, 188, 169, 167, 165, 160 ),
                'keys'   => array( 162 ),
                'perc'   => array( 224, 186, 184, 182, 180, 178 ),
                'none'   => array( 0 ),
            ),
            'beginner_friendly' => array(
                'string' => array( 209, 207, 190, 188, 169, 167, 165, 160 ),
                'keys'   => array( 162 ),
                'perc'   => array( 224, 186, 184, 182, 180, 178 ),
                'none'   => array( 0 ),
            ),
        );
    }
}