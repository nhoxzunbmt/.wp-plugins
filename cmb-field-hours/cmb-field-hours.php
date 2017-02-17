<?php
/*
Plugin Name: CMB2 Field Type: Hours
Depends: CMB2
Description: Adds <code>hours</code> as a feild type to CMB2
Version: 1.0.0
Author: Jeremy Turowetz
Author URI: https://jeremy.turowetz.com
License: GPLv2+
*/


add_filter( 'cmb2_render_hours', 'cmb2_render_hours_field_callback', 10, 5 );
function cmb2_render_hours_field_callback( $field, $value, $object_id, $object_type, $field_type ) {

    // make sure we specify each part of the value we need.
    $value = wp_parse_args( $value, array(
        'opening_time' => '',
        'closing_time' => '',
    ) );

    ?>
    <label for="<?php echo $field_type->_id( '_opening_time' ); ?>">Opens at:</label>
    <?php echo $field_type->input( array(
        'class' => 'cmb_text_time',
        'name'  => $field_type->_name( '[opening_time]' ),
        'id'    => $field_type->_id( '_opening_time' ),
        'value' => $value['opening_time'],
        'type'  => 'time',
        'desc'  => '',
    ) ); ?>
    &nbsp;
    <label for="<?php echo $field_type->_id( '_closing_time' ); ?>'">Closes at:</label>
    <?php echo $field_type->input( array(
        'class' => 'cmb_text_time',
        'name'  => $field_type->_name( '[closing_time]' ),
        'id'    => $field_type->_id( '_closing_time' ),
        'value' => $value['closing_time'],
        'type'  => 'time',
        'desc'  => '',
    ) ); ?>
    <br class="clear">
    <?php
    echo $field_type->_desc( true );

}