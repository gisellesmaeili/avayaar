<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Maps every possible answer, across every stage, to a point vector
 * across the 7 instrument families (string/keys/wind/perc/plucked/
 * vocal/iranian). Fully editable — this is marketing/product judgment,
 * not deep logic. Tune freely; nothing here is load-bearing for
 * correctness, only for which instruments get recommended.
 */
class Avayaar_Family_Weights {

    // How much each stage's signal counts toward the final match —
    // mirrors the "Rhythm ×4 / Ear ×5 / Interest ×3 / Personality ×2" idea.
    const STAGE_WEIGHTS = array(
        'ear'         => 5,
        'rhythm'      => 4,
        'style'       => 3,
        'personality' => 2,
        'mood'        => 1,
    );

    public static function get_map() {
        return array(

            // Stage 2 — rhythm tap accuracy, bucketed into a tier (never "wrong")
            'rhythm_tier' => array(
                'agile'   => array( 'perc' => 3, 'string' => 1, 'plucked' => 1 ),
                'steady'  => array( 'keys' => 2, 'string' => 1, 'iranian' => 1 ),
                'relaxed' => array( 'vocal' => 2, 'keys' => 1 ),
            ),

            // Stage 1 — self-reported tempo feel, no correct answer
            'ear_tempo' => array(
                'calm'   => array( 'vocal' => 2, 'keys' => 1 ),
                'normal' => array( 'string' => 1, 'keys' => 1, 'wind' => 1 ),
                'fast'   => array( 'perc' => 2, 'string' => 1 ),
            ),

            // Stage 1 — "which sounded more regular", self-reported preference
            'ear_regularity' => array(
                'a' => array( 'perc' => 1, 'iranian' => 1 ),
                'b' => array( 'wind' => 1, 'vocal' => 1 ),
            ),

            // Stage 3 — liked music clips (TODO: match genres to your real clips)
            'style' => array(
                'classical'  => array( 'keys' => 3, 'string' => 2, 'wind' => 1 ),
                'traditional'=> array( 'iranian' => 3, 'plucked' => 1 ),
                'rock'       => array( 'string' => 3, 'perc' => 1 ),
                'pop'        => array( 'vocal' => 3, 'keys' => 1 ),
                'jazz'       => array( 'wind' => 2, 'keys' => 2, 'perc' => 1 ),
            ),

            // Stage 4 — quick personality questions
            'personality_vacation' => array(
                'nature' => array( 'iranian' => 2, 'wind' => 1 ),
                'city'   => array( 'string' => 2, 'vocal' => 1 ),
                'home'   => array( 'keys' => 2, 'plucked' => 1 ),
            ),
            'personality_stage' => array(
                'excited'  => array( 'vocal' => 2, 'perc' => 2 ),
                'normal'   => array( 'keys' => 1, 'string' => 1 ),
                'stressed' => array( 'plucked' => 1, 'wind' => 1 ),
            ),

            // Stage 5 — mood tile (purely flavor, low weight)
            'mood' => array(
                'rain'   => array( 'wind' => 2, 'keys' => 1 ),
                'ocean'  => array( 'vocal' => 2, 'string' => 1 ),
                'fire'   => array( 'perc' => 2, 'string' => 1 ),
                'flower' => array( 'iranian' => 1, 'plucked' => 2 ),
            ),
        );
    }
}