<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Scoring {

    public static function calculate_result( $answers ) {
        $rhythm_score = self::score_rhythm( $answers['rhythm'] ?? array() );
        $ear_score    = self::score_ear( $answers['ear'] ?? array() );
        $archetype    = self::determine_archetype( $rhythm_score, $ear_score );

        $preference       = $answers['goals']['g3'] ?? 'none';
        $instrument_ids   = self::get_recommended_ids( $archetype, $preference );
        $resolved_ids     = self::resolve_instrument_posts( $instrument_ids );

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

    private static function get_recommended_ids( $archetype, $preference ) {
        $rules = Avayaar_Recommendations::get_rules();
        return $rules[ $archetype ][ $preference ] ?? $rules[ $archetype ]['none'] ?? array();
    }

    private static function resolve_instrument_posts( $instrument_ids ) {
        $instrument_ids = array_filter( array_map( 'intval', $instrument_ids ) );
        if ( empty( $instrument_ids ) ) return array();

        $posts = get_posts( array(
            'post_type'   => 'mam_instrument',
            'post_status' => 'publish',
            'post__in'    => $instrument_ids,
            'orderby'     => 'post__in',
            'numberposts' => -1,
        ) );

        return wp_list_pluck( $posts, 'ID' );
    }
}