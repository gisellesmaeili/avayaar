<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Avayaar_Questions {

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

    // Stage 1 — Musical Ear (self-report, no correct answer)
    public static function get_ear_stage() {
        return array(
            'tempo_clip' => array( 'bpm' => 96, 'pattern' => array( 1, 1, 1, 1 ) ),
            'tempo_question' => array(
                'text'    => 'این ضرب رو چطور احساس کردی؟',
                'options' => array( 'calm' => 'آروم', 'normal' => 'معمولی', 'fast' => 'تند' ),
            ),
            'regularity_clips' => array(
                'a' => array( 'bpm' => 100, 'pattern' => array( 1, 1, 1, 1 ) ),
                'b' => array( 'bpm' => 100, 'pattern' => array( 0.5, 1.5, 0.5, 1.5 ) ),
            ),
            'regularity_question' => array(
                'text'    => 'کدوم ریتم منظم‌تر به نظر رسید؟',
                'options' => array( 'a' => 'ریتم اول', 'b' => 'ریتم دوم' ),
            ),
        );
    }

    // Stage 3 — Music Style. TODO: replace 'file' with your real clip filenames
    // once sourced, placed under assets/audio/.
    public static function get_style_clips() {
        return array(
            array( 'id' => 'classical',   'label' => 'کلاسیک',   'file' => 'classical.mp3' ),
            array( 'id' => 'traditional', 'label' => 'سنتی',     'file' => 'traditional.mp3' ),
            array( 'id' => 'rock',        'label' => 'راک',      'file' => 'rock.mp3' ),
            array( 'id' => 'pop',         'label' => 'پاپ',      'file' => 'pop.mp3' ),
            array( 'id' => 'jazz',        'label' => 'جز',       'file' => 'jazz.mp3' ),
        );
    }

    // Stage 4 — Personality
    public static function get_personality_questions() {
        return array(
            array(
                'id'      => 'vacation',
                'text'    => 'برای تعطیلات چی رو ترجیح می‌دی؟',
                'options' => array( 'nature' => '🏔 طبیعت', 'city' => '🏙 شهر', 'home' => '🏠 خونه' ),
            ),
            array(
                'id'      => 'stage',
                'text'    => 'اگه قرار بود روی صحنه اجرا کنی...',
                'options' => array( 'excited' => '😀 هیجان‌زده می‌شدم', 'normal' => '😐 عادی بودم', 'stressed' => '😨 استرس می‌گرفتم' ),
            ),
        );
    }

    // Stage 5 — Mood (CSS-tile based, no image assets needed)
    public static function get_mood_options() {
        return array(
            array( 'id' => 'rain',   'label' => 'بارون',   'emoji' => '🌧' ),
            array( 'id' => 'ocean',  'label' => 'دریا',     'emoji' => '🌊' ),
            array( 'id' => 'fire',   'label' => 'آتیش',     'emoji' => '🔥' ),
            array( 'id' => 'flower', 'label' => 'شکوفه',    'emoji' => '🌸' ),
        );
    }

    // ---------- Screen 02 — Meet You (profiling only, never scored) ----------

    public static function get_age_ranges() {
        return array(
            array( 'id' => '12-17', 'label' => '۱۲–۱۷' ),
            array( 'id' => '18-24', 'label' => '۱۸–۲۴' ),
            array( 'id' => '25-34', 'label' => '۲۵–۳۴' ),
            array( 'id' => '35-44', 'label' => '۳۵–۴۴' ),
            array( 'id' => '45+',   'label' => '۴۵+' ),
        );
    }

    public static function get_music_history_options() {
        return array(
            array( 'id' => 'listener', 'label' => 'فقط گوش می‌دم' ),
            array( 'id' => 'sings',    'label' => 'می‌خونم' ),
            array( 'id' => 'plays',    'label' => 'ساز می‌زنم' ),
            array( 'id' => 'played',   'label' => 'قبلاً ساز می‌زدم' ),
            array( 'id' => 'starting', 'label' => 'تازه می‌خوام شروع کنم' ),
            array( 'id' => 'unsure',   'label' => 'هنوز مطمئن نیستم' ),
        );
    }

    public static function get_instrument_options() {
        return array(
            array( 'id' => 'piano',  'label' => 'پیانو' ),
            array( 'id' => 'guitar', 'label' => 'گیتار' ),
            array( 'id' => 'violin', 'label' => 'ویولن' ),
            array( 'id' => 'santur', 'label' => 'سنتور' ),
            array( 'id' => 'tar',    'label' => 'تار' ),
            array( 'id' => 'drums',  'label' => 'درامز' ),
            array( 'id' => 'vocal',  'label' => 'آواز' ),
            array( 'id' => 'other',  'label' => 'سازی دیگه' ),
        );
    }

    public static function get_why_music_options() {
        return array(
            array( 'id' => 'self',       'label' => 'برای خودم' ),
            array( 'id' => 'perform',    'label' => 'برای اجرا' ),
            array( 'id' => 'serious',    'label' => 'برای یادگیری جدی' ),
            array( 'id' => 'experience', 'label' => 'برای تجربه کردن' ),
            array( 'id' => 'career',     'label' => 'برای مسیر حرفه‌ای' ),
            array( 'id' => 'focus',      'label' => 'برای رشد و تمرکز' ),
            array( 'id' => 'unsure',     'label' => 'هنوز نمی‌دونم' ),
        );
    }
}