<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Scoring {

    public static function calculate_result( $answers ) {
        $map = Avayaar_Family_Weights::get_map();
        $w   = Avayaar_Family_Weights::STAGE_WEIGHTS;

        $totals = array( 'string' => 0, 'keys' => 0, 'wind' => 0, 'perc' => 0, 'plucked' => 0, 'vocal' => 0, 'iranian' => 0 );

        $rhythm_tier = self::rhythm_tier( $answers['rhythm'] ?? array() );
        self::add( $totals, $map['rhythm_tier'][ $rhythm_tier ] ?? array(), $w['rhythm'] );

        $ear = $answers['ear'] ?? array();
        self::add( $totals, $map['ear_tempo'][ $ear['tempo'] ?? '' ] ?? array(), $w['ear'] );
        self::add( $totals, $map['ear_regularity'][ $ear['regularity'] ?? '' ] ?? array(), $w['ear'] );

        foreach ( ( $answers['style'] ?? array() ) as $genre ) {
            self::add( $totals, $map['style'][ $genre ] ?? array(), $w['style'] );
        }

        $personality = $answers['personality'] ?? array();
        self::add( $totals, $map['personality_vacation'][ $personality['vacation'] ?? '' ] ?? array(), $w['personality'] );
        self::add( $totals, $map['personality_stage'][ $personality['stage'] ?? '' ] ?? array(), $w['personality'] );

        self::add( $totals, $map['mood'][ $answers['mood'] ?? '' ] ?? array(), $w['mood'] );

        arsort( $totals );
        $top_families = array_slice( array_keys( $totals ), 0, 3 );
        $max_score    = reset( $totals ) ?: 1;

        $top_with_pct = array();
        foreach ( $top_families as $family ) {
            // Always land in a flattering 65–96% band — this is a match
            // indicator, not a graded score, by design.
            $pct = 65 + (int) round( ( $totals[ $family ] / $max_score ) * 31 );
            $top_with_pct[ $family ] = min( 96, $pct );
        }

        $badge = self::determine_badge( $rhythm_tier, $ear, $answers['style'] ?? array() );

        return array(
            'top_families'            => $top_with_pct,
            'badge'                   => $badge,
            'recommended_instruments' => self::resolve_instruments( array_keys( $top_with_pct ) ),
        );
    }

    private static function add( &$totals, $vector, $stage_weight ) {
        foreach ( $vector as $family => $points ) {
            if ( isset( $totals[ $family ] ) ) $totals[ $family ] += $points * $stage_weight;
        }
    }

    private static function rhythm_tier( $rhythm_answers ) {
        if ( empty( $rhythm_answers ) ) return 'steady';

        $deltas = array();
        foreach ( $rhythm_answers as $entry ) {
            foreach ( ( $entry['tap_deltas_ms'] ?? array() ) as $d ) $deltas[] = abs( (float) $d );
        }
        if ( empty( $deltas ) ) return 'steady';

        $avg = array_sum( $deltas ) / count( $deltas );
        if ( $avg < 120 ) return 'agile';
        if ( $avg < 250 ) return 'steady';
        return 'relaxed';
    }

    private static function determine_badge( $rhythm_tier, $ear, $styles ) {
        if ( $rhythm_tier === 'agile' ) return array( 'key' => 'rhythm_master', 'label' => 'استاد ریتم', 'emoji' => '🥁' );
        if ( count( $styles ) >= 3 )   return array( 'key' => 'melody_hunter', 'label' => 'شکارچی ملودی', 'emoji' => '🎼' );
        return array( 'key' => 'good_listener', 'label' => 'شنونده خوب', 'emoji' => '🎵' );
    }

    private static function resolve_instruments( $family_keys ) {
        $term_ids_by_family = Avayaar_Recommendations::get_family_term_ids();
        $result = array();

        foreach ( $family_keys as $family ) {
            $term_id = $term_ids_by_family[ $family ] ?? 0;
            if ( ! $term_id ) continue;

            $posts = get_posts( array(
                'post_type'   => 'mam_instrument',
                'post_status' => 'publish',
                'numberposts' => 2,
                'tax_query'   => array( array(
                    'taxonomy' => Avayaar_Recommendations::TAXONOMY,
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                ) ),
            ) );

            foreach ( $posts as $p ) {
                $result[] = array( 'id' => $p->ID, 'title' => get_the_title( $p ), 'url' => get_permalink( $p ), 'family' => $family );
            }
        }

        return $result;
    }
}