<?php

namespace Glcdesign\HashtagWp;

class Customizer
{

	/**
	 * @var Plugin Plugin class
	 */
	private $plugin;

	/**
	 * @param Plugin instance which initiated the Theme Customizer
	 */
	public function __construct ( Plugin $plugin )
	{

		//assigning plugin
		$this->plugin = $plugin;
		
		//we add an action to register the settings
		add_action( 'customize_register', array( &$this, 'register' ), 10, 1 );
		
		//we add an action to register the live preview
		add_action( 'customize_preview_init', array( &$this, 'live_preview' ) );
		
		//we add an action for the css
		add_action( 'wp_head', array( &$this, 'css' ) );
		
	}
	
	public function register ( $wp_customize )
	{
		
		//we register the section for plugin
		$wp_customize->add_section(
			'glcdesign-hashtag-wp',
			array(
				'title'    => __( 'Hashtag WP', Plugin::TEXTDOMAIN ),
				'priority' => 200
			)
		);
		
		//we register the setting for background enabled
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-background-enabled',
			array(
				'default'   => true,
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for background enabled
		$wp_customize->add_control(
			new \WP_Customize_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-background-enabled',
				array(
					
					'label'         => __( 'Background Enabled', Plugin::TEXTDOMAIN ),
					'section'       => 'glcdesign-hashtag-wp',
					'settings'      => 'glcdesign-hashtag-wp-background-enabled',
					'type'          => 'checkbox',
					'std'           => true
				)
			
			)
		
		);
		
		//we register the setting for background color
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-background-color',
			array(
				'default'   => '#fafafa',
				'transport' => 'postMessage'
			)
		);
		
		
		//we register the control for the background color
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-background-color',
				array(
					
					'label'    => __( 'Background Color', Plugin::TEXTDOMAIN ),
					'section'  => 'glcdesign-hashtag-wp',
					'settings' => 'glcdesign-hashtag-wp-background-color'
				
				)
			
			)
		
		);
		
		//we register the setting for border enabled
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-border-enabled',
			array(
				'default'   => true,
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for border enabled
		$wp_customize->add_control(
			new \WP_Customize_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-border-enabled',
				array(
					
					'label'         => __( 'Border Enabled', Plugin::TEXTDOMAIN ),
					'section'       => 'glcdesign-hashtag-wp',
					'settings'      => 'glcdesign-hashtag-wp-border-enabled',
					'type'          => 'checkbox',
					'std'           => true
				)
			
			)
		
		);
		
		//we register the setting for border color
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-border-color',
			array(
				'default'   => '#eaeaea',
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for the border color
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-border-color',
				array(
					
					'label'    => __( 'Border Color', Plugin::TEXTDOMAIN ),
					'section'  => 'glcdesign-hashtag-wp',
					'settings' => 'glcdesign-hashtag-wp-border-color'
				
				)
			
			)
		
		);
		
		//we register the setting for hash color
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-hash-color',
			array(
				'default'   => '#747474',
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for the hash color
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-hash-color',
				array(
					
					'label'    => __( 'Hash Color', Plugin::TEXTDOMAIN ),
					'section'  => 'glcdesign-hashtag-wp',
					'settings' => 'glcdesign-hashtag-wp-hash-color'
				
				)
			
			)
		
		);
		
		//we register the setting for text color enabled
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-text-color-enabled',
			array(
				'default'   => false,
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for text color enabled
		$wp_customize->add_control(
			new \WP_Customize_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-text-color-enabled',
				array(
					
					'label'         => __( 'Text Color Enabled', Plugin::TEXTDOMAIN ),
					'section'       => 'glcdesign-hashtag-wp',
					'settings'      => 'glcdesign-hashtag-wp-text-color-enabled',
					'type'          => 'checkbox',
					'std'           => false
				)
			
			)
		
		);
		
		//we register the setting for text color
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-text-color',
			array(
				'default'   => '',
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for the text color
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-text-color',
				array(
					
					'label'    => __( 'Text Color', Plugin::TEXTDOMAIN ),
					'section'  => 'glcdesign-hashtag-wp',
					'settings' => 'glcdesign-hashtag-wp-text-color'
				
				)
			
			)
		
		);
		
		//we register the setting for font size
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-font-size',
			array(
				'default'   => 0.750,
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for the font size
		$wp_customize->add_control(
			new \WP_Customize_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-font-size',
				array(
					
					'label'         => __( 'Font Size', Plugin::TEXTDOMAIN ),
					'section'       => 'glcdesign-hashtag-wp',
					'settings'      => 'glcdesign-hashtag-wp-font-size',
					'type'          => 'range',
					'input_attrs'    => array(
						'min'   => 0.5,
						'max'   => 1.2,
						'step'  => 0.01
					)
				
				)
			
			)
		
		);

		//we register the setting for hash spacing
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-hash-spacing',
			array(
				'default'   => 1,
				'transport' => 'postMessage'
			)
		);

		//we register the control for the hash spacing
		$wp_customize->add_control(
			new \WP_Customize_Control(

				$wp_customize,
				'glcdesign-hashtag-wp-hash-spacing',
				array(

					'label'         => __( 'Hash Spacing', Plugin::TEXTDOMAIN ),
					'section'       => 'glcdesign-hashtag-wp',
					'settings'      => 'glcdesign-hashtag-wp-hash-spacing',
					'type'          => 'range',
					'input_attrs'   => array(
						'min'   => 1,
						'max'   => 5,
						'step'  => 0.5
					)

				)

			)

		);
		
		//we register the setting for hash font size
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-hash-font-size',
			array(
				'default'   => 0.825,
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for the hash font size
		$wp_customize->add_control(
			new \WP_Customize_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-hash-font-size',
				array(
					
					'label'         => __( 'Hash Font Size', Plugin::TEXTDOMAIN ),
					'section'       => 'glcdesign-hashtag-wp',
					'settings'      => 'glcdesign-hashtag-wp-hash-font-size',
					'type'          => 'range',
					'input_attrs'   => array(
						'min'   => 0.5,
						'max'   => 1.2,
						'step'  => 0.01
					)
				
				)
			
			)
		
		);
		
		//we register the setting for padding
		$wp_customize->add_setting(
			'glcdesign-hashtag-wp-padding',
			array(
				'default'   => 0.825,
				'transport' => 'postMessage'
			)
		);
		
		//we register the control for the padding
		$wp_customize->add_control(
			new \WP_Customize_Control(
				
				$wp_customize,
				'glcdesign-hashtag-wp-padding',
				array(
					
					'label'         => __( 'Padding', Plugin::TEXTDOMAIN ),
					'section'       => 'glcdesign-hashtag-wp',
					'settings'      => 'glcdesign-hashtag-wp-padding',
					'type'          => 'range',
					'input_attrs'   => array(
						'min'   => 00,
						'max'   => 10,
						'step'  => 0.5
					)
				
				)
			
			)
		
		);

		//enqueuing style
		wp_enqueue_style( 'glcdesign-hashtag-wp-customizer', $this->plugin->getUri() . 'assets/scss/customizer.css', array(), $this->plugin->getVersion() );
		
	}
	
	public function live_preview ()
	{
		
		//we register the theme customizer file
		wp_register_script(
			'glcdesign-hashtag-wp-customizer',
			$this->plugin->getUri() . 'assets/js/customizer.js',
			array( 'jquery', 'customize-preview' ),
			$this->plugin->getVersion(),
			true
		);
		
		//we enqueue the script
		wp_enqueue_script( 'glcdesign-hashtag-wp-customizer' );
		
	}
	
	public function css ()
	{
		
		//we generate the css
		
		echo "<style type=\"text/css\">\n";

		//if background enabled
		if( get_theme_mod( 'glcdesign-hashtag-wp-background-enabled', true ) )
		{
			//background color
			$this->generate_css(
				'.glcdesign-hashtag-wp',
				'background-color',
				'background-color'
			);
		}
		else
		{
			echo '.glcdesign-hashtag-wp { background: none; }';
		}

		//if borders enabled
		if( get_theme_mod( 'glcdesign-hashtag-wp-border-enabled', true ) )
		{
			//border color
			$this->generate_css(
				'.glcdesign-hashtag-wp',
				'border-color',
				'border-color'
			);
		}
		else
		{
			echo '.glcdesign-hashtag-wp { border-color: transparent; }';
		}

		//hash color
		$this->generate_css(
			'.glcdesign-hashtag-wp i',
			'color',
			'hash-color'
		);

		//if text color enabled
		if( get_theme_mod( 'glcdesign-hashtag-wp-text-color-enabled', false ) )
		{
			//text color
			$this->generate_css(
				'.glcdesign-hashtag-wp',
				'color',
				'text-color'
			);
		}

		//font size
		$this->generate_css(
			'.glcdesign-hashtag-wp',
			'font-size',
			'font-size',
			'',
			'em'
		);

		//hash spacing
		$this->generate_css(
			'.glcdesign-hashtag-wp i',
			'margin-right',
			'hash-spacing',
			'',
			'px'
		);

		//hash font size
		$this->generate_css(
			'.glcdesign-hashtag-wp i',
			'font-size',
			'hash-font-size',
			'',
			'em'
		);

		//padding
		$this->generate_css(
			'.glcdesign-hashtag-wp',
			'padding',
			'padding',
			'',
			'px'
		);

		echo "\n</style>";
		
	}

	//Function taken from WordPress Codex
	public function generate_css ( $selector, $style, $mod_name, $prefix = '', $postfix = '', $default = false, $callback = false, $echo = true )
	{
		$return = '';
		$mod = get_theme_mod( 'glcdesign-hashtag-wp-' . $mod_name, $default );
		if( is_callable( $callback ) )
		{
			$mod = call_user_func( $callback, $mod );
		}
		if ( $mod !== false )
		{
			$return = sprintf(
				"\n\t%s \n\t{\n\t\t %s:%s; \n\t}\n",
				$selector,
				$style,
				$prefix . $mod . $postfix
			);
			if ( $echo )
			{
				echo $return;
			}
		}
		return $return;
	}
	
}