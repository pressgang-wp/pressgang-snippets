<?php

$shortname = \get_theme_mod( 'disqus-shortname' );
if ( ! $shortname ) {
	return;
}

$context = [
	'disqus_shortname' => $shortname,
	'page_url'         => \apply_filters( 'pressgang_disqus_page_url', \get_permalink() ),
	'page_identifier'  => \apply_filters( 'pressgang_disqus_page_identifier', \get_queried_object_id() ),
	'page_title'       => \apply_filters( 'pressgang_disqus_page_title', \get_the_title() ),
];

\Timber\Timber::render( 'snippets/integration/disqus.twig', $context );
