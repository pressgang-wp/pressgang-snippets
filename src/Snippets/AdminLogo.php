<?php


namespace PressGang\Snippets;

/**
 * Replaces the default WordPress logo on the login page with the theme's
 * custom logo (set via the Customizer "logo" or "logo_svg_url" theme mod).
 *
 * Enable this snippet to brand the wp-login.php page with your site's logo.
 * No configuration required — the logo is read from existing theme mods set by
 * the Logo or LogoSvg snippets.
 */
class AdminLogo implements SnippetInterface {

	/**
	 * Hooks into login_enqueue_scripts to inject the custom logo CSS on the
	 * WordPress login page.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'login_enqueue_scripts', [ $this, 'add_login_logo' ] );
	}

	/**
	 * Outputs an inline <style> block replacing the default WordPress login
	 * logo with the theme's custom logo. Does nothing if no logo is set.
	 *
	 * @return void
	 */
	public function add_login_logo(): void {
		$logo = \get_theme_mod( 'logo' ) ?: \get_theme_mod( 'logo_svg_url' );

		if ( $logo ) {
			echo $this->generate_login_logo_css( $logo );
		}
	}

	/**
	 * Generates the inline CSS that replaces the login page logo background
	 * image with the given URL.
	 *
	 * @param string $logo_url URL of the custom logo image.
	 *
	 * @return string An HTML <style> block with the login logo CSS.
	 */
	protected function generate_login_logo_css( string $logo_url ): string {
		$logo_url = \esc_url( $logo_url );

		return "
            <style>
                .login h1 a {
                    background-image: url({$logo_url}) !important;
                    width: 100% !important;
                    max-width: 300px !important;
                    -webkit-background-size: contain !important;
                    background-size: contain !important;
                }
            </style>
        ";
	}
}
