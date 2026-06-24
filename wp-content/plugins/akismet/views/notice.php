<<<<<<< HEAD
<?php
//phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.
$kses_allow_link   = array(
	'a' => array(
		'href'   => true,
		'target' => true,
		'class'  => true,
	),
);
$kses_allow_strong = array( 'strong' => true );

if ( ! isset( $type ) ) {
	$type = false; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
}

/*
 * Some notices (plugin, spam-check, spam-check-cron-disabled, alert and usage-limit) are also shown elsewhere in wp-admin, so have different classes applied so that they match the standard WordPress notice format.
 */
?>
<?php if ( $type === 'plugin' ) : ?>
	<?php // Displayed on edit-comments.php to users who have not set up Akismet yet. ?>
	<div class="updated" id="akismet-setup-prompt">
		<form name="akismet_activate" action="<?php echo esc_url( Akismet_Admin::get_page_url() ); ?>" method="post">
			<div class="akismet-activate">
				<input type="submit" class="akismet-activate__button akismet-button" value="<?php esc_attr_e( 'Set up your Akismet account', 'akismet' ); ?>" />
				<div class="akismet-activate__description">
					<?php esc_html_e( 'Almost done! Configure Akismet and say goodbye to spam', 'akismet' ); ?>
				</div>
			</div>
		</form>
	</div>
<?php elseif ( $type === 'spam-check' ) : ?>
	<?php // This notice is only displayed on edit-comments.php. ?>
	<div class="notice notice-warning">
		<p><strong><?php esc_html_e( 'Akismet has detected a problem.', 'akismet' ); ?></strong></p>
		<p><?php esc_html_e( 'Some comments have not yet been checked for spam by Akismet. They have been temporarily held for moderation and will automatically be rechecked later.', 'akismet' ); ?></p>
		<?php if ( ! empty( $link_text ) ) : ?>
			<p><?php echo wp_kses( $link_text, $kses_allow_link ); ?></p>
		<?php endif; ?>
	</div>

<?php elseif ( $type === 'spam-check-cron-disabled' ) : ?>
	<?php // This notice is only displayed on edit-comments.php. ?>
	<div class="notice notice-warning">
		<p><strong><?php esc_html_e( 'Akismet has detected a problem.', 'akismet' ); ?></strong></p>
		<p><?php esc_html_e( 'WP-Cron has been disabled using the DISABLE_WP_CRON constant. Comment rechecks may not work properly.', 'akismet' ); ?></p>
	</div>

<?php elseif ( $type === 'alert' && $code === Akismet::ALERT_CODE_COMMERCIAL && isset( $parent_view ) && $parent_view === 'config' ) : ?>
	<?php // Display a different commercial warning alert on the config page ?>
	<div class="akismet-card akismet-alert is-commercial">
		<div>
			<h3 class="akismet-alert-header"><?php esc_html_e( 'We detected commercial activity on your site', 'akismet' ); ?></h3>
			<p class="akismet-alert-info">
				<?php
					/* translators: The placeholder is a URL. */
					echo wp_kses( sprintf( __( 'Your current subscription is for <a class="akismet-external-link" href="%s">personal, non-commercial use</a>. Please upgrade your plan to continue using Akismet.', 'akismet' ), esc_url( 'https://akismet.com/support/getting-started/free-or-paid/?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=commercial_support' ) ), $kses_allow_link );
				?>
			</p>
			<p class="akismet-alert-info">
				<?php
					/* translators: The placeholder is a URL to the contact form. */
					echo wp_kses( sprintf( __( 'If you believe your site should not be classified as commercial, <a class="akismet-external-link" href="%s">please get in touch</a>', 'akismet' ), esc_url( 'https://akismet.com/contact/?purpose=commercial&utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=commercial_contact' ) ), $kses_allow_link );
				?>
			</p>
		</div>
		<div class="akismet-alert-button-wrapper">
			<?php
			Akismet::view(
				'get',
				array(
					'text'         => __( 'Upgrade plan', 'akismet' ),
					'classes'      => array( 'akismet-alert-button', 'akismet-button' ),
					'redirect'     => 'upgrade',
					'utm_content'  => 'commercial_upgrade',
				)
			);
			?>
		</div>
	</div>

<?php elseif ( $type === 'alert' ) : ?>
<div class="<?php echo isset( $parent_view ) && $parent_view === 'config' ? 'akismet-alert is-bad' : 'error'; ?>">
	<?php /* translators: The placeholder is an error code returned by Akismet. */ ?>
	<p><strong><?php printf( esc_html__( 'Akismet error code: %s', 'akismet' ), esc_html( $code ) ); ?></strong></p>
	<p><?php echo isset( $msg ) ? esc_html( $msg ) : ''; ?></p>
	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: the placeholder is a clickable URL that leads to more information regarding an error code. */
				__( 'For more information, see the <a class="akismet-external-link" href="%s">error documentation on akismet.com</a>', 'akismet' ),
				esc_url( 'https://akismet.com/developers/detailed-docs/errors/akismet-error-' . absint( $code ) . '?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=error_info' )
			),
			$kses_allow_link
		);
		?>
	</p>
</div>

<?php elseif ( $type === 'notice' ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php echo wp_kses( $notice_header, Akismet_Admin::get_notice_kses_allowed_elements() ); ?></h3>
	<p>
		<?php echo wp_kses( $notice_text, Akismet_Admin::get_notice_kses_allowed_elements() ); ?>
	</p>
</div>

<?php elseif ( $type === 'missing-functions' ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php esc_html_e( 'Network functions are disabled.', 'akismet' ); ?></h3>
	<p>
		<?php
		echo wp_kses( __( 'Your web host or server administrator has disabled PHP&#8217;s <code>gethostbynamel</code> function.', 'akismet' ), array_merge( $kses_allow_link, $kses_allow_strong, array( 'code' => true ) ) );
		?>
	</p>
	<p>
		<?php
		/* translators: The placeholder is a URL. */
		echo wp_kses( sprintf( __( 'Please contact your web host or firewall administrator and give them <a class="akismet-external-link" href="%s" target="_blank">this information about Akismet&#8217;s system requirements</a>', 'akismet' ), esc_url( 'https://akismet.com/akismet-hosting-faq/?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=hosting_faq_php' ) ), array_merge( $kses_allow_link, $kses_allow_strong, array( 'code' => true ) ) );
		?>
	</p>
</div>

<?php elseif ( $type === 'servers-be-down' ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php esc_html_e( 'Your site can&#8217;t connect to the Akismet servers.', 'akismet' ); ?></h3>
	<p>
	<?php
		/* translators: The placeholder is a URL. */
		echo wp_kses( sprintf( __( 'Your firewall may be blocking Akismet from connecting to its API. Please contact your host and refer to <a class="akismet-external-link" href="%s" target="_blank">our guide about firewalls</a>', 'akismet' ), esc_url( 'https://akismet.com/akismet-hosting-faq/?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=hosting_faq_firewall' ) ), $kses_allow_link );
	?>
	</p>
</div>

<?php elseif ( $type === 'cancelled' ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php esc_html_e( 'Your Akismet plan has been cancelled.', 'akismet' ); ?></h3>
	<p>
		<?php
		/* translators: The placeholder is a URL. */
		echo wp_kses( sprintf( __( 'Please visit <a class="akismet-external-link" href="%s" target="_blank">Akismet.com</a> to purchase a new subscription.', 'akismet' ), esc_url( 'https://akismet.com/pricing/?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=pricing_cancelled' ) ), $kses_allow_link );
		?>
	</p>
</div>

<?php elseif ( $type === 'suspended' ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php esc_html_e( 'Your Akismet subscription is suspended.', 'akismet' ); ?></h3>
	<p>
		<?php
		/* translators: The placeholder is a URL. */
		echo wp_kses( sprintf( __( 'Please contact <a class="akismet-external-link" href="%s" target="_blank">Akismet support</a> for assistance.', 'akismet' ), esc_url( 'https://akismet.com/contact/?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=support_suspended' ) ), $kses_allow_link );
		?>
	</p>
</div>

<?php elseif ( $type === 'active-notice' && $time_saved ) : ?>
<div class="akismet-alert is-neutral">
	<h3 class="akismet-alert__heading"><?php echo esc_html( $time_saved ); ?></h3>
	<p>
		<?php
		/* translators: the placeholder is a clickable URL to the Akismet account upgrade page. */
		echo wp_kses( sprintf( __( 'You can help us fight spam and upgrade your account by <a class="akismet-external-link" href="%s" target="_blank">contributing a token amount</a>', 'akismet' ), esc_url( 'https://akismet.com/pricing?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=upgrade_contribution' ) ), $kses_allow_link );
		?>
	</p>
</div>

<?php elseif ( $type === 'missing' ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php esc_html_e( 'There is a problem with your API key.', 'akismet' ); ?></h3>
	<p>
		<?php
		/* translators: The placeholder is a URL to the Akismet contact form. */
		echo wp_kses( sprintf( __( 'Please contact <a class="akismet-external-link" href="%s" target="_blank">Akismet support</a> for assistance.', 'akismet' ), esc_url( 'https://akismet.com/contact/?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=support_missing' ) ), $kses_allow_link );
		?>
	</p>
</div>

<?php elseif ( $type === 'no-sub' ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php esc_html_e( 'You don&#8217;t have an Akismet plan.', 'akismet' ); ?></h3>
	<p>
		<?php
		/* translators: the placeholder is the URL to the Akismet pricing page. */
		echo wp_kses( sprintf( __( 'Please <a class="akismet-external-link" href="%s" target="_blank">choose a free or paid plan</a> so Akismet can protect your site from spam.', 'akismet' ), esc_url( 'https://akismet.com/pricing?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=choose_plan' ) ), $kses_allow_link );
		?>
	</p>
	<p><?php echo esc_html__( 'Once you\'ve chosen a plan, return here to complete your setup.', 'akismet' ); ?></p>
</div>

<?php elseif ( $type === 'new-key-valid' ) : ?>
	<?php
	global $wpdb;

	$check_pending_link = false;

	$at_least_one_comment_in_moderation = ! ! $wpdb->get_var( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = '0' LIMIT 1" );

	if ( $at_least_one_comment_in_moderation ) {
		$check_pending_link = 'edit-comments.php?akismet_recheck=' . wp_create_nonce( 'akismet_recheck' );
	}
	?>
	<div class="akismet-alert is-good">
		<p><?php esc_html_e( 'Akismet is now protecting your site from spam.', 'akismet' ); ?></p>
		<?php if ( $check_pending_link ) : ?>
			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: The placeholder is a URL for checking pending comments. */
						__( 'Would you like to <a href="%s">scan pending comments for spam</a>?', 'akismet' ),
						esc_url( $check_pending_link )
					),
					$kses_allow_link
				);
				?>
			</p>
		<?php endif; ?>
	</div>

<?php elseif ( $type === 'new-key-invalid' ) : ?>
<div class="akismet-alert is-bad">
	<p><?php esc_html_e( 'The key you entered is invalid. Please double-check it.', 'akismet' ); ?></p>
</div>

<?php elseif ( $type === Akismet_Admin::NOTICE_EXISTING_KEY_INVALID ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php echo esc_html( __( 'Your API key is no longer valid.', 'akismet' ) ); ?></h3>
	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: The placeholder is a URL to the Akismet contact form. */
				__( 'Please enter a new key or <a class="akismet-external-link" href="%s" target="_blank">contact Akismet support</a>', 'akismet' ),
				'https://akismet.com/contact/?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=support_invalid_key'
			),
			$kses_allow_link
		);
		?>
	</p>
</div>

<?php elseif ( $type === 'new-key-failed' ) : ?>
<div class="akismet-alert is-bad">
	<h3 class="akismet-alert__heading"><?php esc_html_e( 'The API key you entered could not be verified.', 'akismet' ); ?></h3>
	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: The placeholder is a URL. */
				__( 'The connection to akismet.com could not be established. Please refer to <a class="akismet-external-link" href="%s" target="_blank">our guide about firewalls</a> and check your server configuration.', 'akismet' ),
				'https://akismet.com/akismet-hosting-faq/?utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=hosting_faq'
			),
			$kses_allow_link
		);
		?>
	</p>
</div>

<?php elseif ( $type === 'usage-limit' && isset( Akismet::$limit_notices[ $code ] ) ) : ?>
<div class="error akismet-usage-limit-alert">
	<div class="akismet-usage-limit-logo">
		<img src="<?php echo esc_url( plugins_url( '../_inc/img/logo-a-2x.png', __FILE__ ) ); ?>" alt="Akismet logo" />
	</div>
	<div class="akismet-usage-limit-text">
		<h3>
		<?php
		switch ( Akismet::$limit_notices[ $code ] ) {
			case 'FIRST_MONTH_OVER_LIMIT':
			case 'SECOND_MONTH_OVER_LIMIT':
				esc_html_e( 'Your Akismet account usage is over your plan&#8217;s limit', 'akismet' );
				break;
			case 'THIRD_MONTH_APPROACHING_LIMIT':
				esc_html_e( 'Your Akismet account usage is approaching your plan&#8217;s limit', 'akismet' );
				break;
			case 'THIRD_MONTH_OVER_LIMIT':
			case 'FOUR_PLUS_MONTHS_OVER_LIMIT':
				esc_html_e( 'Your account has been restricted', 'akismet' );
				break;
			default:
		}
		?>
		</h3>
		<p>
		<?php
		switch ( Akismet::$limit_notices[ $code ] ) {
			case 'FIRST_MONTH_OVER_LIMIT':
				echo esc_html(
					sprintf(
						/* translators: The first placeholder is a date, the second is a (formatted) number, the third is another formatted number. */
						__( 'Since %1$s, your account made %2$s API calls, compared to your plan&#8217;s limit of %3$s.', 'akismet' ),
						esc_html( gmdate( 'F' ) . ' 1' ),
						number_format( $api_calls ),
						number_format( $usage_limit )
					)
				);
				echo '&nbsp;';
				echo '<a class="akismet-external-link" href="https://akismet.com/support/general/akismet-api-usage-limits/?utm_source=akismet_plugin&amp;utm_campaign=plugin_static_link&amp;utm_medium=in_plugin&amp;utm_content=usage_limit_docs" target="_blank">';
				echo esc_html( __( 'Learn more about usage limits', 'akismet' ) );
				echo '</a>';

				break;
			case 'SECOND_MONTH_OVER_LIMIT':
				echo esc_html( __( 'Your Akismet usage has been over your plan&#8217;s limit for two consecutive months. Next month, we will restrict your account after you reach the limit. Increase your limit to make sure your site stays protected from spam.', 'akismet' ) );
				echo '&nbsp;';
				echo '<a class="akismet-external-link" href="https://akismet.com/support/general/akismet-api-usage-limits/?utm_source=akismet_plugin&amp;utm_campaign=plugin_static_link&amp;utm_medium=in_plugin&amp;utm_content=usage_limit_docs" target="_blank">';
				echo esc_html( __( 'Learn more about usage limits', 'akismet' ) );
				echo '</a>';

				break;
			case 'THIRD_MONTH_APPROACHING_LIMIT':
				echo esc_html( __( 'Your Akismet usage is nearing your plan&#8217;s limit for the third consecutive month. We will restrict your account after you reach the limit. Increase your limit to make sure your site stays protected from spam.', 'akismet' ) );
				echo '&nbsp;';
				echo '<a class="akismet-external-link" href="https://akismet.com/support/general/akismet-api-usage-limits/?utm_source=akismet_plugin&amp;utm_campaign=plugin_static_link&amp;utm_medium=in_plugin&amp;utm_content=usage_limit_docs" target="_blank">';
				echo esc_html( __( 'Learn more about usage limits', 'akismet' ) );
				echo '</a>';

				break;
			case 'THIRD_MONTH_OVER_LIMIT':
			case 'FOUR_PLUS_MONTHS_OVER_LIMIT':
				echo esc_html( __( 'Your Akismet usage has been over your plan&#8217;s limit for three consecutive months. We have restricted your account for the rest of the month. Increase your limit to make sure your site stays protected from spam.', 'akismet' ) );
				echo '&nbsp;';
				echo '<a class="akismet-external-link" href="https://akismet.com/support/general/akismet-api-usage-limits/?utm_source=akismet_plugin&amp;utm_campaign=plugin_static_link&amp;utm_medium=in_plugin&amp;utm_content=usage_limit_docs" target="_blank">';
				echo esc_html( __( 'Learn more about usage limits', 'akismet' ) );
				echo '</a>';

				break;

			default:
		}
		?>
		</p>
	</div>
	<div class="akismet-usage-limit-cta">
		<a href="<?php echo esc_url( $upgrade_url . ( strpos( $upgrade_url, '?' ) !== false ? '&' : '?' ) . 'utm_source=akismet_plugin&utm_campaign=plugin_static_link&utm_medium=in_plugin&utm_content=usage_limit_upgrade' ); ?>" class="button" target="_blank">
			<?php
			if ( isset( $upgrade_via_support ) && $upgrade_via_support ) {
				// Direct user to contact support.
				esc_html_e( 'Contact Akismet support', 'akismet' );
			} elseif ( ! empty( $upgrade_type ) && 'qty' === $upgrade_type ) {
				// If a qty upgrade is required, use recommended plan name if available.
				if ( ! empty( $recommended_plan_name ) && is_string( $recommended_plan_name ) ) {
					echo esc_html(
						sprintf(
							/* translators: The placeholder is the name of an Akismet subscription plan, like "Akismet Pro" or "Akismet Business" . */
							__( 'Add an %s subscription', 'akismet' ),
							$recommended_plan_name
						)
					);
				} else {
					esc_html_e( 'Increase your limit', 'akismet' );
				}
			} else {
				echo esc_html(
					sprintf(
						/* translators: The placeholder is the name of a subscription level, like "Akismet Business" or "Akismet Enterprise" . */
						__( 'Upgrade to %s', 'akismet' ),
						$upgrade_plan
					)
				);
			}
			?>
		</a>
	</div>
</div>
<?php endif; ?>
=======
<?php if ( $type == 'plugin' ) :?>
<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
	<style type="text/css">
.akismet_activate{min-width:825px;border:1px solid #4F800D;padding:5px;margin:15px 0;background:#83AF24;background-image:-webkit-gradient(linear,0% 0,80% 100%,from(#83AF24),to(#4F800D));background-image:-moz-linear-gradient(80% 100% 120deg,#4F800D,#83AF24);-moz-border-radius:3px;border-radius:3px;-webkit-border-radius:3px;position:relative;overflow:hidden}.akismet_activate .aa_a{position:absolute;top:-5px;right:10px;font-size:140px;color:#769F33;font-family:Georgia, "Times New Roman", Times, serif;z-index:1}.akismet_activate .aa_button{font-weight:bold;border:1px solid #029DD6;border-top:1px solid #06B9FD;font-size:15px;text-align:center;padding:9px 0 8px 0;color:#FFF;background:#029DD6;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#029DD6),to(#0079B1));background-image:-moz-linear-gradient(0% 100% 90deg,#0079B1,#029DD6);-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px}.akismet_activate .aa_button:hover{text-decoration:none !important;border:1px solid #029DD6;border-bottom:1px solid #00A8EF;font-size:15px;text-align:center;padding:9px 0 8px 0;color:#F0F8FB;background:#0079B1;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#0079B1),to(#0092BF));background-image:-moz-linear-gradient(0% 100% 90deg,#0092BF,#0079B1);-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px}.akismet_activate .aa_button_border{border:1px solid #006699;-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;background:#029DD6;background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#029DD6),to(#0079B1));background-image:-moz-linear-gradient(0% 100% 90deg,#0079B1,#029DD6)}.akismet_activate .aa_button_container{cursor:pointer;display:inline-block;background:#DEF1B8;padding:5px;-moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;width:266px}.akismet_activate .aa_description{position:absolute;top:22px;left:285px;margin-left:25px;color:#E5F2B1;font-size:15px;z-index:1000}.akismet_activate .aa_description strong{color:#FFF;font-weight:normal}
	</style>
	<form name="akismet_activate" action="<?php echo esc_url( Akismet_Admin::get_page_url() ); ?>" method="POST">
		<div class="akismet_activate">
			<div class="aa_a">A</div>
			<div class="aa_button_container" onclick="document.akismet_activate.submit();">
				<div class="aa_button_border">
					<div class="aa_button"><?php esc_html_e('Activate your Akismet account', 'akismet');?></div>
				</div>
			</div>
			<div class="aa_description"><?php _e('<strong>Almost done</strong> - activate Akismet and say goodbye to spam', 'akismet');?></div>
		</div>
	</form>
</div>
<?php elseif ( $type == 'spam-check' ) :?>
<div id="akismet-warning" class="updated fade">
	<p><strong><?php esc_html_e( 'Akismet has detected a problem.', 'akismet' );?></strong></p>
	<p><?php printf( __( 'Some comments have not yet been checked for spam by Akismet. They have been temporarily held for moderation and will automatically be rechecked later.', 'akismet' ) ); ?></p>
	<?php if ( $link_text ) { ?>
		<p><?php echo $link_text; ?></p>
	<?php } ?>
</div>
<?php elseif ( $type == 'version' ) :?>
<div id="akismet-warning" class="updated fade"><p><strong><?php printf( esc_html__('Akismet %s requires WordPress 3.0 or higher.', 'akismet'), AKISMET_VERSION);?></strong> <?php printf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version, or <a href="%2$s">downgrade to version 2.4 of the Akismet plugin</a>.', 'akismet'), 'https://codex.wordpress.org/Upgrading_WordPress', 'https://wordpress.org/extend/plugins/akismet/download/');?></p></div>
<?php elseif ( $type == 'alert' ) :?>
<div class='error'>
	<p><strong><?php printf( esc_html__( 'Akismet Error Code: %s', 'akismet' ), $code ); ?></strong></p>
	<p><?php echo esc_html( $msg ); ?></p>
	<p><?php

	/* translators: the placeholder is a clickable URL that leads to more information regarding an error code. */
	printf( esc_html__( 'For more information: %s' , 'akismet'), '<a href="https://akismet.com/errors/' . $code . '">https://akismet.com/errors/' . $code . '</a>' );

	?>
	</p>
</div>
<?php elseif ( $type == 'missing-functions' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status failed"><?php esc_html_e('Network functions are disabled.', 'akismet'); ?></h3>
	<p class="description"><?php printf( __('Your web host or server administrator has disabled PHP&#8217;s <code>gethostbynamel</code> function.  <strong>Akismet cannot work correctly until this is fixed.</strong>  Please contact your web host or firewall administrator and give them <a href="%s" target="_blank">this information about Akismet&#8217;s system requirements</a>.', 'akismet'), 'http://blog.akismet.com/akismet-hosting-faq/'); ?></p>
</div>
<?php elseif ( $type == 'servers-be-down' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status failed"><?php esc_html_e("Akismet can&#8217;t connect to your site.", 'akismet'); ?></h3>
	<p class="description"><?php printf( __('Your firewall may be blocking Akismet. Please contact your host and refer to <a href="%s" target="_blank">our guide about firewalls</a>.', 'akismet'), 'http://blog.akismet.com/akismet-hosting-faq/'); ?></p>
</div>
<?php elseif ( $type == 'active-dunning' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status"><?php esc_html_e("Please update your payment information.", 'akismet'); ?></h3>
	<p class="description"><?php printf( __('We cannot process your payment. Please <a href="%s" target="_blank">update your payment details</a>.', 'akismet'), 'https://akismet.com/account/'); ?></p>
</div>
<?php elseif ( $type == 'cancelled' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status"><?php esc_html_e("Your Akismet plan has been cancelled.", 'akismet'); ?></h3>
	<p class="description"><?php printf( __('Please visit your <a href="%s" target="_blank">Akismet account page</a> to reactivate your subscription.', 'akismet'), 'https://akismet.com/account/'); ?></p>
</div>
<?php elseif ( $type == 'suspended' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status failed"><?php esc_html_e("Your Akismet subscription is suspended.", 'akismet'); ?></h3>
	<p class="description"><?php printf( __('Please contact <a href="%s" target="_blank">Akismet support</a> for assistance.', 'akismet'), 'https://akismet.com/contact/'); ?></p>
</div>
<?php elseif ( $type == 'active-notice' && $time_saved ) :?>
<div class="wrap alert active">
	<h3 class="key-status"><?php echo esc_html( $time_saved ); ?></h3>
	<p class="description"><?php printf( __('You can help us fight spam and upgrade your account by <a href="%s" target="_blank">contributing a token amount</a>.', 'akismet'), 'https://akismet.com/account/upgrade/'); ?></p>
</div>
<?php elseif ( $type == 'missing' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status failed"><?php esc_html_e( 'There is a problem with your API key.', 'akismet'); ?></h3>
	<p class="description"><?php printf( __('Please contact <a href="%s" target="_blank">Akismet support</a> for assistance.', 'akismet'), 'https://akismet.com/contact/'); ?></p>
</div>
<?php elseif ( $type == 'no-sub' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status failed"><?php esc_html_e( 'You don&#8217;t have an Akismet plan.', 'akismet'); ?></h3>
	<p class="description">
		<?php printf( __( 'In 2012, Akismet began using subscription plans for all accounts (even free ones). A plan has not been assigned to your account, and we&#8217;d appreciate it if you&#8217;d <a href="%s" target="_blank">sign into your account</a> and choose one.', 'akismet'), 'https://akismet.com/account/upgrade/' ); ?>
		<br /><br />
		<?php printf( __( 'Please <a href="%s" target="_blank">contact our support team</a> with any questions.', 'akismet' ), 'https://akismet.com/contact/' ); ?>
	</p>
</div>
<?php elseif ( $type == 'new-key-valid' ) :?>
<div class="wrap alert active">
	<h3 class="key-status"><?php esc_html_e('Akismet is now activated. Happy blogging!', 'akismet'); ?></h3>
</div>
<?php elseif ( $type == 'new-key-invalid' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status"><?php esc_html_e( 'The key you entered is invalid. Please double-check it.' , 'akismet'); ?></h3>
</div>
<?php elseif ( $type == 'existing-key-invalid' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status"><?php esc_html_e( 'Your API key is no longer valid. Please enter a new key or contact support@akismet.com.' , 'akismet'); ?></h3>
</div>
<?php elseif ( $type == 'new-key-failed' ) :?>
<div class="wrap alert critical">
	<h3 class="key-status"><?php esc_html_e( 'The API key you entered could not be verified.' , 'akismet'); ?></h3>
	<p class="description"><?php printf( __('The connection to akismet.com could not be established. Please refer to <a href="%s" target="_blank">our guide about firewalls</a> and check your server configuration.', 'akismet'), 'http://blog.akismet.com/akismet-hosting-faq/'); ?></p>
</div>
<?php elseif ( $type == 'limit-reached' && in_array( $level, array( 'yellow', 'red' ) ) ) :?>
<div class="wrap alert critical">
	<?php if ( $level == 'yellow' ): ?>
	<h3 class="key-status failed"><?php esc_html_e( 'You&#8217;re using your Akismet key on more sites than your Pro subscription allows.', 'akismet' ); ?></h3>
	<p class="description">
		<?php printf( __( 'Your Pro subscription allows the use of Akismet on only one site. Please <a href="%s" target="_blank">purchase additional Pro subscriptions</a> or upgrade to an Enterprise subscription that allows the use of Akismet on unlimited sites.', 'akismet' ), 'http://docs.akismet.com/billing/add-more-sites/' ); ?>
		<br /><br />
		<?php printf( __( 'Please <a href="%s" target="_blank">contact our support team</a> with any questions.', 'akismet' ), 'https://akismet.com/contact/'); ?>
	</p>
	<?php elseif ( $level == 'red' ): ?>
	<h3 class="key-status failed"><?php esc_html_e( 'You&#8217;re using Akismet on far too many sites for your Pro subscription.', 'akismet' ); ?></h3>
	<p class="description">
		<?php printf( __( 'To continue your service, <a href="%s" target="_blank">upgrade to an Enterprise subscription</a>, which covers an unlimited number of sites.', 'akismet'), 'https://akismet.com/account/upgrade/' ); ?></p>
		<br /><br />
		<?php printf( __( 'Please <a href="%s" target="_blank">contact our support team</a> with any questions.', 'akismet' ), 'https://akismet.com/contact/'); ?></p>
	</p>
	<?php endif; ?>
</div>
<?php endif;?>
>>>>>>> 5a18ec73027fbaf1a0c22718fce45ac95a328f94
