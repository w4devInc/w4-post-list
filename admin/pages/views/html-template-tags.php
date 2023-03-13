<?php
/**
 * Documentation of template tags
 *
 * @package W4_Post_List
 */

?>
<h2>
	<?php esc_html_e( 'What is Template Tags?', 'w4-post-list' ); ?>
</h2>
<p>
	<?php esc_html_e( 'Template tags are like WordPress shortcode. It\'s a piece of code that gets replaced with a dyanamic value.', 'w4-post-list' ); ?>
</p>

<table class="widefat w4pl-template-tags" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th class="col-name">
				<?php esc_html_e( 'Tag', 'w4-post-list' ); ?>
			</th>
			<th class="col-output">
				<?php esc_html_e( 'Output', 'w4-post-list' ); ?>
			</th>
			<th class="col-description">
				<?php esc_html_e( 'Parameters', 'w4-post-list' ); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( w4pl_get_shortcodes() as $shortcode => $attr ) {
			if ( isset( $row_class ) && '' === $row_class ) {
				$row_class = 'alt';
			} else {
				$row_class = '';
			}
			?>
			<tr class="<?php echo esc_attr( $row_class ); ?>">
				<th class="col-name">
					<?php
					echo esc_html( "[{$shortcode}]" );
					?>
				</th>
				<th class="col-output">
					<?php
					if ( isset( $attr['output'] ) ) {
						echo esc_html( $attr['output'] );
					}
					?>
				</th>
				<td class="col-description">
					<?php
					if ( isset( $attr['desc'] ) ) {
						echo wp_kses_post( $attr['desc'] );
					} elseif ( isset( $attr['parameters'] ) ) {
						foreach ( $attr['parameters'] as $param => $param_attrs ) {
							printf(
								'<strong>%s</strong>: %s ',
								esc_html( $param ),
								wp_kses_post( $param_attrs['desc'] )
							);

							if ( isset( $param_attrs['choices'] ) ) {
								printf( '(%s)', esc_html( join( ', ', $param_attrs['choices'] ) ) );
							}

							echo '<br />';
						}
					} else {
						echo '-';
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
