<?php
/**
 * Date Functions
 *
 * @package    CS
 * @subpackage Functions
 * @copyright  Copyright (c) 2018, CommerceStore, LLC
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      3.0
 */

/**
 * Retrieves a localized, formatted date based on the WP timezone rather than UTC.
 *
 * @since 3.0
 *
 * @param int    $timestamp Timestamp. Can either be based on UTC or WP settings.
 * @param string $format    Optional. Accepts shorthand 'date', 'time', or 'datetime'
 *                          date formats, as well as any valid date_format() string.
 *                          Default 'date' represents the value of the 'date_format' option.
 * @return string The formatted date, translated if locale specifies it.
 */
function cs_date_i18n( $timestamp, $format = 'date' ) {
	$format = cs_get_date_format( $format );

	// If timestamp is a string, attempt to turn it into a timestamp.
	if ( ! is_numeric( $timestamp ) ) {
		$timestamp = strtotime( $timestamp );
	}

	// We need to get the timezone offset so we can pass that to date_i18n.
	$date = CS()->utils->date( 'now', cs_get_timezone_id(), false );

	return date_i18n( $format, (int) $timestamp + $date->getOffset() );
}

/**
 * Retrieve timezone ID
 *
 * @since 1.6
 * @return string $timezone The timezone ID
 */
function cs_get_timezone_id() {
	return CS()->utils->get_time_zone( true );
}

/**
 * Accept an CommerceStore date object and get the UTC equivalent version of it.
 * The CommerceStore date object passed-in can be in any timezone. The one you'll get back will be the UTC equivalent of that time.
 * This is useful when querying data from the tables by a user-defined date range, like "today".
 *
 * @since 3.0
 * @param CS\Utils\Date $cs_date_object The CommerceStore Date object for which you wish to get the UTC equiavalent.
 * @return CS\Utils\Date The CommerceStore date object set at the UTC equivalent time.
 */
function cs_get_utc_equivalent_date( $cs_date_object ) {

	$instance_check = 'CS\Utils\Date';
	if ( ! $cs_date_object instanceof $instance_check ) {
		return false;
	}

	// Convert the timezone (and thus, also the time) from the WP/CS Timezone to the UTC equivalent.
	$utc_timezone = new DateTimeZone( 'utc' );
	$cs_date_object->setTimezone( $utc_timezone );

	return $cs_date_object;
}

/**
 * Accept an CommerceStore date object set in UTC, and get the WP/CS Timezone equivalent version of it.
 * The CommerceStore date object must be in UTC. The one you'll get back will be the WP timezone equivalent of that time.
 * This is useful when showing date information to the user, so that they see it in the proper timezone, instead of UTC.
 *
 * @since 3.0
 * @param CS\Utils\Date $cs_date_object The CommerceStore Date object for which you wish to get the UTC equiavalent.
 * @return CS\Utils\Date The CommerceStore date object set at the UTC equivalent time.
 */
function cs_get_cs_timezone_equivalent_date_from_utc( $cs_date_object ) {

	$instance_check = 'CS\Utils\Date';
	if ( ! $cs_date_object instanceof $instance_check ) {
		return false;
	}

	// If you passed a date object to this function that isn't set to UTC, that is incorrect usage.
	if ( 'UTC' !== $cs_date_object->format( 'T' ) ) {
		return false;
	}

	// Convert the timezone (and thus, also the time) from UTC to the WP/CS Timezone.
	$cs_timezone = new DateTimeZone( cs_get_timezone_id() );
	$cs_date_object->setTimezone( $cs_timezone );

	return $cs_date_object;
}

/**
 * Get the timezone abbreviation for the WordPress timezone setting.
 *
 * @since 3.0
 *
 * @return string The abreviation for the current WordPress timezone setting.
 */
function cs_get_timezone_abbr() {
	$cs_timezone    = cs_get_timezone_id();
	$cs_date_object = CS()->utils->date( 'now', $cs_timezone, true );
	return $cs_date_object->format( 'T' );
}

/**
 * Retrieves a date format string based on a given short-hand format.
 *
 * @since 3.0
 *
 * @see \CS_Utilities::get_date_format_string()
 *
 * @param string $format Shorthand date format string. Accepts 'date', 'time', 'mysql', or
 *                       'datetime'. If none of the accepted values, the original value will
 *                       simply be returned. Default 'date' represents the value of the
 *                       'date_format' option.
 *
 * @return string date_format()-compatible date format string.
 */
function cs_get_date_format( $format = 'date' ) {
	return CS()->utils->get_date_format_string( $format );
}

/**
 * Get the format used by jQuery UI Datepickers.
 *
 * Use this if you need to use placeholder or format attributes in input fields.
 *
 * This is a bit different than `cs_get_date_format()` because these formats
 * are exposed to users as hints and also used by jQuery UI so the Datepicker
 * knows what format it returns into it's connected input value.
 *
 * Previous to this function existing, all values were hard-coded, causing some
 * inconsistencies across admin-area screens.
 *
 * @see https://github.com/commercestore/commercestore/commit/e9855762892b6eec578b0a402f7950f22bd19632
 *
 * @since 3.0
 *
 * @param string $context The context we are getting the format for. Accepts 'display' or 'js'.
 *                        Use 'js' for use with jQuery UI Datepicker. Use 'display' for HTML attributes.
 * @return string
 */
function cs_get_date_picker_format( $context = 'display' ) {

	// What is the context that we are getting the picker format for?
	switch ( $context ) {

		// jQuery UI Datepicker does its own thing
		case 'js' :
		case 'javascript' :
			$retval = CS()->utils->get_date_format_string( 'date-js' );
			break;

		// Used to display in an attribute, placeholder, etc...
		case 'display' :
		default :
			$retval = CS()->utils->get_date_format_string( 'date-attribute' );
			break;
	}

	/**
	 * Filter the date picker format, allowing for custom overrides
	 *
	 * @since 3.0
	 *
	 * @param string $retval  Date format for date picker
	 * @param string $context The context this format is for
	 */
	return apply_filters( 'cs_get_date_picker_format', $retval, $context );
}

/**
 * Return an array of values used to populate an hour dropdown
 *
 * @since 3.0
 *
 * @return array
 */
function cs_get_hour_values() {
	return (array) apply_filters( 'cs_get_hour_values', array(
		'00' => '00',
		'01' => '01',
		'02' => '02',
		'03' => '03',
		'04' => '04',
		'05' => '05',
		'06' => '06',
		'07' => '07',
		'08' => '08',
		'09' => '09',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		'13' => '13',
		'14' => '14',
		'15' => '15',
		'16' => '16',
		'17' => '17',
		'18' => '18',
		'19' => '19',
		'20' => '20',
		'21' => '21',
		'22' => '22',
		'23' => '23',
		'24' => '24'
	) );
}

/**
 * Return an array of values used to populate a minute dropdown
 *
 * @since 3.0
 *
 * @return array
 */
function cs_get_minute_values() {
	return (array) apply_filters( 'cs_get_minute_values', array(
		'00' => '00',
		'01' => '01',
		'02' => '02',
		'03' => '03',
		'04' => '04',
		'05' => '05',
		'06' => '06',
		'07' => '07',
		'08' => '08',
		'09' => '09',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		'13' => '13',
		'14' => '14',
		'15' => '15',
		'16' => '16',
		'17' => '17',
		'18' => '18',
		'19' => '19',
		'20' => '20',
		'21' => '21',
		'22' => '22',
		'23' => '23',
		'24' => '24',
		'25' => '25',
		'26' => '26',
		'27' => '27',
		'28' => '28',
		'29' => '29',
		'30' => '30',
		'31' => '31',
		'32' => '32',
		'33' => '33',
		'34' => '34',
		'35' => '35',
		'36' => '36',
		'37' => '37',
		'38' => '38',
		'39' => '39',
		'40' => '40',
		'41' => '41',
		'42' => '42',
		'43' => '43',
		'44' => '44',
		'45' => '45',
		'46' => '46',
		'47' => '47',
		'48' => '48',
		'49' => '49',
		'50' => '50',
		'51' => '51',
		'52' => '52',
		'53' => '53',
		'54' => '54',
		'55' => '55',
		'56' => '56',
		'57' => '57',
		'58' => '58',
		'59' => '59'
	) );
}
