<?php

/**
 * Minimal WP_Post stub for unit tests.
 *
 * Allows dynamic properties (e.g. 'views' set by TrackPostViews) without
 * triggering PHP 8.2+ deprecation notices.
 */
#[\AllowDynamicProperties]
class WP_Post {

	public int $ID = 0;
}
