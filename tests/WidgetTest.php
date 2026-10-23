<?php
/**
 * The classic widget: escaped output, no extract(), a placeholder option.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class WidgetTest extends W4PL_Snapshot_TestCase {

	private function make_list( $title = 'Sidebar list' ) {
		$list_id = self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_title'  => $title,
				'post_status' => 'publish',
			)
		);
		update_post_meta(
			$list_id,
			'_w4pl',
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 2,
				'template'       => '<ul>[posts]<li>[post_title]</li>[/posts]</ul>',
			)
		);

		return $list_id;
	}

	private function render_widget( array $instance, array $args = array() ) {
		$widget = new W4PL_Widget_Postlist();

		ob_start();
		$widget->widget( $args, $instance );

		return ob_get_clean();
	}

	public function test_widget_is_registered() {
		global $wp_widget_factory;

		$this->assertArrayHasKey( 'W4PL_Widget_Postlist', $wp_widget_factory->widgets );
	}

	public function test_widget_renders_the_list_inside_the_sidebar_markup() {
		$list_id = $this->make_list();
		$html    = $this->render_widget(
			array( 'title' => 'Latest', 'id' => $list_id ),
			array(
				'before_widget' => '<section class="widget">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2>',
				'after_title'   => '</h2>',
			)
		);

		$this->assertStringStartsWith( '<section class="widget"><h2>Latest</h2>', $html );
		$this->assertStringContainsString( 'id="w4pl-list-' . $list_id . '"', $html );
		$this->assertStringContainsString( '<li>Spring cleaning tips</li>', $html );
		$this->assertStringEndsWith( '</section>', $html );
	}

	public function test_widget_without_a_list_prints_nothing() {
		$this->assertSame( '', $this->render_widget( array( 'title' => 'Latest', 'id' => 0 ), array( 'before_widget' => '<section>' ) ) );
		$this->assertSame( '', $this->render_widget( array( 'title' => 'Latest' ), array( 'before_widget' => '<section>' ) ) );
	}

	public function test_widget_without_a_title_prints_no_title_markup() {
		$list_id = $this->make_list();
		$html    = $this->render_widget( array( 'id' => $list_id ), array( 'before_title' => '<h2>', 'after_title' => '</h2>' ) );

		$this->assertStringNotContainsString( '<h2>', $html );
	}

	public function test_widget_uses_the_args_array_not_extracted_variables() {
		$list_id = $this->make_list();
		// A stray key must not become a variable the widget reads: with
		// extract(), $title here would have shadowed the instance title.
		$html = $this->render_widget(
			array( 'title' => 'Instance title', 'id' => $list_id ),
			array( 'title' => 'Shadow title', 'before_title' => '<h2>', 'after_title' => '</h2>' )
		);

		$this->assertStringContainsString( '<h2>Instance title</h2>', $html );
		$this->assertStringNotContainsString( 'Shadow title', $html );
	}

	public function test_update_strips_tags_from_the_title_and_casts_the_id() {
		$widget   = new W4PL_Widget_Postlist();
		$instance = $widget->update( array( 'title' => '<b>Hi</b><script>x()</script>', 'id' => '12abc' ), array() );

		$this->assertSame( 'Hi', $instance['title'] );
		$this->assertSame( 12, $instance['id'] );
	}

	public function test_a_filtered_title_is_passed_through_kses() {
		$list_id = $this->make_list();
		$evil    = function () {
			return '<em>Fine</em><script>alert(1)</script>';
		};
		add_filter( 'widget_title', $evil );
		$html = $this->render_widget( array( 'title' => 'x', 'id' => $list_id ), array( 'before_title' => '<h2>', 'after_title' => '</h2>' ) );
		remove_filter( 'widget_title', $evil );

		$this->assertStringContainsString( '<h2><em>Fine</em>alert(1)</h2>', $html );
		$this->assertStringNotContainsString( '<script>', $html );
	}

	public function test_form_offers_a_placeholder_selected_by_default_and_escapes_list_titles() {
		$list_id = $this->make_list( 'Tom & "Jerry" <b>' );
		$widget  = new W4PL_Widget_Postlist();
		$widget->_set( 3 );

		ob_start();
		$widget->form( array() );
		$form = ob_get_clean();

		$this->assertMatchesRegularExpression( '/<option value="0" selected=\'selected\'>\s*— Select a list —\s*<\/option>/', $form );
		$this->assertStringContainsString( 'Tom &amp; &quot;Jerry&quot; &lt;b&gt;', $form );
		$this->assertStringNotContainsString( '<b>', $form );
		$this->assertStringContainsString( 'value="' . $list_id . '"', $form );
		$this->assertStringContainsString( 'post-new.php?post_type=w4pl', $form );
	}

	public function test_form_marks_the_saved_list_selected_and_escapes_the_title_attribute() {
		$list_id = $this->make_list();
		$widget  = new W4PL_Widget_Postlist();
		$widget->_set( 3 );

		ob_start();
		$widget->form( array( 'title' => 'a"b', 'id' => $list_id ) );
		$form = ob_get_clean();

		$this->assertStringContainsString( 'value="' . $list_id . '" selected=\'selected\'', $form );
		$this->assertStringContainsString( 'value="a&quot;b"', $form );
		$this->assertDoesNotMatchRegularExpression( '/<option value="0" selected/', $form );
	}
}
