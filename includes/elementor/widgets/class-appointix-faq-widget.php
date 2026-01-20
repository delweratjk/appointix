<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Appointix_FAQ_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'appointix_faq';
	}

	public function get_title() {
		return __( 'XIO FAQ Accordion', 'appointix' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	public function get_categories() {
		return array( 'appointix-widgets' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'FAQ Items', 'appointix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Section Title', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Frequently Asked Questions', 'appointix' ),
				'label_block' => true
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'question',
			array(
				'label'   => __( 'Question', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'What time is check-in and check-out?', 'appointix' ),
				'label_block' => true
			)
		);

		$repeater->add_control(
			'answer',
			array(
				'label'   => __( 'Answer', 'appointix' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'Check-in is from 15:00 to 19:00, and check-out is until 10:00.', 'appointix' ),
			)
		);

		$this->add_control(
			'faq_list',
			array(
				'label'       => __( 'FAQ List', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'question' => __( 'Check-in / Check-out', 'appointix' ), 'answer' => __( '15:00–19:00 / 10:00. Late check-in upon request.', 'appointix' ) ),
					array( 'question' => __( 'Beach?', 'appointix' ), 'answer' => __( 'It is a 2-minute walk away.', 'appointix' ) ),
					array( 'question' => __( 'Reservation?', 'appointix' ), 'answer' => __( 'You can reserve via our website, WhatsApp, or email.', 'appointix' ) ),
					array( 'question' => __( 'Parking?', 'appointix' ), 'answer' => __( 'Free private parking is available on-site.', 'appointix' ) ),
					array( 'question' => __( 'Family-friendly?', 'appointix' ), 'answer' => __( 'Yes, we are family-friendly.', 'appointix' ) ),
					array( 'question' => __( 'Bedrooms?', 'appointix' ), 'answer' => __( 'All apartments have two bedrooms.', 'appointix' ) ),
					array( 'question' => __( 'Kitchen?', 'appointix' ), 'answer' => __( 'All apartments have a fully equipped kitchen.', 'appointix' ) ),
					array( 'question' => __( 'WiFi?', 'appointix' ), 'answer' => __( 'Yes, free WiFi is available.', 'appointix' ) ),
					array( 'question' => __( 'Reception?', 'appointix' ), 'answer' => __( 'No, there is no 24/7 reception. Please announce your arrival.', 'appointix' ) ),
					array( 'question' => __( 'Airport transfer?', 'appointix' ), 'answer' => __( 'Available upon request.', 'appointix' ) ),
					array( 'question' => __( 'Smoking?', 'appointix' ), 'answer' => __( 'Smoking is not allowed inside the apartments.', 'appointix' ) ),
				),
				'title_field' => '{{{ question }}}',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id = 'xio-faq-' . $this->get_id();
		?>
		<div class="xio-faq-container">
			<?php if ( ! empty( $settings['title'] ) ) : ?>
				<h2 class="xio-faq-title"><?php echo esc_html( $settings['title'] ); ?></h2>
			<?php endif; ?>
			
			<div class="xio-faq-accordion" id="<?php echo esc_attr( $id ); ?>">
				<?php foreach ( $settings['faq_list'] as $index => $item ) : ?>
					<div class="xio-faq-item">
						<div class="xio-faq-question" data-target="#faq-ans-<?php echo esc_attr( $index ); ?>">
							<span><?php echo esc_html( $item['question'] ); ?></span>
							<i class="fas fa-chevron-down"></i>
						</div>
						<div class="xio-faq-answer" id="faq-ans-<?php echo esc_attr( $index ); ?>">
							<div class="xio-faq-answer-content">
								<?php echo wpautop( esc_html( $item['answer'] ) ); ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#<?php echo esc_js( $id ); ?> .xio-faq-question').on('click', function() {
				const item = $(this).closest('.xio-faq-item');
				const answer = $(this).next('.xio-faq-answer');
				
				if (item.hasClass('active')) {
					answer.slideUp();
					item.removeClass('active');
				} else {
					// Close others
					// $('#<?php echo esc_js( $id ); ?> .xio-faq-answer').slideUp();
					// $('#<?php echo esc_js( $id ); ?> .xio-faq-item').removeClass('active');
					
					answer.slideDown();
					item.addClass('active');
				}
			});
		});
		</script>

		<style>
			.xio-faq-container {
				width: 100%;
			}
			.xio-faq-title {
				text-align: center;
				font-size: 2.5rem;
				font-weight: 800;
				color: #1e293b;
				margin-bottom: 40px;
			}
			.xio-faq-accordion {
				width: 100%;
				margin: 0 auto;
				display: flex;
				flex-direction: column;
				gap: 15px;
			}
			.xio-faq-item {
				background: #fff;
				border-radius: 12px;
				box-shadow: 0 4px 15px rgba(0,0,0,0.03);
				border: 1px solid #f0f0f0;
				overflow: hidden;
				transition: all 0.3s ease;
			}
			.xio-faq-item:hover {
				box-shadow: 0 8px 25px rgba(0,0,0,0.06);
				transform: translateY(-2px);
			}
			.xio-faq-question {
				padding: 20px 25px;
				cursor: pointer;
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-weight: 700;
				font-size: 1.1rem;
				color: #2c3e50;
				transition: color 0.3s ease;
			}
			.xio-faq-item.active .xio-faq-question {
				color: black;
				background: #f8fbfd;
			}
			.xio-faq-question i {
				font-size: 0.9rem;
				color: #ccc;
				transition: transform 0.3s ease, color 0.3s ease;
			}
			.xio-faq-item.active .xio-faq-question i {
				transform: rotate(180deg);
				color: black;
			}
			.xio-faq-answer {
				display: none;
				border-top: 1px solid #f0f0f0;
			}
			.xio-faq-answer-content {
				padding: 25px;
				color: #555;
				line-height: 1.7;
				font-size: 1rem;
				background: #fff;
			}
		</style>
		<?php
	}
}
