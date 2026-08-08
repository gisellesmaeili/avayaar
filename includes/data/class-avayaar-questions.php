<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Static question bank. Edited directly in code by design —
 * no wp-admin content management for this feature.
 */
class Avayaar_Questions {

    /**
     * Rhythm tap-along patterns, grouped by difficulty tier (1 = easiest).
     * 'pattern' values are beat durations in units (1 = quarter note at the given bpm).
     */
    public static function get_rhythm_patterns() {
        return array(
            1 => array(
                array( 'id' => 'r1_1', 'bpm' => 80, 'pattern' => array( 1, 1, 1, 1 ) ),
                array( 'id' => 'r1_2', 'bpm' => 80, 'pattern' => array( 1, 1, 2 ) ),
            ),
            2 => array(
                array( 'id' => 'r2_1', 'bpm' => 90, 'pattern' => array( 0.5, 0.5, 1, 1, 1 ) ),
                array( 'id' => 'r2_2', 'bpm' => 90, 'pattern' => array( 1, 0.5, 0.5, 1, 1 ) ),
            ),
            3 => array(
                array( 'id' => 'r3_1', 'bpm' => 100, 'pattern' => array( 1.5, 0.5, 1, 0.5, 0.5 ) ),
                array( 'id' => 'r3_2', 'bpm' => 100, 'pattern' => array( 0.5, 0.5, 0.5, 0.5, 2 ) ),
            ),
        );
    }

    /**
     * Ear-sense questions — same/different and longer/shorter only.
     * No musical background required, per client requirement.
     */
    public static function get_ear_questions() {
        return array(
            array( 'id' => 'e1', 'type' => 'same_or_different',
                'tone_a' => array( 'freq' => 440, 'duration_ms' => 600 ),
                'tone_b' => array( 'freq' => 440, 'duration_ms' => 600 ),
                'correct' => 'same' ),
            array( 'id' => 'e2', 'type' => 'same_or_different',
                'tone_a' => array( 'freq' => 440, 'duration_ms' => 600 ),
                'tone_b' => array( 'freq' => 494, 'duration_ms' => 600 ),
                'correct' => 'different' ),
            array( 'id' => 'e3', 'type' => 'longer_or_shorter',
                'tone_a' => array( 'freq' => 392, 'duration_ms' => 400 ),
                'tone_b' => array( 'freq' => 392, 'duration_ms' => 900 ),
                'correct' => 'b_longer' ),
            array( 'id' => 'e4', 'type' => 'longer_or_shorter',
                'tone_a' => array( 'freq' => 349, 'duration_ms' => 900 ),
                'tone_b' => array( 'freq' => 349, 'duration_ms' => 400 ),
                'correct' => 'a_longer' ),
        );
    }

    /**
     * Goals/preferences — not scored, drives recommendation only.
     */
    public static function get_goals_questions() {
        return array(
            array( 'id' => 'g1', 'question' => 'چرا مایل به یادگیری موسیقی هستید؟',
                'options' => array(
                    'hobby'   => 'به عنوان سرگرمی',
                    'kid_dev' => 'رشد فرزندم',
                    'serious' => 'دنبال کردن جدی موسیقی',
                    'social'  => 'فعالیت اجتماعی/دورهمی',
                ) ),
            array( 'id' => 'g2', 'question' => 'سن شما در چه بازه‌ای است؟',
                'options' => array(
                    'child' => 'کودک (زیر ۱۲ سال)',
                    'teen'  => 'نوجوان (۱۲ تا ۱۸ سال)',
                    'adult' => 'بزرگسال',
                ) ),
            array( 'id' => 'g3', 'question' => 'آیا سازی مد نظرتان است؟',
                'options' => array(
                    'string'  => 'سازهای زهی (گیتار، ویولن، ...)',
                    'keys'    => 'سازهای کلاویه‌ای (پیانو، ارگ، ...)',
                    'wind'    => 'سازهای بادی',
                    'perc'    => 'سازهای کوبه‌ای',
                    'plucked' => 'سازهای زهی مضرابی (سنتور، قانون، چنگ، ...)',
                    'vocal'   => 'آواز و آموزش موسیقی',
                    'iranian' => 'سازهای سنتی ایرانی',
                    'none'    => 'هنوز مشخص نیست',
                ) ),
        );
    }

    /**
     * Same as get_ear_questions() but strips the answer key —
     * this is what gets sent to the browser.
     */
    public static function get_ear_questions_frontend() {
        return array_map( function( $q ) {
            unset( $q['correct'] );
            return $q;
        }, self::get_ear_questions() );
    }
}