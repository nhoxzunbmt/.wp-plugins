<?php
/*
    Plugin Name: CMB2 Field Type: Address
    Depends: CMB2
    Description: Adds <code>address</code> as a feild type to CMB2
    Version: 1.0.0
    Author: Jeremy Turowetz
    Author URI: https://jeremy.turowetz.com
    License: GPLv2+
    Text Domain: cmb2-address
    Domain Path: /lang
*/


function cmb2_province_list() {
    $province_list = array(
      'BC' => array( 'name' => __( 'British Columbia', 'cmb2-address' ),            'abr' => __( 'B.C.',    'cmb2-address' ) ),
      'ON' => array( 'name' => __( 'Ontario', 'cmb2-address' ),                     'abr' => __( 'Ont.',    'cmb2-address' ) ),
      'NL' => array( 'name' => __( 'Newfoundland and Labrador', 'cmb2-address' ),   'abr' => __( 'N.L.',    'cmb2-address' ) ),
      'NS' => array( 'name' => __( 'Nova Scotia', 'cmb2-address' ),                 'abr' => __( 'N.S.',    'cmb2-address' ) ),
      'PE' => array( 'name' => __( 'Prince Edward Island', 'cmb2-address' ),        'abr' => __( 'P.E.I.',  'cmb2-address' ) ),
      'NB' => array( 'name' => __( 'New Brunswick', 'cmb2-address' ),               'abr' => __( 'N.B.',    'cmb2-address' ) ),
      'QC' => array( 'name' => __( 'Quebec', 'cmb2-address' ),                      'abr' => __( 'Que.',    'cmb2-address' ) ),
      'MB' => array( 'name' => __( 'Manitoba', 'cmb2-address' ),                    'abr' => __( 'Man.',    'cmb2-address' ) ),
      'SK' => array( 'name' => __( 'Saskatchewan', 'cmb2-address' ),                'abr' => __( 'Sask.',   'cmb2-address' ) ),
      'AB' => array( 'name' => __( 'Alberta', 'cmb2-address' ),                     'abr' => __( 'Alta.',   'cmb2-address' ) ),
      'NT' => array( 'name' => __( 'Northwest Territories', 'cmb2-address' ),       'abr' => __( 'N.W.T.',  'cmb2-address' ) ),
      'NU' => array( 'name' => __( 'Nunavut', 'cmb2-address' ),                     'abr' => __( 'Nvt.',    'cmb2-address' ) ),
      'YT' => array( 'name' => __( 'Yukon Territory', 'cmb2-address' ),             'abr' => __( 'Y.T.',    'cmb2-address' ) ),
    );
    return $province_list;
}



// Build Province Options for address field below
function cmb2_province_options( $value = false ) {

    $province_list = cmb2_province_list();
    ksort( $province_list );

    $province_options = '';
    foreach ( $province_list as $key => $province ) {
        $province_options .= '<option value="'. $key .'" '. selected( $value, $key, false ) .'>'. $province['name'] .'</option>';
    }

    return $province_options;
}


// Build Override Options
function cmb2_address_override_options( $value = false ) {

  $override_list = array(
    'normal'            => __( 'Normal Address', 'cmb2-address' ),
    'national'          => __( 'National', 'cmb2-address' ),
    'international'     => __( 'International', 'cmb2-address' ),
    );

    $override_options = '';
    foreach ( $override_list as $val => $item_name ) {
        $override_options .= '<option value="'. $val .'" '. selected( $value, $val, false ) .'>'. $item_name .'</option>';
    }

    return $override_options;
}




//Build A CMB2 address feild
add_filter( 'cmb2_render_address', 'cmb2_render_address_field_callback', 10, 5 );
function cmb2_render_address_field_callback( $field, $value, $object_id, $object_type, $field_type ) {

    // make sure we specify each part of the value we need.
    $value = wp_parse_args( $value, array(
        'override'   => '',
        'address-1'     => '',
        'address-2'     => '',
        'city'          => '',
        'province'      => '',
        'zip'           => '',
    ) );

    ?>
    <div><p><label for="<?php echo $field_type->_id( '_override' ); ?>">Override Address</label></p>
        <?php echo $field_type->select( array(
            'name'      => $field_type->_name( '[override]' ),
            'id'        => $field_type->_id( '_override' ),
            'options'   => cmb2_address_override_options( $value['override'] ),
            'desc'      => ' <small><em>Selecting National or International will override the rest of the address options below</em></small>',
        ) ); ?>
    <p>&nbsp;</p><hr/><p>&nbsp;</p></div>
    <div><p><label for="<?php echo $field_type->_id( '_address_1' ); ?>">Address 1</label></p>
        <?php echo $field_type->input( array(
            'name'  => $field_type->_name( '[address-1]' ),
            'id'    => $field_type->_id( '_address_1' ),
            'value' => $value['address-1'],
            'desc'  => '',
        ) ); ?>
    </div>
    <div><p><label for="<?php echo $field_type->_id( '_address_2' ); ?>'">Address 2</label></p>
        <?php echo $field_type->input( array(
            'name'  => $field_type->_name( '[address-2]' ),
            'id'    => $field_type->_id( '_address_2' ),
            'value' => $value['address-2'],
            'desc'  => '',
        ) ); ?>
    </div>
    <div class="alignleft"><p><label for="<?php echo $field_type->_id( '_city' ); ?>'">City</label></p>
        <?php echo $field_type->input( array(
            'class' => 'cmb_text_small',
            'name'  => $field_type->_name( '[city]' ),
            'id'    => $field_type->_id( '_city' ),
            'value' => $value['city'],
            'desc'  => '',
        ) ); ?>
    </div>
    <div class="alignleft"><p><label for="<?php echo $field_type->_id( '_province' ); ?>'">Province</label></p>
        <?php echo $field_type->select( array(
            'name'    => $field_type->_name( '[province]' ),
            'id'      => $field_type->_id( '_province' ),
            'options' => cmb2_province_options( $value['province'] ),
            'desc'    => '',
        ) ); ?>
    </div>
    <div class="alignleft"><p><label for="<?php echo $field_type->_id( '_postal' ); ?>'">Postal Code</label></p>
        <?php echo $field_type->input( array(
            'class' => 'cmb_text_small',
            'name'  => $field_type->_name( '[postal]' ),
            'id'    => $field_type->_id( '_postal' ),
            'value' => $value['postal'],
            'type'  => 'text_small',
            'desc'  => '',
        ) ); ?>
    </div>
    <br class="clear">
    <?php
    echo $field_type->_desc( true );

}
