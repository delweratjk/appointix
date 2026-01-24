<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Appointix_Apartments_Grid_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'appointix_apartments_grid';
	}

	public function get_title() {
		return __( 'XIO Apartment Grid', 'appointix' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
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
			'posts_per_page',
			array(
				'label'   => __( 'Posts Per Page', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		if ( ! class_exists( 'Appointix_Apartments_Model' ) ) {
			return;
		}

		$apartments = Appointix_Apartments_Model::get_latest_apartments( $settings['posts_per_page'] );
		$currency = get_option( 'appointix_currency', '$' );

		if ( empty( $apartments ) ) {
			echo '<p>' . __( 'No apartments found.', 'appointix' ) . '</p>';
			return;
		}
		?>
		<div class="xio-apartments-grid">
			<?php foreach ( $apartments as $apartment ) : ?>
				<div class="xio-apt-card">
					<div class="xio-apt-thumb">
						<?php if ( $apartment->thumbnail ) : ?>
							<img src="<?php echo esc_url( $apartment->thumbnail ); ?>" alt="<?php echo esc_attr( $apartment->name ); ?>">
						<?php endif; ?>
					</div>
					<div class="xio-apt-info">
						<h3><?php echo esc_html( $apartment->name ); ?></h3>
						<p class="xio-apt-desc"><?php echo esc_html( $apartment->property_summary ); ?></p>
						<div class="xio-apt-meta">
							<span><i class="fas fa-bed"></i> <?php echo esc_html( $apartment->bedrooms ); ?> <?php echo esc_html__( 'Bedrooms', 'appointix' ); ?></span>
							<span><i class="fas fa-users"></i> <?php echo esc_html( $apartment->max_guests ); ?> <?php echo esc_html__( 'Guests', 'appointix' ); ?></span>
						</div>
						<a href="<?php echo esc_url( $apartment->permalink ); ?>" class="xio-apt-link">
							<?php _e( 'View Apartment', 'appointix' ); ?>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<style>
			.xio-apartments-grid {
				display: grid;
				grid-template-columns: repeat(2, 1fr);
				gap: 30px;
				padding: 20px 0;
			}
			.xio-apt-card {
				background: #fff;
				border-radius: 12px;
				overflow: hidden;
				box-shadow: 0 10px 30px rgba(0,0,0,0.05);
				transition: transform 0.3s ease;
				border: 1px solid gainsboro;
			}
			.xio-apt-card:hover {
				transform: translateY(-5px);
			}
			.xio-apt-thumb {
				position: relative;
				height: 250px;
			}
			.xio-apt-thumb img {
				width: 100%; height: 100%; object-fit: cover;
			}
			.xio-apt-info {
				padding: 25px;
			}
			.xio-apt-info h3 {
				margin: 0 0 10px; font-size: 1.4rem;
			}
			.xio-apt-desc {
				color: #666; font-size: 0.95rem; margin-bottom: 20px;
				display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
			}
			.xio-apt-meta {
				display: flex; gap: 20px; margin-bottom: 25px; font-size: 0.9rem; color: #444;
			}
			.xio-apt-meta i { color: #b8a36c; margin-right: 5px; }
			.xio-apt-link {
				display: block; text-align: center; padding: 12px;
				background: #f8f9fa; color: #333; text-decoration: none;
				font-weight: 600; border-radius: 6px; transition: all 0.3s ease;border: 1px solid gainsboro;
			}
			.xio-apt-link:hover {
				background: #b8a36c;
				border-color: #e3c000;
			}
			@media screen and ( max-width: 767px ) {
				.xio-apartments-grid {
					grid-template-columns: repeat(1, 1fr); 
				}
			}
		</style>
		<?php
	}
}
