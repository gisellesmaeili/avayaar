<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display labels/descriptions for the 4 archetype keys computed by
 * Avayaar_Scoring. Edited directly in code — no wp-admin UI.
 */
class Avayaar_Archetypes {

    public static function get_all() {
        return array(
            'rhythm_driven' => array(
                'label'       => 'ریتم‌شناس',
                'description' => 'حس ریتم قوی داری و به‌سرعت الگوهای ضربی رو می‌گیری.',
            ),
            'ear_driven' => array(
                'label'       => 'گوش‌نواز',
                'description' => 'گوش حساسی برای تشخیص تفاوت‌های ظریف صدا داری.',
            ),
            'balanced' => array(
                'label'       => 'استعداد متعادل',
                'description' => 'ترکیب خوبی از حس ریتم و شنوایی داری؛ نقطه شروع قوی.',
            ),
            'beginner_friendly' => array(
                'label'       => 'آماده شروع',
                'description' => 'نقطه شروع خوبی داری؛ با تمرین منظم به‌سرعت پیشرفت می‌کنی.',
            ),
        );
    }

    public static function get( $key ) {
        $all = self::get_all();
        return $all[ $key ] ?? $all['beginner_friendly'];
    }
}