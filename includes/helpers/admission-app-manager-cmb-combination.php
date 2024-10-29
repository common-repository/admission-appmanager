<?php

/**
 * Render 'combination' custom field type
 *
 * @since 0.1.0
 *
 * @param array  $field              The passed in `CMB2_Field` object
 * @param mixed  $value              The value of this field escaped.
 *                                   It defaults to `sanitize_text_field`.
 *                                   If you need the unescaped value, you can access it
 *                                   via `$field->value()`
 * @param int    $object_id          The ID of the current object
 * @param string $object_type        The type of object you are working with.
 *                                   Most commonly, `post` (this applies to all post-types),
 *                                   but could also be `comment`, `user` or `options-page`.
 * @param object $field_type_object  The `CMB2_Types` object
 */
function aam_cmb2_render_combination_field_callback( $field, $value, $object_id, $object_type, $field_type_object ) {

	// make sure we specify each part of the value we need.
	$value = wp_parse_args( $value, array(
		'school'    => '',
		'program'   => '',
		'intake'    => '',
		'combination' => '',
		'round'     => ''
	) );

	//get schools
	$args = array(
		'post_type' => 'school'
	);

	$query = new WP_Query($args);

	$schools = '<option value="">None</option>';

	foreach($query->get_posts() as $school) {
		$schools .= '<option value="' . $school->ID . '" ' . selected( $value['school'], $school->ID, false ) . '>'  . $school->post_title . '</option>';
	}

	//get post if editing
	/*$school_id = 0;

	if($value['school'] != '') {
		$school_id = get_post_meta($value['school'], '_aam_combination_school', true);
	}*/

	$pgs = '<option value="">None</option>';

	//get programs
	$args = array(
		'post_type' => 'school',
	);

	if($value['school'] != '') {
		$args['p'] = $value['school'];

		$query = new WP_Query( $args );

		foreach ( $query->get_posts() as $school ) {
			$programs = get_post_meta( $school->ID, '_aam_school_programs_group', true );

			foreach ( $programs as $program ) {
				$pgs .= '<option value="' . $program['name'] . '" ' . selected( $value['program'], $program['name'], false ) . '>' . $program['name'] . '</option>';
			}
		}
	}

	//get intake
	$intks = '<option value="">None</option>';

	if ($value['school'] != '' && $value['program'] != '') {
		$intakes = get_terms( array(
			'taxonomy' => 'intake',
			'hide_empty' => false
		) );

		foreach($intakes as $intake) {
			$intks .= '<option value="' . $intake->term_id . '" ' . selected( $value['intake'], $intake->term_id, false ) . '>' . $intake->name . '</option>';
		}
	}

	/*//get combinations
	$cbs = '<option value="">None</option>';

	if($value['school'] != '' && $value['program'] != '' && $value['intake'] != '') {
		$args = array(
			'post_type' => 'combination',
			'tax_query' => array(
				array(
					'taxonomy' => 'intake',
					'field'    => 'term_id',
					'terms'    => $value['intake']
				),
			),
			'meta_query' => array(
				array(
					'key'     => '_aam_combination_school',
					'value'   => $value['school'],
					'compare' => '=',
				),
				array(
					'key'     => '_aam_combination_program',
					'value'   => $value['program'],
					'compare' => '=',
				),
			)
		);

		$query = new WP_Query($args);

		if($query->have_posts()) {
			foreach ( $query->get_posts() as $comb ) {
				$r = get_post_meta( $comb->ID, '_aam_combination_rounds', true );

				$cbs .= '<option data-rounds="' . $r . '" value="' . $comb->ID . '" ' . selected($value['combination'], $comb->ID, false) . '>' . $comb->post_title . ' - ' . $r . ' rounds</option>';
			}
		}
	}*/

	//get rounds
	$rds = '<option value="">None</option>';

	if($value['combination'] != '') {
		$rounds = get_post_meta( $value['combination'], '_aam_combination_rounds', true );

		for($i = 1; $i <= $rounds; $i++) {
			$rds .= '<option value="' . $i . '" ' . selected($value['round'], $i, false) . '>' . $i . '</option>';
		}
	}

	?>
	<p>Please select School, Program and Intake to search for combinations</p>
	<div class=""><p><label for="<?php echo $field_type_object->_id( '_school' ); ?>"><?php echo esc_html( $field_type_object->_text( 'combination_school_text', 'School' ) ); ?></label></p>
		<?php echo $field_type_object->select( array(
			'name'  => $field_type_object->_name( '[school]' ),
			'id'    => $field_type_object->_id( '_school' ),
			'options' => $schools
		) ); ?>
	</div>
	<div class=""><p><label for="<?php echo $field_type_object->_id( '_program' ); ?>'"><?php echo esc_html( $field_type_object->_text( 'combination_program_text', 'Program' ) ); ?></label></p>
		<?php echo $field_type_object->select( array(
			'name'  => $field_type_object->_name( '[program]' ),
			'id'    => $field_type_object->_id( '_program' ),
			'options' => $pgs
		) ); ?>
	</div>
	<div class=""><p><label for="<?php echo $field_type_object->_id( '_intake' ); ?>'"><?php echo esc_html( $field_type_object->_text( 'combination_intake_text', 'Intake' ) ); ?></label></p>
		<?php echo $field_type_object->select( array(
			'name'    => $field_type_object->_name( '[intake]' ),
			'id'      => $field_type_object->_id( '_intake' ),
			'options' => $intks,
		) ); ?>
	</div>
	<div class=""><p><label for="<?php echo $field_type_object->_id( '_round' ); ?>'"><?php echo esc_html( $field_type_object->_text( 'combination_round_text', 'Round' ) ); ?></label></p>
		<?php echo $field_type_object->select( array(
			'name'    => $field_type_object->_name( '[round]' ),
			'id'      => $field_type_object->_id( '_round' ),
			'options' => $rds,
		) ); ?>
	</div>
    <input type="hidden" name="_aam_application_combination[combination]" id="_aam_application_combination_combination" value="<?php echo $value['combination']; ?>" />
	<?php
	echo $field_type_object->_desc( true );

}
add_filter( 'cmb2_render_combination', 'aam_cmb2_render_combination_field_callback', 10, 5 );

function cmb2_sanitize_combination_field( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {

	// if not repeatable, bail out.
	if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
		return $check;
	}

	foreach ( $meta_value as $key => $val ) {
		$meta_value[ $key ] = array_map( 'sanitize_text_field', $val );
	}

	return $meta_value;
}
add_filter( 'cmb2_sanitize_combination', 'cmb2_sanitize_combination_field', 10, 5 );

function cmb2_types_esc_combination_field( $check, $meta_value, $field_args, $field_object ) {
	// if not repeatable, bail out.
	if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
		return $check;
	}

	foreach ( $meta_value as $key => $val ) {
		$meta_value[ $key ] = array_map( 'esc_attr', $val );
	}

	return $meta_value;
}
add_filter( 'cmb2_types_esc_combination', 'cmb2_types_esc_combination_field', 10, 4 );