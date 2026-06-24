<<<<<<< HEAD
<div id="akismet-plugin-container">
	<?php if ( has_action( 'akismet_header' ) ) : ?>
		<?php do_action( 'akismet_header' ); ?>
	<?php else : ?>
		<div class="akismet-masthead">
			<div class="akismet-masthead__inside-container">
				<?php Akismet::view( 'logo', array( 'include_logo_link' => true ) ); ?>
				<div class="akismet-masthead__back-link-container">
					<a class="akismet-masthead__back-link" href="<?php echo esc_url( Akismet_Admin::get_page_url() ); ?>"><?php esc_html_e( 'Back to settings', 'akismet' ); ?></a>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php /* name attribute on iframe is used as a cache-buster here to force Firefox to load the new style charts: https://bugzilla.mozilla.org/show_bug.cgi?id=356558 */ ?>
	<iframe id="stats-iframe" src="<?php echo esc_url( sprintf( 'https://tools.akismet.com/1.0/user-stats.php?blog=%s&token=%s&locale=%s&is_redecorated=1', urlencode( get_option( 'home' ) ), urlencode( Akismet::get_access_token() ), esc_attr( get_user_locale() ) ) ); ?>" name="<?php echo esc_attr( 'user-stats- ' . filemtime( __FILE__ ) ); ?>" width="100%" height="2500px" frameborder="0" title="<?php echo esc_attr__( 'Akismet detailed stats', 'akismet' ); ?>"></iframe>
	<?php Akismet::view( 'footer' ); ?>
</div>
=======
<div class="wrap">
	<h2><?php esc_html_e( 'Akismet Stats' , 'akismet');?><?php if ( !isset( $hide_settings_link ) ): ?> <a href="<?php echo esc_url( Akismet_Admin::get_page_url() );?>" class="add-new-h2"><?php esc_html_e( 'Settings' , 'akismet');?></a><?php endif;?></h2> 
	<iframe src="<?php echo esc_url( sprintf( '//akismet.com/web/1.0/user-stats.php?blog=%s&api_key=%s&locale=%s', urlencode( get_bloginfo('url') ), Akismet::get_api_key(), get_locale() ) ); ?>" width="100%" height="2500px" frameborder="0" id="akismet-stats-frame"></iframe>
</div>
>>>>>>> 5a18ec73027fbaf1a0c22718fce45ac95a328f94
