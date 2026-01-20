<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Appointix_Info_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'appointix_info';
	}

	public function get_title() {
		return __( 'XIO Info Box', 'appointix' );
	}

	public function get_icon() {
		return 'eicon-text-area';
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
			'image',
			array(
				'label'   => __( 'Image', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'About XIO', 'appointix' ),
			)
		);

		$this->add_control(
			'content',
			array(
				'label'   => __( 'Content', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::WYSIWYG,
				'default' => __( 'Located directly on the seafront in Bar, XIO Apartments offer comfort, privacy, and Adriatic views.<br><br>Susanjska Beach is a <strong>2-minute walk</strong> away.<br>Podgorica Airport: <strong>41 km</strong> | Tivat Airport: <strong>57 km</strong><br><br>Free private parking is available on-site.', 'appointix' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="xio-info-box">
			<div class="xio-info-grid">
				<div class="xio-info-image-col">
					<?php 
					$img_url = ! empty( $settings['image']['url'] ) ? $settings['image']['url'] : plugin_dir_url( __DIR__ . '/../../' ) . 'assets/images/xio-about.png';
					?>
					<img src="<?php echo esc_url( $img_url ); ?>" alt="About XIO" class="xio-info-image">
				</div>
				<div class="xio-info-content-col">
					<?php if ( ! empty( $settings['title'] ) ) : ?>
						<h2 class="xio-info-title"><?php echo esc_html( $settings['title'] ); ?></h2>
					<?php endif; ?>
					<div class="xio-info-content">
						<?php echo $settings['content']; ?>
					</div>
					<div class="xio-info-action">
						 <a href="#availability" class="xio-btn-primary"><?php _e('Book Now', 'appointix'); ?></a>
					</div>
				</div>
			</div>
		</div>

		<style>
			.xio-info-box {
				padding: 60px 0;
				max-width: 1200px;
				margin: 0 auto;
			}
			.xio-info-grid {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 50px;
				align-items: center;
			}
			.xio-info-image-col {
				position: relative;
			}
			.xio-info-image {
				width: 100%;
				height: auto;
				border-radius: 20px;
				box-shadow: 0 20px 40px rgba(0,0,0,0.1);
				object-fit: cover;
				min-height: 400px;
			}
			.xio-info-title {
				font-size: 2.5rem; 
				margin-bottom: 30px; 
				color: #1e293b;
				font-weight: 800;
				line-height: 1.2;
				background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
			}
			.xio-info-content {
				font-size: 1.1rem; 
				line-height: 1.8; 
				color: #64748b;
				margin-bottom: 40px;
			}
			.xio-btn-primary {
				display: inline-block;
				background: linear-gradient(135deg, #6366f1, #4f46e5);
				color: #fff;
				padding: 16px 32px;
				border-radius: 50px;
				text-decoration: none;
				font-weight: 600;
				box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
				transition: transform 0.3s ease, box-shadow 0.3s ease;
			}
			.xio-btn-primary:hover {
				transform: translateY(-2px);
				box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
				color: #fff;
			}
			@media (max-width: 768px) {
				.xio-info-grid {
					grid-template-columns: 1fr;
					gap: 30px;
				}
				.xio-info-image {
					min-height: 250px;
				}
				.xio-info-title {
					font-size: 2rem;
				}
			}
		</style>
		<?php
	}
}
