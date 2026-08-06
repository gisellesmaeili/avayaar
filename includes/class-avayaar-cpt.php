<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AVAYAAR_CPT {

    public function __construct() {
        add_action( 'init', array( $this, 'register_question_cpt' ) );
    }

    public function register_question_cpt() {
        register_post_type( 'avayaar_question', array(
            'labels' => array(
                'name'          => 'سوالات آزمون',
                'singular_name' => 'سوال آزمون',
            ),
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'avayaar', // nests under our top-level menu, no separate entry
            'supports'     => array( 'title' ),
            'capability_type' => 'post',
        ) );
    }
}