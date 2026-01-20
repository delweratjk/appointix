<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Appointix_Hero_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'appointix_hero';
	}

	public function get_title() {
		return __( 'XIO Hero', 'appointix' );
	}

	public function get_icon() {
		return 'eicon-image-hotspot';
	}

	public function get_categories() {
		return array( 'appointix-widgets' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'appointix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Beachfront Two-Bedroom Apartments in Bar', 'appointix' ),
				'placeholder' => __( 'Beachfront Two-Bedroom Apartments in Bar', 'appointix' ),
				'label_block' => true
			)
		);

		$this->add_control(
			'description',
			array(
				'label'       => __( 'Description', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Enjoy a relaxing seaside stay at XIO Apartments, located directly on the beach in Bar, Montenegro. Spacious two-bedroom apartments, stunning sea views, and a calm, family-friendly atmosphere — ideal for couples and families.', 'appointix' ),
				'placeholder' => __( 'Enjoy a relaxing seaside stay...', 'appointix' ),
			)
		);

		$this->add_control(
			'bg_image',
			array(
				'label'   => __( 'Background Image', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
			)
		);

		$this->add_control(
			'cta_text_1',
			array(
				'label'   => __( 'CTA 1 Text', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Book Your Stay', 'appointix' ),
				'label_block' => true
			)
		);

		$this->add_control(
			'cta_link_1',
			array(
				'label'   => __( 'CTA 1 Link', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'default' => array(
					'url' => '#',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'Style', 'appointix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => __( 'Title Typography', 'appointix' ),
				'selector' => '{{WRAPPER}} .hero-title',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title Color', 'appointix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .hero-title' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
        	'description_color',
        	array(
        		'label' => __( 'Description Color', 'appointix' ),
        		'type' => \Elementor\Controls_Manager::COLOR,
        		'selectors' => array(
        			'{{WRAPPER}} .hero-description' => 'color: {{VALUE}};',
        		),
        	)
        );
        
        $this->add_control(
        	'button_text_color',
        	array(
        		'label' => __( 'Button Text Color', 'appointix' ),
        		'type' => \Elementor\Controls_Manager::COLOR,
        		'selectors' => array(
        			'{{WRAPPER}} .xio-btn-primary' => 'color: {{VALUE}};',
        		),
        	)
        );
        
        $this->add_control(
        	'button_bg_color',
        	array(
        		'label' => __( 'Button Background Color', 'appointix' ),
        		'type' => \Elementor\Controls_Manager::COLOR,
        		'selectors' => array(
        			'{{WRAPPER}} .xio-btn-primary' => 'background-color: {{VALUE}};',
        		),
        	)
        );
        
        $this->add_control(
        	'button_bg_hover_color',
        	array(
        		'label' => __( 'Button Hover Background', 'appointix' ),
        		'type' => \Elementor\Controls_Manager::COLOR,
        		'selectors' => array(
        			'{{WRAPPER}} .xio-btn-primary:hover' => 'background-color: {{VALUE}};',
        		),
        	)
        );



		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="xio-hero-section" style="background-image: url('<?php echo esc_url( $settings['bg_image']['url'] ); ?>');">
			<div class="xio-hero-overlay"></div>
			<div class="xio-hero-content">
				<h1 class="hero-title"><?php echo esc_html( $settings['title'] ); ?></h1>
				<p class="hero-description"><?php echo esc_html( $settings['description'] ); ?></p>
				<div class="hero-actions">
					<a href="<?php echo esc_url( $settings['cta_link_1']['url'] ); ?>" class="xio-btn xio-btn-primary">
						<?php echo esc_html( $settings['cta_text_1'] ); ?>
					</a>
				</div>
			</div>
		</section>

		<style>
			.xio-hero-section {
				position: relative;
				height: 100vh;
				background-size: cover;
				background-position: center bottom;
				display: flex;
				align-items: center;
				justify-content: center;
				text-align: center;
				color: #fff;
				padding: 20px;
			}
			.xio-hero-overlay {
				position: absolute;
				top: 0; left: 0; width: 100%; height: 100%;
				background: rgba(0,0,0,0.4);
			}
			.xio-hero-content {
				position: relative;
				max-width: 800px;
				z-index: 1;
			}
			.hero-title {
				font-size: 3.5rem;
				font-weight: 700;
				margin-bottom: 20px;
				line-height: 1.2;
			}
			.hero-description {
				font-size: 1.2rem;
				margin-bottom: 30px;
				line-height: 1.6;
			}
			.xio-btn {
				display: inline-block;
				padding: 15px 35px;
				border-radius: 5px;
				text-decoration: none;
				font-weight: 600;
				transition: all 0.3s ease;
			}
			.xio-btn-primary {
				background-color: #0077b6;
				color: #fff;
			}
			.xio-btn-primary:hover {
				background-color: #0096c7;
			}
			@media (max-width: 768px) {
				.hero-title { font-size: 2.5rem; }
				.hero-description { font-size: 1rem; }
			}
		</style>
		<?php
	}
}
