<?php

namespace PressGang\ToDo;

use PressGang\Scripts;
use function PressGang\get_template_directory_uri;

/**
 * Class Charts
 *
 * https://cdnjs.com/libraries/Chart.js
 *
 * @package PressGang
 */
class Chart {
	public static function register() {
		// enqueue chart.js
		Scripts::$scripts['chart'] = [
			'src'       => 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.min.js',
			'ver'       => '2.5.0',
			'in_footer' => true
		];

		// enqueue pressgang's chart.js
		Scripts::$scripts['pressgang-chart'] = [
			'src'       => get_template_directory_uri() . '/js/src/custom/slick.js',
			'deps'      => [ 'chart', 'jquery' ],
			'ver'       => '0.1',
			'in_footer' => true
		];
	}


}
