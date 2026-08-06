<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Maps quiz preference/archetype to mam_instrument taxonomy terms.
 * Edited directly in code — no wp-admin UI for this by design.
 */
class Avayaar_Recommendations {
    const TAXONOMY = 'mam_instrument_category';

    public static function get_family_term_ids() {
        return array(
            'string'  => 6, // TODO: String Instruments
            'keys'    => 7, // TODO: Keyboard Instruments
            'wind'    => 8, // TODO: Wind Instruments
            'perc'    => 9, // TODO: Percussion Instruments
            'plucked' => 11, // TODO: Plucked & Hammered Instruments
            'vocal'   => 12, // TODO: Vocal & Music Education
            'iranian' => 10, // TODO: Iranian Traditional Instruments
        );
    }

    /**
     * Only used when the user answered "none" (not sure) to g3.
     * Picks a sensible default family based on aptitude archetype.
     */
    public static function get_archetype_fallback_family() {
        return array(
            'rhythm_driven'     => 'perc',
            'ear_driven'        => 'vocal',
            'balanced'          => 'keys',
            'beginner_friendly' => 'vocal',
        );
    }

    /**
     * Term ID for "Children's Music" — used as a bias, not a standalone bucket.
     */
    public static function get_children_term_id() {
        return 13; // TODO: Children's Music
    }

}