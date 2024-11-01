<?php
/**
 * Holds the Spiral Member Login widget class
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPMLS_Spiral_Member_Login_Widget' ) ) :

/**
 * Spiral Member Login widget class
 *
 * @since 1.0.0
 */
class WPMLS_Spiral_Member_Login_Widget extends WP_Widget {
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $widget_options = array(
            'classname'   => 'widget_spiral_v2_member_login',
            'description' => esc_html__( 'SPIRAL Login widget for your site', 'spiral-member-login' )
        );
        parent::__construct( 'spiral_member_login', esc_html__( 'WP Member Login by SPIRAL', 'spiral-member-login' ), $widget_options );
    }

    /**
     * Displays the widget
     *
     * @since 1.0.0
     *
     * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
     * @param array $instance The settings for the particular instance of the widget
     */
    public function widget( $args, $instance ) {
        $spiral_member_login = WPMLS_Spiral_Member_Login::get_instance();

        $instance = wp_parse_args( $instance, array(
            'default_action'       => 'login',
            'logged_in_widget'     => true,
            'logged_out_widget'    => true,
            'show_name'            => true,
            'show_title'           => true,
            'show_reg_link'        => true,
            'show_pass_link'       => true,
            'show_profile_link'    => true,
            'show_resetpass_link'  => true,
            'show_withdrawal_link' => true,
            'enable_blank_tab'     => false
        ) );

        // Show if logged in?
        if ( $spiral_member_login->is_logged_in() && ! $instance['logged_in_widget'] ) {
            return;
        }

        // Show if logged out?
        if ( ! $spiral_member_login->is_logged_in() && ! $instance['logged_out_widget'] ) {
            return;
        }

        if ( WPMLS_Spiral_Member_Login::is_sml_page() && $spiral_member_login->get_page_action( get_the_ID() ) == 'login' ) {
            return;
        }

        $args = array_merge( $args, $instance );
        $args_widget = array_merge( $args, ['is_widget' => true] );

        echo $spiral_member_login->shortcode_show_template( $args_widget );
    }

    /**
     * Updates the widget
     *
     * @since 1.0.0
     *
     * @param array $new_instance New settings for the widget instance
     * @param array $old_instance Old settings for the widget instance
     * @return array Updated instance settings
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['default_action']       = in_array( $new_instance['default_action'], array( 'login', 'register', 'lostpassword' ) ) ? $new_instance['default_action'] : 'login';
        $instance['logged_in_widget']     = ! empty( $new_instance['logged_in_widget'] );
        $instance['logged_out_widget']    = ! empty( $new_instance['logged_out_widget'] );
        $instance['show_name']            = ! empty( $new_instance['show_name'] );
        $instance['show_title']           = ! empty( $new_instance['show_title'] );
        $instance['show_reg_link']        = ! empty( $new_instance['show_reg_link'] );
        $instance['show_pass_link']       = ! empty( $new_instance['show_pass_link'] );
        $instance['show_profile_link']    = ! empty( $new_instance['show_profile_link'] );
        $instance['show_resetpass_link']  = ! empty( $new_instance['show_resetpass_link'] );
        $instance['show_withdrawal_link'] = ! empty( $new_instance['show_withdrawal_link'] );
        $instance['enable_blank_tab']     = ! empty( $new_instance['enable_blank_tab'] );
        update_option( 'is_blank_page', $instance['enable_blank_tab'] );
        return $instance;
    }

    /**
     * Displays the widget admin form
     *
     * @since 1.0.0
     *
     * @param array $instance Current settings
     */
    public function form( $instance ) {
        $defaults = array(
            'default_action'       => 'login',
            'logged_in_widget'     => 1,
            'logged_out_widget'    => 1,
            'show_name'            => 1,
            'show_title'           => 1,
            'show_reg_link'        => 1,
            'show_pass_link'       => 1,
            'show_profile_link'    => 1,
            'show_resetpass_link'  => 1,
            'show_withdrawal_link' => 1,
            'register_widget'      => 1,
            'enable_blank_tab'     => 0,
            'lostpassword_widget'  => 1
        );
        $instance = wp_parse_args( $instance, $defaults );

        $checkboxes = array(
            'logged_in_widget'     => __( 'Show When Logged In', 'spiral-member-login' ),
            'logged_out_widget'    => __( 'Show When Logged Out', 'spiral-member-login' ),
            'show_title'           => __( 'Show Title', 'spiral-member-login' ),
            'show_name'            => __( 'Show Name', 'spiral-member-login' ),
            'show_reg_link'        => __( 'Show Register Link', 'spiral-member-login' ),
            'show_pass_link'       => __( 'Show Reset Password Link', 'spiral-member-login' ),
            'show_profile_link'    => __( 'Show Profile Link', 'spiral-member-login' ),
            'show_resetpass_link'  => __( 'Show Lost Password Link', 'spiral-member-login' ),
            'show_withdrawal_link' => __( 'Show Withdrawal Link', 'spiral-member-login' ),
            'enable_blank_tab'     => __( 'Open link with new tab', 'spiral-member-login' ),
        );

        foreach ( $checkboxes as $key => $label ) {
            $is_checked = ! empty( $instance[ $key ] ) ? 'checked="checked"' : '';
            echo '<p><input name="' . esc_attr( $this->get_field_name( $key ) ) . '" type="checkbox" id="' . esc_attr( $this->get_field_id( $key ) ) . '" value="1" ' . $is_checked . '/> <label for="' . esc_attr( $this->get_field_id( $key ) ) . '">' . esc_html( $label ) . '</label></p>' . "\n";
            echo ($key == "logged_out_widget") ?  '<hr>' : '';
        }
    }
}

endif; // Class exists