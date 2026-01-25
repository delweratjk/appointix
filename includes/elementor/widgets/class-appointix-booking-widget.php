<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Appointix_Booking_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'appointix_booking_widget';
	}

	public function get_title() {
		return __( 'XIO Booking', 'appointix' );
	}

	public function get_icon() {
		return 'eicon-calendar';
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
			'booking_form_heading',
			array(
				'label'       => __( 'Heading', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the heading for the booking form.', 'appointix' ),
			)
		);

		$this->add_control(
			'booking_form_subheading',
			array(
				'label'       => __( 'Subheading', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the subheading for the booking form.', 'appointix' ),
			)
		);
		
		$this->add_control(
			'booking_form_check_in',
			array(
				'label'       => __( 'Check-in', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the check-in text for the booking form.', 'appointix' ),
			)
		);

		$this->add_control(
			'booking_form_check_out',
			array(
				'label'       => __( 'Check-out', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the check-out text for the booking form.', 'appointix' ),
			)
		);

		$this->add_control(
			'booking_form_select_dates',
			array(
				'label'       => __( 'Select Dates', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the select dates text for the booking form.', 'appointix' ),
			)
		);

		$this->add_control(
			'booking_form_adults',
			array(
				'label'       => __( 'Adults', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the adults text for the booking form.', 'appointix' ),
			)
		);

		$this->add_control(
			'booking_form_children',
			array(
				'label'       => __( 'Children', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the children text for the booking form.', 'appointix' ),
			)
		);

		$this->add_control(
			'booking_form_check_availability',
			array(
				'label'       => __( 'Check Availability', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the check availability text for the booking form.', 'appointix' ),
			)
		);

		$this->add_control(
			'booking_form_apartment_label',
			array(
				'label'       => __( 'Apartment Label', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the apartment label for the booking form.', 'appointix' ),
				'default'     => __( 'Select Apartment', 'appointix' ),
			)
		);

		$this->add_control(
			'apartment_name',
			array(
				'label'       => __( 'Apartment Name', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Enter the apartment name (optional).', 'appointix' ),
			)
		);

		$this->add_control(
			'apartment_id',
			array(
				'label'       => __( 'Specific Apartment (optional)', 'appointix' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'description' => __( 'Enter the ID of a specific apartment to pre-select.', 'appointix' ),
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

		$this->add_control(
			'form_bg',
			array(
				'label'     => __( 'Form Background', 'appointix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .appointix-search-box' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'form_heading_bg',
			array(
				'label'     => __( 'Heading Background', 'appointix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .appointix-booking-header' => 'background: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'btn_bg',
			array(
				'label'     => __( 'Button Background', 'appointix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .appointix-btn-search' => 'background: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'btn_color',
			array(
				'label'     => __( 'Button Text Color', 'appointix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .appointix-btn-search' => 'color: {{VALUE}};',
				),
			)
		);
		
		
		$this->add_control(
			'btn_hover_bg',
			array(
				'label'     => __( 'Button Hover Background', 'appointix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .appointix-btn-search:hover' => 'background: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'btn_hover_color',
			array(
				'label'     => __( 'Button Text Hover Color', 'appointix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .appointix-btn-search:hover' => 'color: {{VALUE}};',
				),
			)
		);
		
    	$this->add_group_control(
        	\Elementor\Group_Control_Border::get_type(),
        	[
        		'name' => 'border',
        		'selectors' => [
        			'{{WRAPPER}} .appointix-search-box' => '{{VALUE}}',
        			'{{WRAPPER}} .appointix-input'  => '{{VALUE}}',
        		],
        	]
        );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$apartment_id = ! empty( $settings['apartment_id'] ) ? $settings['apartment_id'] : 0;
		// pass all the fields values by array
		$apartment_name = ! empty( $settings['apartment_name'] ) ? $settings['apartment_name'] : '';
		$booking_form_heading = ! empty( $settings['booking_form_heading'] ) ? $settings['booking_form_heading'] : '';
		$booking_form_subheading = ! empty( $settings['booking_form_subheading'] ) ? $settings['booking_form_subheading'] : '';
		$booking_form_check_in = ! empty( $settings['booking_form_check_in'] ) ? $settings['booking_form_check_in'] : '';
		$booking_form_check_out = ! empty( $settings['booking_form_check_out'] ) ? $settings['booking_form_check_out'] : '';
		$booking_form_select_dates = ! empty( $settings['booking_form_select_dates'] ) ? $settings['booking_form_select_dates'] : '';
		$booking_form_adults = ! empty( $settings['booking_form_adults'] ) ? $settings['booking_form_adults'] : '';
		$booking_form_children = ! empty( $settings['booking_form_children'] ) ? $settings['booking_form_children'] : '';
		$booking_form_check_availability = ! empty( $settings['booking_form_check_availability'] ) ? $settings['booking_form_check_availability'] : '';
		$booking_form_apartment_label = ! empty( $settings['booking_form_apartment_label'] ) ? $settings['booking_form_apartment_label'] : '';
		$show_apartment_dropdown = 'yes';
		$apartment_label = ! empty( $booking_form_apartment_label ) ? $booking_form_apartment_label : __( 'Select Apartment', 'appointix' );

		echo do_shortcode( sprintf(
			'[appointix_booking apartment_id="%s" apartment_name="%s" booking_form_heading="%s" booking_form_subheading="%s" booking_form_check_in="%s" booking_form_check_out="%s" booking_form_select_dates="%s" booking_form_adults="%s" booking_form_children="%s" booking_form_check_availability="%s" show_apartment_dropdown="%s" apartment_label="%s"]',
			esc_attr( $apartment_id ),
			esc_attr( $apartment_name ),
			esc_attr( $booking_form_heading ),
			esc_attr( $booking_form_subheading ),
			esc_attr( $booking_form_check_in ),
			esc_attr( $booking_form_check_out ),
			esc_attr( $booking_form_select_dates ),
			esc_attr( $booking_form_adults ),
			esc_attr( $booking_form_children ),
			esc_attr( $booking_form_check_availability ),
			esc_attr( $show_apartment_dropdown ),
			esc_attr( $apartment_label )
		) );
		
		?>
		<style>
		    @media screen and ( max-width: 450px ) {
		        .appointix-booking-header h2 {
                    font-size: 20px;
                }
                .appointix-booking-header {
                    padding: 20px 15px;
                }
                .appointix-btn-search {
                    height: auto;
                }
		    }
		</style>
		<?php
	}
}
