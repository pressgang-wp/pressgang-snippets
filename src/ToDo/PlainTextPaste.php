<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\add_filter;

class PlainTextPaste {

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		add_filter( 'tiny_mce_before_init', [ $this, 'plain_text_paste' ] );
		add_filter( 'teeny_mce_before_init', [ $this, 'plain_text_paste' ] );
	}

	/**
	 * plain_text_paste
	 *
	 * @param $mceInit
	 *
	 * @return mixed
	 */
	public function plain_text_paste( $mceInit ) {
		$mceInit['paste_text_sticky']         = true;
		$mceInit['paste_text_sticky_default'] = true;

		return $mceInit;
	}

}

new PlainTextPaste();
