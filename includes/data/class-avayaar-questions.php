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

    // Real instruments — queries the mam_instrument CPT, filtered to any
    // post tagged under one of the family term IDs already defined in
    // Avayaar_Recommendations::get_family_term_ids(). Same taxonomy you
    // already use for recommendations, just queried directly here.
    public static function get_instrument_options() {
        if ( ! post_type_exists( 'mam_instrument' ) ) {
            return array();
        }

        $term_ids = array_values( Avayaar_Recommendations::get_family_term_ids() );

        $posts = get_posts( array(
            'post_type'   => 'mam_instrument',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
            'tax_query'   => array( array(
                'taxonomy' => Avayaar_Recommendations::TAXONOMY,
                'field'    => 'term_id',
                'terms'    => $term_ids,
            ) ),
        ) );

        $options = array();
        foreach ( $posts as $p ) {
            $options[] = array( 'id' => $p->ID, 'label' => get_the_title( $p ) );
        }
        $options[] = array( 'id' => 'other', 'label' => 'سازی دیگه' );

        return $options;
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

    // ---------- Chapter 01 — Rhythm question bank (Screens 06–13) ----------
    // Small pools for now — expand each array to grow variety; the
    // adaptive "pick 4–5 from a pool of 20" selector is a future step.
    public static function get_rhythm_bank() {
        return array(
            'speed_pairs' => array(
                array( 'id' => 'sp1', 'a' => array( 'bpm' => 70, 'pattern' => array( 1, 1, 1, 1 ) ), 'b' => array( 'bpm' => 150, 'pattern' => array( 1, 1, 1, 1 ) ) ),
            ),
            'memory_sets' => array(
                array(
                    'id'      => 'ms1',
                    'target'  => array( 'bpm' => 95, 'pattern' => array( 1, 0.5, 0.5, 1, 1 ) ),
                    'options' => array(
                        array( 'key' => 'A', 'bpm' => 95, 'pattern' => array( 1, 0.5, 0.5, 1, 1 ) ),
                        array( 'key' => 'B', 'bpm' => 95, 'pattern' => array( 0.5, 0.5, 1, 1, 1 ) ),
                        array( 'key' => 'C', 'bpm' => 95, 'pattern' => array( 1, 1, 0.5, 0.5, 1 ) ),
                    ),
                ),
            ),
            'missing_beat' => array(
                array( 'id' => 'mb1', 'bpm' => 100, 'units' => array( 1, 1, 1, 1, 1 ), 'missing_index' => 2 ),
            ),
            'energy_choice' => array(
                array( 'key' => 'A', 'bpm' => 80, 'pattern' => array( 1, 1, 1, 1 ) ),
                array( 'key' => 'B', 'bpm' => 120, 'pattern' => array( 0.5, 0.5, 1, 0.5, 0.5, 1 ) ),
                array( 'key' => 'C', 'bpm' => 100, 'pattern' => array( 1, 0.5, 0.5, 1, 1 ) ),
            ),
            'sync' => array( 'bpm' => 90, 'beats' => 6 ),
            'personality_pair' => array(
                'a' => array( 'bpm' => 85, 'pattern' => array( 1, 1, 1, 1 ) ),
                'b' => array( 'bpm' => 115, 'pattern' => array( 0.5, 0.5, 1, 0.5, 1 ) ),
            ),
            'final_challenge' => array( 'bpm' => 100, 'pattern' => array( 1, 0.5, 0.5, 1, 0.5, 0.5, 1.5 ) ),
        );
    }

    // ---------- Chapter 02 — Your Ears question bank (Screens 15–30) ----------
    // Frequencies are approximate note values (Hz) — no audio files needed,
    // everything is synthesized client-side via Web Audio.
    public static function get_ear_bank() {
        return array(
            'low_high'        => array( 'a' => 330, 'b' => 660 ),
            'triplet_middle'  => array( 'freqs' => array( 392, 659, 494 ) ),
            'similarity_pair' => array( 'a' => 440, 'b' => 466 ),
            'odd_one'         => array( 'base' => 523, 'odd' => 587, 'odd_index' => 2 ),
            'direction'       => array( 'from' => 500, 'pan_from' => -1 ),
            'note_count'      => array( 'freqs' => array( 392, 440, 494, 523 ) ),
            'melody_direction'=> array( 'freqs' => array( 330, 392, 440, 523 ) ),
            'mode_pieces'     => array(
                'a' => array( 523, 622, 659, 784 ),
                'b' => array( 523, 587, 659, 698 ),
            ),
            'sound_memory' => array(
                'target'  => array( 392, 440, 523 ),
                'options' => array(
                    array( 392, 440, 523 ),
                    array( 392, 494, 523 ),
                    array( 440, 494, 587 ),
                ),
            ),
            'preference' => array(
                'options' => array(
                    array( 330, 392, 440 ),
                    array( 440, 523, 659 ),
                    array( 294, 349, 392 ),
                ),
            ),
            'final_melody' => array(
                'target'  => array( 392, 440, 494, 587, 659 ),
                'options' => array(
                    array( 392, 440, 494, 587, 659 ),
                    array( 392, 349, 494, 440, 659 ),
                    array( 330, 440, 494, 587, 698 ),
                    array( 392, 440, 523, 587, 659 ),
                ),
            ),
        );
    }
}