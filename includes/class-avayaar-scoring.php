<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Scoring {

    public static function calculate_result( $answers ) {
        $rhythm_score = self::score_rhythm( $answers['rhythm'] ?? array() );
        $ear_score    = self::score_ear( $answers['ear'] ?? array() );
        $archetype    = self::determine_archetype( $rhythm_score, $ear_score );

        $preference = $answers['goals']['g3'] ?? 'none';
        $age        = $answers['goals']['g2'] ?? '';

        $resolved_ids = self::get_recommended_instruments( $archetype, $preference, $age );

        return array(
            'module_scores'           => array( 'rhythm' => $rhythm_score, 'ear' => $ear_score ),
            'archetype'                => $archetype,
            'recommended_instruments'  => $resolved_ids,
        );
    }

    private static function score_rhythm( $rhythm_answers ) {
        if ( empty( $rhythm_answers ) ) return 0;

        $total_accuracy = 0;
        $count = 0;

        foreach ( $rhythm_answers as $entry ) {
            foreach ( ( $entry['tap_deltas_ms'] ?? array() ) as $delta_ms ) {
                $abs_delta = abs( (float) $delta_ms );
                $total_accuracy += max( 0, 1 - ( $abs_delta / 300 ) ); // 100ms=near-perfect, 300ms+=0 credit
                $count++;
            }
        }

        return $count > 0 ? round( ( $total_accuracy / $count ) * 100 ) : 0;
    }

    private static function score_ear( $ear_answers ) {
        if ( empty( $ear_answers ) ) return 0;

        $by_id = array();
        foreach ( Avayaar_Questions::get_ear_questions() as $q ) $by_id[ $q['id'] ] = $q;

        $correct = 0;
        foreach ( $ear_answers as $entry ) {
            $q = $by_id[ $entry['id'] ] ?? null;
            if ( $q && $entry['answer'] === $q['correct'] ) $correct++;
        }

        return round( ( $correct / count( $ear_answers ) ) * 100 );
    }

    private static function determine_archetype( $rhythm_score, $ear_score ) {
        if ( $rhythm_score >= 70 && $ear_score >= 70 ) return 'balanced';
        if ( $rhythm_score >= $ear_score + 15 )        return 'rhythm_driven';
        if ( $ear_score >= $rhythm_score + 15 )        return 'ear_driven';
        return 'beginner_friendly';
    }

    private static function get_recommended_instruments( $archetype, $preference, $age ) {
        $family_ids = Avayaar_Recommendations::get_family_term_ids();

        if ( $preference !== 'none' && ! empty( $family_ids[ $preference ] ) ) {
            $family_term_id = $family_ids[ $preference ];
        } else {
            $fallback_key   = Avayaar_Recommendations::get_archetype_fallback_family()[ $archetype ] ?? 'keys';
            $family_term_id = $family_ids[ $fallback_key ] ?? 0;
        }

        if ( empty( $family_term_id ) ) return array();

        $children_term_id = ( $age === 'child' ) ? Avayaar_Recommendations::get_children_term_id() : 0;

        if ( $children_term_id ) {
            $ids = self::query_by_terms( array( $family_term_id, $children_term_id ) );
            if ( ! empty( $ids ) ) return $ids;
        }

        return self::query_by_terms( array( $family_term_id ) );
    }

    private static function query_by_terms( $term_ids ) {
        $posts = get_posts( array(
            'post_type'   => 'mam_instrument',
            'post_status' => 'publish',
            'numberposts' => -1,
            'tax_query'   => array(
                array(
                    'taxonomy' => Avayaar_Recommendations::TAXONOMY,
                    'field'    => 'term_id',
                    'terms'    => $term_ids,
                    'operator' => count( $term_ids ) > 1 ? 'AND' : 'IN',
                ),
            ),
        ) );

        return wp_list_pluck( $posts, 'ID' );
    }
}