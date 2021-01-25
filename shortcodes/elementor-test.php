<?php
namespace Adminz\Admin\AdminzElementor;
use Adminz\Admin\Adminz;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;


class ADMINZ_Test extends Widget_Base {
	public function get_name() {
		return 'adminz-test';
	}
	public function get_title() {
		return __( 'Test', 'elementor' );
	}
	public function get_icon() {
		return 'eicon-flash';
	}
	public function get_keywords() {
		$adminz = new Adminz;
		return [ $adminz->get_adminz_menu_title() ];
	}
	public function get_categories() {
		$adminz = new Adminz;
		return [ $adminz->get_adminz_slug() ];
	}
	public function get_script_depends() {
		return [ 'elementor-hello-world' ];
	}
	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'elementor-hello-world' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Title', 'elementor-hello-world' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'elementor-hello-world' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_transform',
			[
				'label' => __( 'Text Transform', 'elementor-hello-world' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'None', 'elementor-hello-world' ),
					'uppercase' => __( 'UPPERCASE', 'elementor-hello-world' ),
					'lowercase' => __( 'lowercase', 'elementor-hello-world' ),
					'capitalize' => __( 'Capitalize', 'elementor-hello-world' ),
				],
				'selectors' => [
					'{{WRAPPER}} .title' => 'text-transform: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();

		echo '<div class="title">';
		echo $settings['title'];
		echo '</div>';
	}
	protected function _content_template() {
		?>
		<div class="title">
			{{{ settings.title }}}
		</div>
		<?php
	}
}
