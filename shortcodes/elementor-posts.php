<?php
namespace Adminz\Admin\AdminzElementor;
use Adminz\Admin\Adminz;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
/**
 * Class Posts
 */
class ADMINZ_Posts extends Widget_Base {
	public function get_name() {
		return 'adminz-posts';		
	}
	public function get_title() {
		return __( 'Posts', 'elementor' );
	}
	public function get_icon() {
		return 'eicon-gallery-grid';
	}
	public function get_keywords() {
		$adminz = new Adminz;
		return [ $adminz->get_adminz_menu_title(), 'post' ];
	}
	public function get_categories() {
		$adminz = new Adminz;
		return [ $adminz->get_adminz_slug() ];
	}
	private function get_post_types(){
		return get_post_types( ['public'=>true], 'objects' );
	}
	public function _register_controls(){

		$this->start_controls_section(
			'query',
			[
				'label' => __( 'Query', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$post_types = $this->get_post_types();
		$arr_post_types = array();
		foreach ($post_types as $key => $post_type) {
			$arr_post_types[$post_type->name] = $post_type->labels->singular_name;
		}

		$this->add_control(
			'post_type',
			[
				'label' => __( 'Post Type', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => $arr_post_types,
				'default' => $this->get_default($arr_post_types,'post'),
			]
		);
		foreach ($post_types as $key => $post_type) {			
			$taxonomy_objects = get_object_taxonomies( $post_type->name, 'objects' );
			$arr_taxonomy = array();
			if(!empty($taxonomy_objects)){
				foreach ($taxonomy_objects as $taxonomy) {
					$arr_taxonomy[$taxonomy->name] = $taxonomy->label; 
				}
			}

			$this->add_control(
				'taxonomy-'.$post_type->name,
				[
					'label' => $post_type->label. " ".__( 'Taxonomy', 'elementor' ),
					'type' => Controls_Manager::SELECT,
					'options' => $arr_taxonomy,
					'default' => $this->get_default($arr_taxonomy,''),
					'condition'=> ['post_type'=>$post_type->name]
				]
			);
			foreach ($arr_taxonomy as $key => $value) {
				$this->add_control(
					'term-'.$post_type->name."-".$key,
					[
						'label' => __( 'Select', 'plugin-domain' )." " .$value,
						'type' => \Elementor\Controls_Manager::SELECT2,
						'multiple' => true,
						'default' => 'DESC',
						'options' => $this->get_terms($key),
						'condition' => [
							'post_type'=>$post_type->name,
							'taxonomy-'.$post_type->name=> $key
						]
					]
				);
			}

		}
		
		foreach ($arr_post_types as $key => $value) {
			$this->add_control(
				'post__not_in-'.$key,
				[
					'label'=> $value." ". __( 'Role Excluded', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'multiple' => true,
					'options' => $this->get_not_in_ids($key),
					'condition' => ['post_type'=>$key],
				]
			);
		};		
 		$this->add_control(
			'posts_per_page',
			[
				'label' => __('Posts Per Page', 'elementor-pro'),
				'type' => Controls_Manager::TEXT,
				'default' => get_option( 'posts_per_page' ),
				'separator' => 'before',
			]
		);
		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'DESC'  => 'DESC',
					'ASC' => 'ASC',
				],
			]
		);
		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order by', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'none' => 'none',
					'ID' => 'ID',
					'author' => 'author',
					'title' => 'title',
					'name' => 'name',
					'type' => 'type',
					'date' => 'date',
					'modified' => 'modified',
					'parent' => 'parent',
					'rand' => 'rand',
					'comment_count' => 'comment_count',
					'relevance' => 'relevance',
					'menu_order' => 'menu_order',
					'meta_value' => 'meta_value',
					'meta_type' => 'meta_type',
					'meta_value_num' => 'meta_value_num',
					'post__in' => 'post__in',
					'post_name__in' => 'post_name__in',
					'post_parent__in' => 'post_parent__in',
				],
			]
		);		
		
		$this->add_control(
			'ignore_sticky_posts',
			[
				'label' => __( 'Ignore sticky posts', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Hide', 'your-plugin' ),
				'label_off' => __( 'Show', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'separator' => 'before',
			]
		);
		$this->add_control(
			'pagination',
			[
				'label' => __( 'Pagination', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'your-plugin' ),
				'label_off' => __( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		$this->add_control(
			'pagination_show',
			[
				'label' => __( 'Show ', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all' => __( 'All', 'plugin-domain' ),
					'prev_next' => __( 'Previous Next', 'plugin-domain' ),
					'number' => __( 'Number', 'plugin-domain' ),					
				],
				'condition' => [
					'pagination' => 'yes'
				]
			]
		);
		$this->add_control(
			'pagination_next_text',
			[
				'label' => __( 'Next text', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Next', 'plugin-domain' ),
				'condition' => [
					'pagination' => 'yes',
					'pagination_show'  => ['all','prev_next'],
				],
			]
		);
		$this->add_control(
			'pagination_prev_text',
			[
				'label' => __( 'Prev text', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Prev', 'plugin-domain' ),
				'condition' => [
					'pagination' => 'yes',
					'pagination_show'  => ['all','prev_next'],
				],
			]
		);
		$this->add_control(
			'pagination_end_size',
			[
				'label' => __( 'End size', 'plugin-domain' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 1,
				'condition' => [
					'pagination' => 'yes',
					'pagination_show'  => ['all','number'],
				]
			]
		);
		$this->add_control(
			'pagination_mid_size',
			[
				'label' => __( 'Mid size', 'plugin-domain' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 1,
				'condition' => [
					'pagination' => 'yes',
					'pagination_show'  => ['all','number'],
				]
			]
		);

		$this->add_control(
			'pagination_show_all',
			[
				'label' => __( 'Show all paginations', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'your-plugin' ),
				'label_off' => __( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'no',
				'condition' => [
					'pagination'  => 'yes',
				],
			]
		);

		
		$this->add_control(
			'pagination_type',
			[
				'label' => __( 'Pagination type', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'list' => __( 'List', 'elementor-pro' ),
					'' => __( 'Default', 'elementor-pro' ),
				],
				'condition' => [
					'pagination'  => 'yes',
				],
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'content_layout',
			[
				'label' => __( 'Content', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'meta_data',
			[
				'label' => __( 'Meta Data', 'elementor-pro' ),
				'label_block' => true,
				'type' => Controls_Manager::SELECT2,
				'default' => [ 'date', 'comments' ],
				'multiple' => true,
				'options' => [
					'author' => __( 'Author', 'elementor-pro' ),
					'date' => __( 'Date', 'elementor-pro' ),
					'time' => __( 'Time', 'elementor-pro' ),
					'comments' => __( 'Comments', 'elementor-pro' ),
					'modified' => __( 'Date Modified', 'elementor-pro' ),
				],
				'separator' => 'before',
			]
		);	
		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'list_meta',
			[
				'label' => __( 'Meta Data', 'elementor-pro' ),
				'label_block' => true,
				'type' => Controls_Manager::SELECT,				
				'multiple' => true,
				'options' => [
					'thumbnail' => __( 'Thumbnail', 'elementor-pro' ),					
					'title' => __( 'Title', 'elementor-pro' ),
					'excerpt' => __( 'Excerpt', 'elementor-pro' ),
					'category' => __( 'Category', 'elementor-pro' ),
					'readmore'=> __('Read more','elementor-pro'),
					'author' => __( 'Author', 'elementor-pro' ),
					'date' => __( 'Date', 'elementor-pro' ),
					'comment' =>__( 'Comments', 'elementor-pro' ),
					'meta' => __('List of meta: Author, date, comment','elementor-pro')
				],
				'description' => 'if META: Go to Layout to select meta for showing'			
			]
		);
		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'thumbnail_size',
				'default' => 'medium',
				'exclude' => [ 'custom' ],
				'prefix_class' => 'elementor-posts--thumbnail-size-',
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);
		
		$repeater->add_control(
			'thumbnail_link',
			[
				'label' => __( 'Link for thumbnail', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'your-plugin' ),
				'label_off' => __( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'no',
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);
		$repeater->add_control(
			'img_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .thumbnail img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);
		$repeater->add_control(
			'thumbnail_padding',
			[
				'label' => __( 'Thumbnail Padding', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .thumbnail img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);
		$repeater->add_control(
			'thumbnail_margin',
			[
				'label' => __( 'Thumbnail Margin', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .thumbnail img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);

		$repeater->start_controls_tabs( 'thumbnail_effects_tabs' );

		$repeater->start_controls_tab( 'normal',
			[
				'label' => __( 'Normal', 'elementor-pro' ),
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);

		$repeater->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'thumbnail_filters',
				'selector' => '{{WRAPPER}} .thumbnail img',
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'hover',
			[
				'label' => __( 'Hover', 'elementor-pro' ),
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);

		$repeater->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'thumbnail_hover_filters',
				'selector' => '{{WRAPPER}} .elementor-post:hover .thumbnail img',
				'condition' => [
					'list_meta' => 'thumbnail'
				]
			]
		);

		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();

		$repeater->add_control(
			'title_size',
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
				'condition' => [
					'list_meta' => 'title'
				]
			]
		);
		$repeater->add_control(
			'title_color',
			[
				'label' => __( 'Title Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .title *' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'title'
				]
			]
		);
		$repeater->add_control(
			'title_link',
			[
				'label' => __( 'Link for title', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'your-plugin' ),
				'label_off' => __( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'No',
				'condition' => [
					'list_meta' => 'title'
				]
			]
		);
		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .title, {{WRAPPER}} .title *',
				'condition' => [
					'list_meta' => 'title'
				],
			]
		);
		$repeater->add_control(
			'title_margin',
			[
				'label' => __( 'Title Margin', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'title'
				]
			]
		);
		$repeater->add_control(
			'excerpt_length',
			[
				'label' => __( 'Excerpt length', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => '15',
				'condition' => [
					'list_meta' => 'excerpt'
				]
			]
		);
		$repeater->add_control(
			'excerpt_color',
			[
				'label' => __( 'Excerpt Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .excerpt' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'excerpt'
				]
			]
		);
		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .excerpt, {{WRAPPER}} .excerpt *',
				'condition' => [
					'list_meta' => 'excerpt'
				],
			]
		);
		$repeater->add_control(
			'excerpt_margin',
			[
				'label' => __( 'Excerpt Margin', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'excerpt'
				]
			]
		);	
		$repeater->add_control(
			'category_color',
			[
				'label' => __( 'Category Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .category *' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'category'
				]
			]
		);	
		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'category_typography',
				'selector' => '{{WRAPPER}} .category, {{WRAPPER}} .category *',
				'condition' => [
					'list_meta' => 'category'
				],
			]
		);
		$repeater->add_control(
			'category_margin',
			[
				'label' => __( 'Category Margin', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .category' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'category'
				]
			]
		);
		$repeater->add_control(
			'readmore_text',
			[
				'label' => __( 'Readmore text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => __('Detail','elementor-pro'),
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		
		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'readmore_typography',
				'selector' => '{{WRAPPER}} .readmore_text, {{WRAPPER}} .readmore_text *',
				'condition' => [
					'list_meta' => 'readmore'
				],
			]
		);
		$repeater->add_control(
			'readmore_margin',
			[
				'label' => __( 'Read more Margin', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .readmore_text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [ 'top' => 15, 'right'=> 0, 'bottom'=> 15, 'left'=> 0 ],
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		$repeater->start_controls_tabs( 'readmore_effects_tabs' );

		$repeater->start_controls_tab( 'readmore_normal',
			[
				'label' => __( 'Normal', 'elementor-pro' ),
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);

		$repeater->add_control(
			'readmore_color',
			[
				'label' => __( 'Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .readmore_text a' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'readmore'
				],
				'default' => '#231f20'
			]
		);
		$repeater->add_control(
			'readmore_bgcolor',
			[
				'label' => __( 'Background Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .readmore_text a' => 'background-color: {{VALUE}} !important',
				],
				'default' => '#fff',
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		$repeater->add_control(
			'readmore_padding',
			[
				'label' => __( 'Padding', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .readmore_text a' => ' padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [ 'top' => 10, 'right'=> 10, 'bottom'=> 10, 'left'=> 10 ],
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		$repeater->add_control(
			'readmore_border-color',
			[
				'label' => __( 'border Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .readmore_text a' => 'border-style: solid ; border-color: {{VALUE}} !important',
				],
				'default' => '#231f20',
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);

		$repeater->add_control(
			'readmore_border_width',
			[
				'label' => __( 'Border width', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .readmore_text a' => ' border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [ 'top' => 1, 'right'=> 1, 'bottom'=> 1, 'left'=> 1 ],
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		$repeater->add_control(
			'readmore_border_radius',
			[
				'label' => __( 'Border radius', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .readmore_text a' => ' border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [ 'top' => 5, 'right'=> 5, 'bottom'=> 5, 'left'=> 5 ],
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'readmore_hover',
			[
				'label' => __( 'Hover', 'elementor-pro' ),
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);

		$repeater->add_control(
			'readmore_color_hover',
			[
				'label' => __( 'Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .readmore_text a:hover' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'readmore'
				],
				'default' => '#fff'
			]
		);
		$repeater->add_control(
			'readmore_bgcolor_hover',
			[
				'label' => __( 'Background Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .readmore_text a:hover' => 'background-color: {{VALUE}} !important',
				],
				'default' => '#231f20',
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		$repeater->add_control(
			'readmore_padding_hover',
			[
				'label' => __( 'Padding', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .readmore_text a:hover' => ' padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [ 'top' => 10, 'right'=> 10, 'bottom'=> 10, 'left'=> 10 ],
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		$repeater->add_control(
			'readmore_border-color_hover',
			[
				'label' => __( 'border Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .readmore_text a:hover' => 'border-style: solid ; border-color: {{VALUE}} !important',
				],
				'default' => '#231f20',
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);

		$repeater->add_control(
			'readmore_border_width_hover',
			[
				'label' => __( 'Border width', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .readmore_text a:hover' => ' border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [ 'top' => 1, 'right'=> 1, 'bottom'=> 1, 'left'=> 1 ],
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);
		$repeater->add_control(
			'readmore_border_radius_hover',
			[
				'label' => __( 'Border radius', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .readmore_text a:hover' => ' border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [ 'top' => 5, 'right'=> 5, 'bottom'=> 5, 'left'=> 5 ],
				'condition' => [
					'list_meta' => 'readmore'
				]
			]
		);


		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();


		$repeater->add_control(
			'author_color',
			[
				'label' => __( 'Author Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .author' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'author'
				]
			]
		);
		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'author_typography',
				'selector' => '{{WRAPPER}} .author',
				'condition' => [
					'list_meta' => 'author'
				],
			]
		);
		$repeater->add_control(
			'author_margin',
			[
				'label' => __( 'Author Margin', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .author' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'author'
				]
			]
		);
		$repeater->add_control(
			'date_color',
			[
				'label' => __( 'Date Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .date' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'date'
				]
			]
		);
		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'date_typography',
				'selector' => '{{WRAPPER}} .date',
				'condition' => [
					'list_meta' => 'date'
				],
			]
		);
		$repeater->add_control(
			'date_margin',
			[
				'label' => __( 'Date Margin', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'date'
				]
			]
		);
		$repeater->add_control(
			'comment_color',
			[
				'label' => __( 'Comment Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .comment' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'comment'
				]
			]
		);
		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'comment_typography',
				'selector' => '{{WRAPPER}} .comment',
				'condition' => [
					'list_meta' => 'comment'
				],
			]
		);
		$repeater->add_control(
			'comment_margin',
			[
				'label' => __( 'Comment Margin', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .comment' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'list_meta' => 'comment'
				]
			]
		);
		$repeater->add_control(
			'meta_color',
			[
				'label' => __( 'Meta data Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post .elementor-post__meta-data *' => 'color: {{VALUE}} !important',
				],
				'condition' => [
					'list_meta' => 'meta'
				]
			]
		);
		$this->add_control(
			'content',
			[
				'label' => __( 'List Item', 'elementor' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'list_meta' => 'thumbnail',
					],
					[
						'list_meta' => 'title',
					],
					[
						'list_meta' => 'excerpt',
					],
					[
						'list_meta' => 'readmore'
					]
				],
				'title_field' => '{{{ list_meta }}}',
			]
		);
		$this->end_controls_section();
		
		// start tab style
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'layout',
			[
				'label' => __('Layout', 'elementor-pro'),
				'type' => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid' => __("Grid",'elementor-pro'),					
				],
				'prefix_class' => 'elementor-',
			]
		);
		$this->add_responsive_control(
			'columns',
			[
				'label' => __('Columns', 'elementor-pro'),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => 1,
					'2' => 2,
					'3' => 3,
					'4' => 4,
					'5' => 5,
					'6' => 6,
				],
				'prefix_class' => 'elementor-grid%s-',
				'frontend_available' => true,
			]
		);	
		$this->add_control(
			'column_gap',
			[
				'label' => __( 'Columns Gap', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 30,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'row_gap',
			[
				'label' => __( 'Rows Gap', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 35,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'frontend_available' => true,
				'selectors' => [
					'{{WRAPPER}}' => '--grid-row-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);		
		$this->add_control(
			'display_order',
			[
				'label' => __( 'Display order', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'display: flex; flex-direction: column;',
				'options' => [
					'display: flex; flex-direction: column;' => __( 'Vertical', 'elementor-pro' ),
					'display: flex; flex-direction: column-reverse;' => __( 'Vertical Reverse', 'elementor-pro' ),
					'display: flex; flex-direction: row;' => __( 'Horizontal', 'elementor-pro' ),
					'display: flex; flex-direction: row-reverse;' => __( 'Horizontal Reverse', 'elementor-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-post' => '{{VALUE}}',
				],
			]
		);
		$this->add_control(
			'alignment',
			[
				'label' => __( 'Alignment', 'elementor-pro' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'left',
				'options' => [
					'left' => [
						'title' => __( 'Left', 'elementor-pro' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'elementor-pro' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'elementor-pro' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-post' => 'text-align: {{VALUE}}',
				],
			]
		);
		
		$this->end_controls_section();

		$this->start_controls_section(
			'section_design_box',
			[
				'label' => __( 'Box', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'box_border_width',
			[
				'label' => __( 'Border Width', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-post' => 'border-style: solid; border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'box_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-post' => 'border-radius: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'box_padding',
			[
				'label' => __( 'Padding', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'top' => 10, 'right'=> 10, 'bottom'=> 10, 'left'=> 10
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-post' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		/*$this->add_control(
			'content_padding',
			[
				'label' => __( 'Content Padding', 'elementor-pro' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-post__text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
				'separator' => 'after',
			]
		);
*/
		$this->start_controls_tabs( 'bg_effects_tabs' );

		$this->start_controls_tab( 'classic_style_normal',
			[
				'label' => __( 'Normal', 'elementor-pro' ),
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'selector' => '{{WRAPPER}} .elementor-post',
			]
		);

		$this->add_control(
			'box_bg_color',
			[
				'label' => __( 'Background Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post' => 'background-color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'box_text_color',
			[
				'label' => __( 'Text Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post *' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'box_border_color',
			[
				'label' => __( 'Border Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'classic_style_hover',
			[
				'label' => __( 'Hover', 'elementor-pro' ),
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow_hover',
				'selector' => '{{WRAPPER}} .elementor-post:hover',
			]
		);

		$this->add_control(
			'box_bg_color_hover',
			[
				'label' => __( 'Background Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post:hover' => 'background-color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'box_text_color_hover',
			[
				'label' => __( 'Text Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post:hover *' => 'color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'box_border_color_hover',
			[
				'label' => __( 'Border Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-post:hover' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
		
		
	}
	private function get_terms ($taxonomy ){
		$return = [];
		$arg = array( 
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			 );
		$terms = get_terms($arg);
		if(!empty($terms)){
			foreach ($terms as $key => $value) {
				if(isset($value->term_id) and isset($value->name)){
					$return[$value->term_id] = $value->name;
				}
			}
		}
		return $return;
	}
	private function get_default($arr,$default){
		if(empty($arr)){
			return $default;
		}else {
			foreach ($arr as $key => $value) {
				return $key;
			}
		}
		return ;
	}
	public function get_authors(){
		$users = get_users();
		$return = array();		
		foreach ($users as $user) {
		   $return[$user->ID] = $user->display_name;		   
		}
		return $return ; 

	}
	public function get_not_in_ids($post_type,$taxonomy = null,$term = null){
		$return = [];
		$args = array(
		    'post_type' => $post_type,
		    'posts_per_page'=>-1
		);
		$query = new \WP_Query( $args );
		if ( $query->have_posts() ) {
		    while ( $query->have_posts() ) {
		        $query->the_post();
		        $return[get_the_ID()] = get_the_title();
		    }
		}
		wp_reset_postdata();
		return $return ; 
	}
	public function render(){
		$args = [];
		$settings = $this->get_settings_for_display(); $args['post_type'] = $settings['post_type'];
		$args['posts_per_page'] = $settings['posts_per_page'];
		$args['tax_query'] = [
			'relation' => 'AND'
		];
		if($settings['taxonomy-'.$settings['post_type']]){
			$terms = $settings['term-'.$settings['post_type']."-".$settings['taxonomy-'.$settings['post_type']]];
			if(isset($terms)){
				$args['tax_query'][] = [
					'taxonomy' => $settings['taxonomy-'.$settings['post_type']],
					'field' => 'id',
					'terms' => $terms,
					'include_children' => false,
					'operator' => "IN"
				];
			}
		}
		
 		$args['post__not_in'] = $settings['post__not_in-'.$settings['post_type']];
 		$args['order'] = $settings['order'];
 		$args['orderby'] = $settings['orderby'];
 		$args['ignore_sticky_posts'] = $settings['ignore_sticky_posts'] == 'yes' ? true : false;
 		$args['nopaging'] = true;
 		if($settings['pagination'] == 'yes'){
 			$args['nopaging'] = false;
 			$args['paged'] = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
 		}
		
		$this->add_render_attribute( [
			'wrapper' => [
				'class' => 'elementor-grid elementor-posts-container elementor-posts',
			],
			
		] );
		$query = new \WP_Query( $args );
		if ( $query->have_posts() ) {
			?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		    <?php 
	    	while ( $query->have_posts() ) {
		        $query->the_post();

		        $archive_class = ['elementor-post','elementor-grid-item'];
		        $archive_class = array_merge($archive_class,get_post_class());
		        $this->add_render_attribute( [
					'archive'.get_the_ID() => [
						'class' => $archive_class,
					],
					
				] );

		        echo '<archive '.$this->get_render_attribute_string('archive'.get_the_ID()).'>';

		        if(!empty($settings['content'])){
		        	foreach ($settings['content'] as $meta) {
		        		switch ($meta['list_meta']) {
		        			case 'thumbnail':
		        				echo '<div class="thumbnail">';		        				
        						echo sprintf(
        							'%1$s%2$s%3$s', 
        							($meta['thumbnail_link'] =='yes')? '<a href="'.get_the_permalink().'">' : "", 
        							get_the_post_thumbnail(get_the_ID(), $meta['thumbnail_size_size']), 
        							($meta['thumbnail_link'] =='yes')? '</a>' : ""
        							) ;
	        					echo '</div>';
		        				break;
		        			case 'category':
		        				echo '<div class="category">';
		        				echo the_category(', ');
								echo '</div>';
		        				break;
	        				case 'title':
	        					echo '<div class="title">';
	        					echo sprintf(
        							'%1$s%2$s%3$s', 
        								($meta['title_link'] =='yes')? '<a href="'.get_the_permalink().'">' : "",
        								sprintf('<%1$s>%2$s</%1$s>',$meta['title_size'],get_the_title()),
        								($meta['title_link'] =='yes')? '</a>' : ""
        							) ;
		        				echo '</div>';
		        				break;
	        				case 'excerpt':
	        					echo '<div class="excerpt">';
		        				echo wp_trim_words( get_the_content(), $meta['excerpt_length'], '' );
		        				echo '</div>';
		        				break;
	        				case 'author':
	        					echo '<div class="author">';
		        				echo get_the_author();
		        				echo '</div>';
		        				break;
	        				case 'date':
	        					echo '<div class="date">';
		        				echo get_the_date();
		        				echo '</div>';
		        				break;
	        				case 'comment':
	        					echo '<div class="comment">';
		        				echo get_comments_number()? get_comments_number() : __("No","elementor-pro")." ". " " . __("comments",'elementor');
		        				echo '</div>';
		        				break;
	        				case 'readmore':
	        					echo '<div class="readmore_text">';
	        					echo '<a href="'.get_the_permalink().'">'.$meta['readmore_text'].'</a>';
	        					echo '</div>';
	        					break;
        					case 'meta':
        						echo '<div class="elementor-post__meta-data">';
	        					if(!empty($settings['meta_data'])){
	        						foreach ($settings['meta_data'] as $key) {
	        							switch ($key) {
	        								case 'author':
	        									echo '<span class="author">';
						        				echo get_the_author();
						        				echo '</span> ';
						        				break;
	        								case 'date':
	        									echo '<span class="date">';
						        				echo get_the_date();
						        				echo '</span> ';
	        									break;
        									case 'time':
	        									echo '<span class="time">';
						        				echo get_the_time();
						        				echo '</span> ';
	        									break;
        									case 'comments':
	        									echo '<span class="comment">';
						        				echo get_comments_number()? get_comments_number() : __("No","elementor-pro")." ". " " . __("comments",'elementor');
						        				echo '</span> ';
	        									break;
        									case 'modified':
	        									echo '<span class="comment">';
						        				echo get_the_modified_date();
						        				echo '</span> ';
	        									break;
	        								default:
	        									# code...
	        									break;
	        							}
	        						}
	        					}

	        					echo '</div>';
        						break;
		        			default:
		        				
		        				break;
		        		}
		        	}
		        }
		        
		        
		        
		        echo "</archive>";
		    }	

		    ?>
			</div> <!-- end wrapper-->
		    <div class="mk-pagination">
		    	<?php
			    $big = 999999999;
			    $pag_args = array(
		          	'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
		          	'format' => '?paged=%#%',
		          	'current' => $args['paged'],
		          	'total' => $query->max_num_pages,	          	
		          	'prev_text' => $settings['pagination_prev_text'],
		          	'next_text' => $settings['pagination_next_text'],
		          	'prev_next' => in_array($settings['pagination_show'],['all','prev_next']) ? true: false,
		          	'show_all' => $settings['pagination_show_all'] == 'yes'? true: false,
		          	'end_size' => $settings['pagination_end_size'],
		          	'mid_size' => $settings['pagination_mid_size'],
		          	'type' 	=> $settings['pagination_type']
	     		);
	     		ob_start();
		     	echo paginate_links( $pag_args);
		     	$pagination_html = ob_get_contents();
		     	ob_end_clean();
		     	preg_match_all('/class="prev.*<\/a>/i',$pagination_html,$out_prev);
				preg_match_all('/class="next.*<\/a>/i',$pagination_html,$out_next);
		     	$prev_html = !empty($out_prev[0][0]) ? "<a ".$out_prev[0][0] ."</a>": "";
		     	$next_html = !empty($out_next[0][0]) ? "<a ".$out_next[0][0] ."</a>": "";
				if ($settings['pagination_show'] == 'prev_next'){
					echo $prev_html;
					echo $next_html;
				}else{
					echo $pagination_html;
				}
		     	?>
	     	</div>
	     	<?php
		}
		wp_reset_postdata();
		//echo "<pre>";print_r($settings);echo "</pre>";
	}
}