<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Appointix_Amenities_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'appointix_amenities';
	}

	public function get_title() {
		return __( 'XIO Amenities', 'appointix' );
	}

	public function get_icon() {
		return 'eicon-star';
	}

	public function get_categories() {
		return array( 'appointix-widgets' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Amenities', 'appointix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Free private parking', 'appointix' ),
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-check-circle',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'amenities_list',
			array(
				'label'       => __( 'Amenities List', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'text' => __( 'Verified by Booking.com', 'appointix' ),
						'icon' => array( 'value' => 'fas fa-check-circle' ),
					),
					array(
						'text' => __( 'Free private parking', 'appointix' ),
						'icon' => array( 'value' => 'fas fa-parking' ),
					),
					array(
						'text' => __( 'Free WiFi', 'appointix' ),
						'icon' => array( 'value' => 'fas fa-wifi' ),
					),
					array(
						'text' => __( 'Family-friendly', 'appointix' ),
						'icon' => array( 'value' => 'fas fa-child' ),
					),
				),
				'title_field' => '{{{ text }}}',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="xio-amenities-list">
			<?php foreach ( $settings['amenities_list'] as $item ) : ?>
				<div class="xio-amenity-item">
					<span class="xio-amenity-icon">
						<?php \Elementor\Icons_Manager::render_icon( $item['icon'], array( 'aria-hidden' => 'true' ) ); ?>
					</span>
					<span class="xio-amenity-text"><?php echo wp_kses_post( $item['text'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>

		<style>
			/* Define the custom property for smooth angle interpolation */
			@property --xio-border-angle {
				syntax: "<angle>";
				initial-value: 0deg;
				inherits: false;
			}

			.xio-amenities-list {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
				gap: 20px;
				padding: 20px 0;
			}

			.xio-amenity-item {
				display: flex;
				align-items: center;
				gap: 15px;
				padding: 20px 24px;
				border-radius: 12px;
				box-shadow: 0 4px 6px rgba(0,0,0,0.02);
				transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
				position: relative;
				
				/* Static State */
				border: 2px solid #e5e7eb; /* Light gray border by default */
				background: #fff;
			}

			/* Hover State with Houdini Animation */
			.xio-amenity-item:hover {
				transform: translateY(-5px);
				box-shadow: 0 15px 30px rgba(0,0,0,0.1);
				
				/* The Magic: Layered Backgrounds */
				border: 2px solid transparent; /* Make border transparent so gradient shows */
				background: 
					linear-gradient(#fff, #fff) padding-box, /* Inner White Content Background */
					conic-gradient(
						from var(--xio-border-angle),
						transparent 0%,
						#b8a36c 50%,
						transparent 100%
					) border-box; /* Outer Rotating Gradient Border */
				
				animation: xioBorderRotate 2s linear infinite;
			}

			@keyframes xioBorderRotate {
				from { --xio-border-angle: 0deg; }
				to { --xio-border-angle: 360deg; }
			}

			/* Icon Styling */
			.xio-amenity-icon {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 48px;
				height: 48px;
				background: transparent;
				border-radius: 12px;
				color: #4f46e5;
				font-size: 1.2rem;
				transition: all 0.3s ease;
			}

			.xio-amenity-item:hover .xio-amenity-icon {
				background: transparent; /* Remove background on hover */
				color: #b8a36c;
			}

			/* Text Styling */
			.xio-amenity-text {
				font-weight: 600;
				color: #334155;
				display: flex;
				font-size: 1.05rem;
			}
			.xio-amenity-text span{
			    background: #b8a36c;
                color: white;
                padding: 3px 5px;
                border-radius: 7px;
                height: max-content;
                width: max-content;
                min-width: 40px;
                text-align: center;
			}
			
			/* Icon SVG fill transitions */
			.xio-amenity-icon svg {
                fill: #b8a36c;
                transition: fill 0.3s ease;
                    width: 50px;
                 height: 50px;
            }
            .xio-amenity-item:hover .xio-amenity-icon svg {
                fill: #b8a36c; /* Gold fill on hover */
            }
            @media screen and ( max-width: 500px ) {
                .xio-amenities-list{
                    display:block;
                }
                .xio-amenity-text {
                    font-size: 16px;
                    gap: 7px;
                }
                .xio-amenity-item {
                    margin-bottom: 15px;
                }
            }
		</style>
		<?php
	}
}
