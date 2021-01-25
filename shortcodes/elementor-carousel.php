<?php
namespace Adminz\Admin\AdminzElementor;
use Adminz\Admin\Adminz;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Control_Media;
use Elementor\Group_Control_Image_Size;

class ADMINZ_Carousel extends Widget_Base {
	public function __construct( $data = [], $args = null ) {			
		parent::__construct( $data, $args );		
		wp_enqueue_script( 'carousel-js', plugin_dir_url(ADMINZ_BASENAME).'assets/js/elementor-carousel.js', [ 'elementor-frontend' ], '1.0.0', true );		
	}
	public function get_script_depends() {
     	return [ 'carousel-js' ];
  	}
	public function get_name() {
		return 'adminz-carousel';		
	}
	public function get_title() {
		return __( 'Carousel', 'elementor' );
	}
	public function get_icon() {
		return 'eicon-slides';
	}
	public function get_keywords() {
		$adminz = new Adminz;
		return [ $adminz->get_adminz_menu_title(), 'image', 'photo', 'box' ];
	}
	public function get_categories() {
		$adminz = new Adminz;
		return [ $adminz->get_adminz_slug() ];
	}
	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'elementor' ),
				'tab' => Controls_Manager::TAB_CONTENT,				
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'list_image',
			[
				'label' => __( 'Choose Image', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
				],
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'list_title_text',
			[
				'label' => __( 'Title & Description', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'This is the heading', 'elementor' ),
				'placeholder' => __( 'Enter your title', 'elementor' ),
				'label_block' => true,
			]
		);
		$repeater->add_control(
			'list_title_size',
			[
				'label' => __( 'Title HTML Tag', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'span' => 'span',
					'p' => 'p',
				],
				'default' => 'h3',
			]
		);
		$repeater->add_control(
			'list_description_text',
			[
				'label' => __( 'Content', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'elementor' ),
				'placeholder' => __( 'Enter your description', 'elementor' ),
				'separator' => 'none',
				'rows' => 10,
				'show_label' => false,
			]
		);
		$repeater->add_control(
			'list_link',
			[
				'label' => __( 'Link', 'elementor' ),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'elementor' ),
				'separator' => 'before',
			]
		);
		$repeater->add_control(
			'list_link_text',
			[
				'label' => __( 'Link text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'This is the heading', 'elementor' ),
				'placeholder' => __( 'Enter your title', 'elementor' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'list',
			[
				'label' => __( 'List Item', 'elementor' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'list_title_text' => __( 'Title #1', 'elementor' ),
						'list_description_text' => __( 'Item content. Click the edit button to change this text.', 'elementor' ),
						'list_title_size' => 'h3',
						'list_link' => "https://your-link.com",
						'link_link_text'=>__('Click me', 'elementor')
					],
					[
						'list_title_text' => __( 'Title #2', 'elementor' ),
						'list_description_text' => __( 'Item content. Click the edit button to change this text.', 'elementor' ),
						'list_title_size' => 'h3',
						'list_link' => "https://your-link.com",
						'link_link_text'=>__('Click me', 'elementor')
					],
					[
						'list_title_text' => __( 'Title #3', 'elementor' ),
						'list_description_text' => __( 'Item content. Click the edit button to change this text.', 'elementor' ),
						'list_title_size' => 'h3',
						'list_link' => "https://your-link.com",
						'link_link_text'=>__('Click me', 'elementor')
					]
				],
				'title_field' => '{{{ list_title_text }}}',
			]
		);

		$this->end_controls_section();
		$this->start_controls_section(
			'section_additional_options',
			[
				'label' => __( 'Additional Options', 'elementor' ),
			]
		);
		
		$slides_to_show = range( 1, 10 );
		$slides_to_show = array_combine( $slides_to_show, $slides_to_show );

		$this->add_responsive_control(
			'slides_to_show',
			[
				'label' => __( 'Slides to Show', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Default', 'elementor' ),
				] + $slides_to_show,
				/*'frontend_available' => true,*/
			]
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			[
				'label' => __( 'Slides to Scroll', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'description' => __( 'Set how many slides are scrolled per swipe.', 'elementor' ),
				'options' => [
					'' => __( 'Default', 'elementor' ),
				] + $slides_to_show,
				'condition' => [
					'slides_to_show!' => '1',
				],
				/*'frontend_available' => true,*/
			]
		);
		$this->add_control(
			'navigation',
			[
				'label' => __( 'Navigation', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'both',
				'options' => [
					'both' => __( 'Arrows and Dots', 'elementor' ),
					'arrows' => __( 'Arrows', 'elementor' ),
					'dots' => __( 'Dots', 'elementor' ),
					'none' => __( 'None', 'elementor' ),
				],
				/*'frontend_available' => true,*/
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'elementor' ),
					'no' => __( 'No', 'elementor' ),
				],
				'render_type' => 'none',
				/*'frontend_available' => true,*/
			]
		);
		$this->add_control(
			'pause_on_hover',
			[
				'label' => __( 'Pause on Hover', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'elementor' ),
					'no' => __( 'No', 'elementor' ),
				],
				'condition' => [
					'autoplay' => 'yes',
				],
				'render_type' => 'none',
				/*'frontend_available' => true,*/
			]
		);
		$this->add_control(
			'pause_on_interaction',
			[
				'label' => __( 'Pause on Interaction', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'elementor' ),
					'no' => __( 'No', 'elementor' ),
				],
				'condition' => [
					'autoplay' => 'yes',
				],
				'render_type' => 'none',
				/*'frontend_available' => true,*/
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => __( 'Autoplay Speed', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 4999,
				'condition' => [
					'autoplay' => 'yes',
				],
				'render_type' => 'none',
				/*'frontend_available' => true,*/
			]
		);

		// Loop requires a re-render so no 'render_type = none'
		$this->add_control(
			'infinite',
			[
				'label' => __( 'Infinite Loop', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'elementor' ),
					'no' => __( 'No', 'elementor' ),
				],
				/*'frontend_available' => true,*/
			]
		);

		$this->add_control(
			'speed',
			[
				'label' => __( 'Animation Speed', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 499,
				'render_type' => 'none',
				/*'frontend_available' => true,*/
			]
		);
		$this->add_control(
			'arrows_position',
			[
				'label' => __( 'Arrow Position', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'inside',
				'options' => [
					'inside' => __( 'Inside', 'elementor' ),
					'outside' => __( 'Outside', 'elementor' ),
				],
				'prefix_class' => 'elementor-arrows-position-',
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);
		$this->add_control(
			'dots_position',
			[
				'label' => __( 'Dot Position', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'outside',
				'options' => [
					'outside' => __( 'Outside', 'elementor' ),
					'inside' => __( 'Inside', 'elementor' ),
				],
				'prefix_class' => 'elementor-pagination-position-',
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);
		$this->end_controls_section();


	}
	function print_item(){
		$settings = $this->get_settings_for_display();
		$settings = $settings['list'];

		$html = '';
		if (!Utils::is_empty($settings)){
			foreach ($settings as $key => $list) {
				$has_content = ! Utils::is_empty( $list['list_title_text'] ) || ! Utils::is_empty( $list['list_description_text'] );


				if ( empty( $list['list_link']['url'] ) ) {
					$list['list_link']['url'] = "#";
				}
				$link_key = 'link_' . $key;
				$this->add_link_attributes( $link_key, $list['list_link'] );

				$html .='<div class="swiper-slide adminz-elementor-image-box-content">';
				$html .='<div class="adminz-wrapper-item">';
				if ( ! empty( $list['list_image']['url'] ) ) {
					$this->add_render_attribute( 'list_image', 'src', $list['list_image']['url'] );
					$this->add_render_attribute( 'list_image', 'alt', Control_Media::get_image_alt( $list['list_image'] ) );
					$this->add_render_attribute( 'list_image', 'title', Control_Media::get_image_title( $list['list_image'] ) );

					if ( isset($list['list_hover_animation'] )) {
						$this->add_render_attribute( 'list_image', 'class', 'elementor-animation-' . $list['list_hover_animation'] );
					}

					$image_html = Group_Control_Image_Size::get_attachment_image_html( $list, 'thumbnail', 'list_image' );

					if ( ! empty( $list['list_link']['url'] ) ) {
						$image_html = '<a ' . $this->get_render_attribute_string( $link_key ) . '>' . $image_html . '</a>';
					}

					$html .= '<figure class="elementor-image-box-img">' . $image_html . '</figure>';
				}
				if ( $has_content ) {
					$html .= '<div class="elementor-image-box-content">';
					if ( ! Utils::is_empty( $list['list_title_text'] ) ) {

						$title_html = $list['list_title_text'];

						if ( ! empty( $list['list_link']['url'] ) ) {
							$title_html = '<a ' . $this->get_render_attribute_string( $link_key ) . '>' . $title_html . '</a>';
						}

						$html .= sprintf( '<%1$s class="adminz-carousel-title">%2$s</%1$s>', $list['list_title_size'],  $title_html );
					}
					if ( ! Utils::is_empty( $list['list_description_text'] ) ) {
						$description_key = 'list_description_text'.$key;
						$this->add_render_attribute( $description_key, 'class', 'elementor-image-box-description' );
						$this->add_inline_editing_attributes( 'list_description_text' );

						$html .= sprintf( '<p %1$s>%2$s</p>', $this->get_render_attribute_string( $description_key ), $list['list_description_text'] );
					}
					if ( ! Utils::is_empty( $list['list_link_text'] ) ) {
						$this->add_inline_editing_attributes( 'list_link' );
						$this->add_render_attribute($link_key,'class','readmore');
						$html .= '<a '. $this->get_render_attribute_string( $link_key ) .'>'.$list['list_link_text']."</a>";
					}
					$html .= '</div>';
				}
				$html .='</div>';
				$html .='</div>';
			}
		}

		echo $html ; 
	}
	protected function render() {
		$settings = $this->get_settings_for_display();
		$data_slider = 
			array(
			'slides_to_show'=> !empty($settings['slides_to_show'])? $settings['slides_to_show']: 3 ,
			'slides_to_show_tablet' => !empty($settings['slides_to_show_tablet'])? $settings['slides_to_show_tablet']: 2 ,
			'slides_to_show_mobile' => !empty($settings['slides_to_show_mobile'])? $settings['slides_to_show_mobile']: 1 ,
			'slides_to_scroll' => !empty($settings['slides_to_scroll'])? $settings['slides_to_scroll']: 3 ,
			'slides_to_scroll_tablet' => !empty($settings['slides_to_scroll_tablet'])? $settings['slides_to_scroll_tablet']: 2 ,
			'slides_to_scroll_mobile' => !empty($settings['slides_to_scroll_mobile'])? $settings['slides_to_scroll_mobile']: 1 ,
			'navigation' => !empty($settings['navigation'])? $settings['navigation']: "both" ,
			'autoplay' => !empty($settings['autoplay'])? $settings['autoplay']: "yes" ,
			'pause_on_hover' => !empty($settings['pause_on_hover'])? $settings['pause_on_hover']: "yes" ,
			'pause_on_interaction' => !empty($settings['pause_on_interaction'])? $settings['pause_on_interaction']: "yes" ,
			'autoplay_speed'=> !empty($settings['autoplay_speed'])? $settings['autoplay_speed']: 4999 ,
			'infinite' => !empty($settings['infinite'])? $settings['infinite']: "yes" ,
			'speed' => !empty($settings['speed'])? $settings['speed']: 499 ,
			);
		
		$data = (object) $data_slider;
		foreach ($data_slider as $key => $value) {
			$data->$key = $value;
		}
		$data = json_encode($data);
		$data = str_replace('"', '&quot;', $data);

		$this->add_render_attribute( [
			'carousel' => [
				'class' => 'elementor-image-carousel swiper-wrapper',
			],
			'carousel-wrapper' => [
				'class' => 'elementor-image-carousel-wrapper swiper-container adminz-carousel',
				'data-settings' => $data,
				'id' => $this->get_id()
			],
		] );
		?>
		<div <?php echo $this->get_render_attribute_string( 'carousel-wrapper' ); ?>>
			<div <?php echo $this->get_render_attribute_string( 'carousel' ); ?>>
				<?php echo $this->print_item(); ?>
			</div>
			<?php
			$show_dots = ( in_array( $settings['navigation'], [ 'dots', 'both' ] ) );
			$show_arrows = ( in_array( $settings['navigation'], [ 'arrows', 'both' ] ) );

			if ( $show_dots ){
				echo '<div class="swiper-pagination"></div>';
			}
			if ( $show_arrows ){
				echo '<div class="elementor-swiper-button elementor-swiper-button-prev">
							<i class="eicon-chevron-left" aria-hidden="true"></i>
							<span class="elementor-screen-only">'.__( 'Previous', 'elementor' ).'</span>
						</div>
						<div class="elementor-swiper-button elementor-swiper-button-next">
							<i class="eicon-chevron-right" aria-hidden="true"></i>
							<span class="elementor-screen-only">'.__( 'Next', 'elementor' ).'</span>
						</div>';
			}
			?>
		</div>
		<?php
	}

	protected function _content_template() {
		
	}
	
	
}