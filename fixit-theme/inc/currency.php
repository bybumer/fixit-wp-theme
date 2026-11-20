<?php
/**
 * Valyuta məzənnələri: qiymətlərin USD və RUB ekvivalenti üçün.
 *
 * Mənbə Azərbaycan Mərkəzi Bankının gündəlik rəsmi bülletenidir
 * (cbar.az/currencies/GG.AA.İİİİ.xml). Qiymətlər AZN-də yazılır, sayt
 * ekvivalenti brauzerdə hesablayır. Məzənnə 6 saatdan bir yenilənir; AMB
 * cavab verməsə son bilinən məzənnə işlədilir.
 *
 * /wp-json/fixit/v1/rates — {date, rates:{USD,RUB,...}} (1 vahid = X AZN).
 * Səhifə keşdə günlərlə qala bilər, ona görə məzənnə HTML-ə yazılmır,
 * brauzer onu bu ünvandan alır.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Saytda seçilə bilən valyutalar (AZN əsasdır). */
function fixit_fx_currencies() {
	return array( 'AZN', 'USD', 'RUB' );
}

/**
 * AMB bülletenini oxuyur.
 *
 * @param string $xml XML mətni.
 * @return array{date:string,rates:array<string,float>}|null
 */
function fixit_fx_parse( $xml ) {
	if ( ! is_string( $xml ) || false === strpos( $xml, '<ValCurs' ) ) {
		return null;
	}

	$date = preg_match( '/<ValCurs[^>]*Date="(\d{2}\.\d{2}\.\d{4})"/', $xml, $m ) ? $m[1] : '';
	$want = array( 'USD', 'RUB', 'EUR' );
	$out  = array();

	if ( preg_match_all( '#<Valute\s+Code="([A-Z]{3})">(.*?)</Valute>#s', $xml, $all, PREG_SET_ORDER ) ) {
		foreach ( $all as $v ) {
			if ( ! in_array( $v[1], $want, true ) ) {
				continue;
			}
			$nominal = preg_match( '#<Nominal>\s*([\d.]+)#', $v[2], $n ) ? (float) $n[1] : 0;
			$value   = preg_match( '#<Value>\s*([\d.]+)\s*</Value>#', $v[2], $x ) ? (float) $x[1] : 0;
			if ( $nominal > 0 && $value > 0 ) {
				$out[ $v[1] ] = round( $value / $nominal, 8 );
			}
		}
	}

	return ( $date && isset( $out['USD'], $out['RUB'] ) ) ? array(
		'date'  => $date,
		'rates' => $out,
	) : null;
}

/**
 * Qüvvədə olan məzənnələr.
 *
 * @param bool $force Keşə baxmadan AMB-dən al.
 * @return array{date:string,rates:array<string,float>,fetched:int}|null
 */
function fixit_fx_rates( $force = false ) {
	$saved = get_option( 'fixit_fx_rates' );
	$saved = is_array( $saved ) && ! empty( $saved['rates'] ) ? $saved : null;

	$fresh = $saved && ( time() - (int) $saved['fetched'] ) < 6 * HOUR_IN_SECONDS;
	if ( $fresh && ! $force ) {
		return $saved;
	}

	// Uğursuz cəhddən sonra bir saat AMB-yə yenidən getmirik.
	if ( ! $force && get_transient( 'fixit_fx_wait' ) ) {
		return $saved;
	}

	// AMB bülleteni Bakı vaxtı ilə tarixlənir; bayram və həftə sonu günləri
	// fayl olmaya bilər, ona görə bir neçə gün geriyə baxılır.
	$tz = new DateTimeZone( 'Asia/Baku' );
	for ( $i = 0; $i < 6; $i++ ) {
		$day      = ( new DateTimeImmutable( 'now', $tz ) )->modify( "-{$i} day" )->format( 'd.m.Y' );
		$response = wp_remote_get(
			'https://www.cbar.az/currencies/' . $day . '.xml',
			array(
				'timeout'    => 8,
				'user-agent' => 'FIXIT site',
			)
		);
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			continue;
		}
		$parsed = fixit_fx_parse( wp_remote_retrieve_body( $response ) );
		if ( $parsed ) {
			$parsed['fetched'] = time();
			update_option( 'fixit_fx_rates', $parsed, false );
			delete_transient( 'fixit_fx_wait' );
			return $parsed;
		}
	}

	set_transient( 'fixit_fx_wait', 1, HOUR_IN_SECONDS );
	return $saved;
}

/**
 * /wp-json/fixit/v1/rates
 */
function fixit_fx_register_rest() {
	register_rest_route(
		'fixit/v1',
		'/rates',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => function () {
				$fx = fixit_fx_rates();
				if ( ! $fx ) {
					return new WP_REST_Response( array( 'error' => 'unavailable' ), 503 );
				}
				$rates = array();
				foreach ( fixit_fx_currencies() as $code ) {
					if ( 'AZN' !== $code && isset( $fx['rates'][ $code ] ) ) {
						$rates[ $code ] = $fx['rates'][ $code ];
					}
				}
				$response = rest_ensure_response(
					array(
						'date'  => $fx['date'],
						'rates' => $rates,
					)
				);
				$response->header( 'Cache-Control', 'public, max-age=1800' );
				return $response;
			},
		)
	);
}
add_action( 'rest_api_init', 'fixit_fx_register_rest' );
