<?php
/*
Plugin Name: Kony 2012 Countdown
Plugin URI: http://plugins.twinpictures.de/plugins/kony-2012-countdown/
Description: Countdown to events leading to the arrest and prosecution of Joseph Kony in 2012 for crimes against humanity.
Version: 0.3
Author: twinpictures, baden03
Author URI: http://www.twinpictures.de/
License: GPL2
*/

/*  Copyright 2012 Twinpictures (www.twinpictures.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//init scripts
function kony_2012_countdown_init(){
        $plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
		if (!is_admin()){
				wp_enqueue_script('jquery');
				
				//lwtCountdown script
				wp_register_script('countdown-script', $plugin_url.'/js/jquery.t-countdown-1.0.js', array ('jquery'), '1.0' );
				wp_enqueue_script('countdown-script');
				
				//css
				wp_register_style( 'kony-2012-css', $plugin_url.'/css/kony/style.css', array (), '1.0' );
				wp_enqueue_style( 'kony-2012-css' );
				
				//shortcode
				add_shortcode('kony2012', 'kony2012');
		}
}
add_action('init', 'kony_2012_countdown_init');

/**
 * Kony2012Countdown Class
 */
class Kony2012Countdown extends WP_Widget {
    /** constructor */
    function Kony2012Countdown() {
		$widget_ops = array('classname' => 'Kony2012Countdown', 'description' => __('Countdown to events leading to the capture of Joseph Kony in 2012') );
		$this->WP_Widget('Kony2012Countdown', 'Kony 2012 Countdown', $widget_ops);
    }
	
    /** Widget */
    function widget($args, $instance) {
		global $add_my_script;
        extract( $args );
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$tophtml = empty($instance['tophtml']) ? ' ' : apply_filters('widget_tophtml', $instance['tophtml']);
        $bothtml = empty($instance['bothtml']) ? ' ' : apply_filters('widget_bothtml', $instance['bothtml']);
		
		//now
		$now = time() + ( get_option( 'gmt_offset' ) * 3600);
		
		//April 20, 2012
		$hour = 0; 
		$min = 0; 
		$sec = 0;
		$day = 20;
		$month = 04;
		$year = 2012;
		
		//Dec 1 Jan, 2013
		if($now > 1334966399){
				$day = 01;
				$month = 01;
				$year = 2013;	
		}
		
		
		$weektitle = empty($instance['weektitle']) ? 'weeks' : apply_filters('widget_weektitle', $instance['weektitle']);
		$daytitle = empty($instance['daytitle']) ? 'days' : apply_filters('widget_daytitle', $instance['daytitle']);
		$hourtitle = empty($instance['hourtitle']) ? 'hours' : apply_filters('widget_hourtitle', $instance['hourtitle']);
		$mintitle = empty($instance['mintitle']) ? 'minutes' : apply_filters('widget_mintitle', $instance['mintitle']);
		$sectitle = empty($instance['sectitle']) ? 'seconds' : apply_filters('widget_sectitle', $instance['sectitle']);
		$omitweeks = 'true';
		$jsplacement = empty($instance['jsplacement']) ? 'footer' : apply_filters('widget_jsplacement', $instance['jsplacement']);
		
		
		//target
		$target = mktime(
			$hour, 
			$min, 
			$sec, 
			$month, 
			$day, 
			$year
		);
		
		//difference in seconds
		$diffSecs = $target - $now;
		
		//countdown digits
		$date = array();
		$date['secs'] = $diffSecs % 60;
		$date['mins'] = floor($diffSecs/60)%60;
		$date['hours'] = floor($diffSecs/60/60)%24;
		if($omitweeks == 'false'){
		    $date['days'] = floor($diffSecs/60/60/24)%7;
		}
		else{
		    $date['days'] = floor($diffSecs/60/60/24); 
		}
		$date['weeks']	= floor($diffSecs/60/60/24/7);
	
		foreach ($date as $i => $d) {
			$d1 = $d%10;
			//53 = 3
			//153 = 3
	
			if($d < 100){
				$d2 = ($d-$d1) / 10;
				//53 = 50 / 10 = 5
				$d3 = 0;
			}
			else{
				$dr = $d%100;
				//153 = 53
				//345 = 45
				$dm = $d-$dr;
				//153 = 100
				//345 = 300
				$d2 = ($d-$dm-$d1) / 10;
				//153 = 50 / 10 = 5
				//345 = 40 / 10 = 4
				$d3 = $dm / 100;
			}
			/* here is where the 1000's support will go... someday. */
			
			//now assign all the digits to the array
			$date[$i] = array(
				(int)$d3,
				(int)$d2,
				(int)$d1,
				(int)$d
			);
		}
		
		
        echo $before_widget;
        if ( $title ){
            echo $before_title . $title . $after_title;
        }
		echo '<div id="'.$args['widget_id'].'-widget">';
		echo '<div id="'.$args['widget_id'].'-tophtml" class="kony-tophtml" >';
        if($tophtml){
            echo stripslashes($tophtml); 
        }
		echo '</div>';
		
		//drop in the dashboard
		echo '<div id="'.$args['widget_id'].'-dashboard" class="kony-dashboard">';
		
			if($omitweeks == 'false'){
				//set up correct style class for double or triple digit love
				$wclass = 'kony-dash kony-weeks_dash';
				if($date['weeks'][0] > 0){
					$wclass = 'kony-tripdash kony-weeks_trip_dash';
				}
			
				echo '<div class="'.$wclass.'">
						<span class="kony-dash_title">'.$weektitle.'</span>';
						//show third week digit if the number of weeks is greater than 99
				if($date['weeks'][0] > 0){
					echo '<div class="kony-digit">'.$date['weeks'][0].'</div>';
				}
				echo '<div class="kony-digit">'.$date['weeks'][1].'</div>
						<div class="kony-digit">'.$date['weeks'][2].'</div>
					</div>'; 
			}
					
			//set up correct style class for double or triple digit love
			$dclass = 'kony-dash kony-days_dash';
			if($omitweeks == 'true' && $date['days'][3] > 99){
				$dclass = 'kony-tripdash kony-days_trip_dash';
			}
			
			echo '<div class="'.$dclass.'">
					<span class="kony-dash_title">'.$daytitle.'</span>';
			//show third day digit if there are NO weeks and the number of days is greater that 99
			if($omitweeks == 'true' && $date['days'][3] > 99){
				echo '<div class="kony-digit">'.$date['days'][0].'</div>';
			}
			echo '<div class="kony-digit">'.$date['days'][1].'</div>
				<div class="kony-digit">'.$date['days'][2].'</div>
			</div>
	
			<div class="kony-dash kony-hours_dash">
				<span class="kony-dash_title">'.$hourtitle.'</span>
				<div class="kony-digit">'.$date['hours'][1].'</div>
				<div class="kony-digit">'.$date['hours'][2].'</div>
			</div>
	
			<div class="kony-dash kony-minutes_dash">
				<span class="kony-dash_title">'.$mintitle.'</span>
				<div class="kony-digit">'.$date['mins'][1].'</div>
				<div class="kony-digit">'.$date['mins'][2].'</div>
			</div>
	
			<div class="kony-dash kony-seconds_dash">
				<span class="kony-dash_title">'.$sectitle.'</span>
				<div class="kony-digit">'.$date['secs'][1].'</div>
				<div class="kony-digit">'.$date['secs'][2].'</div>
			</div>
        </div>'; //close the dashboard
		
        echo '<div id="'.$args['widget_id'].'-bothtml" class="kony-bothtml">';
        if($bothtml){
            echo  stripslashes($bothtml);    
        }
		echo '</div>';
		echo '</div>';
		echo $after_widget;
		$t = date( 'n/j/Y H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600));
		
		//launch div
		$launchdiv = "";
		if($launchtarget == "Above Countdown"){
			$launchdiv = "tophtml";
		}
		else if($launchtarget == "Below Countdown"){
			$launchdiv = "bothtml";
		}
		else if($launchtarget == "Entire Widget"){
			$launchdiv = "widget";
		}
		$id = $args['widget_id'];
		if($jsplacement == "footer"){
			$add_my_script[$args['widget_id']] = array(
				'id' => $id,
				'day' => $day,
				'month' => $month,
				'year' => $year,
				'hour' => $hour,
				'min' => $min,
				'sec' => $sec,
				'localtime' => $t,
				'style' => 'kony',
				'omitweeks' => $omitweeks,
				'content' => trim($launchhtml),
				'launchtarget' => $launchdiv,
				'launchwidth' => 'auto',
				'launchheight' => 'auto'
			);
		}
		else{
			?>            
			<script language="javascript" type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('#<?php echo $args['widget_id']; ?>-dashboard').countDown({	
						targetDate: {
							'day': 	<?php echo $day; ?>,
							'month': 	<?php echo $month; ?>,
							'year': 	<?php echo $year; ?>,
							'hour': 	<?php echo $hour; ?>,
							'min': 	<?php echo $min; ?>,
							'sec': 	<?php echo $sec; ?>,
							'localtime':	'<?php echo $t; ?>'
						},
						style: 'kony',
						omitWeeks: <?php echo $omitweeks;
						if($launchhtml){
							echo ", onComplete: function() { jQuery('#".$args['widget_id']."-".$launchdiv."').html('".do_shortcode($launchhtml)."'); }";
						}
						?>
					});
				});
			</script>
			<?php
		}
    }

    /** Update */
    function update($new_instance, $old_instance) {
		$instance = array_merge($old_instance, $new_instance);
		return array_map('mysql_real_escape_string', $instance);
    }

    /** Form */
    function form($instance) {
        $title = stripslashes($instance['title']);
		$omitweeks = esc_attr($instance['omitweeks']);
		if(!$omitweeks){
			$omitweeks = 'false';
		}
		
		$jsplacement = esc_attr($instance['jsplacement']);
		if(!$jsplacement){
			$jsplacement = 'footer';
		}

		$weektitle = empty($instance['weektitle']) ? 'weeks' : apply_filters('widget_weektitle', stripslashes($instance['weektitle']));
		$daytitle = empty($instance['daytitle']) ? 'days' : apply_filters('widget_daytitle', stripslashes($instance['daytitle']));
		$hourtitle = empty($instance['hourtitle']) ? 'hours' : apply_filters('widget_hourtitle', stripslashes($instance['hourtitle']));
		$mintitle = empty($instance['mintitle']) ? 'minutes' : apply_filters('widget_mintitle', stripslashes($instance['mintitle']));
		$sectitle = empty($instance['sectitle']) ? 'seconds' : apply_filters('widget_sectitle', stripslashes($instance['sectitle']));
			
		$tophtml = empty($instance['tophtml']) ? ' ' : apply_filters('widget_tophtml', stripslashes($instance['tophtml']));
		$bothtml = empty($instance['bothtml']) ? ' ' : apply_filters('widget_bothtml', stripslashes($instance['bothtml']));
		$launchhtml = empty($instance['launchhtml']) ? ' ' : apply_filters('widget_launchhtml', stripslashes($instance['launchhtml']));
		$launchtarget = empty($instance['launchtarget']) ? 'After Counter' : apply_filters('widget_launchtarget', $instance['launchtarget']);
		?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('weektitle'); ?>"><?php _e('How do you spell "weeks"?:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('weektitle'); ?>" name="<?php echo $this->get_field_name('weektitle'); ?>" type="text" value="<?php echo $weektitle; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('daytitle'); ?>"><?php _e('How do you spell "days"?:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('daytitle'); ?>" name="<?php echo $this->get_field_name('daytitle'); ?>" type="text" value="<?php echo $daytitle; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('hourtitle'); ?>"><?php _e('How do you spell "hours"?:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('hourtitle'); ?>" name="<?php echo $this->get_field_name('hourtitle'); ?>" type="text" value="<?php echo $hourtitle; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('mintitle'); ?>"><?php _e('How do you spell "minutes"?:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('mintitle'); ?>" name="<?php echo $this->get_field_name('mintitle'); ?>" type="text" value="<?php echo $mintitle; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('sectitle'); ?>"><?php _e('And "seconds" are spelled:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('sectitle'); ?>" name="<?php echo $this->get_field_name('sectitle'); ?>" type="text" value="<?php echo $sectitle; ?>" /></label></p>
		<?php
    }
} // class CountDownTimer

// register CountDownTimer widget
add_action('widgets_init', create_function('', 'return register_widget("Kony2012Countdown");'));


//code fore the footer
add_action('wp_footer', 'print_kony2012_script');
 
function print_kony2012_script() {
	global $add_my_script;
 
	if ( ! $add_my_script ){
		return;
	}
	
	?>
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(function() {
	<?php			
	foreach((array) $add_my_script as $script){
	?>
		jQuery('#<?php echo $script['id']; ?>-dashboard').countDown({	
			targetDate: {
				'day': 	<?php echo $script['day']; ?>,
				'month': <?php echo $script['month']; ?>,
				'year': <?php echo $script['year']; ?>,
				'hour': <?php echo $script['hour']; ?>,
				'min': 	<?php echo $script['min']; ?>,
				'sec': 	<?php echo $script['sec']; ?>,
				'localtime': '<?php echo $script['localtime']; ?>'
			},
			style: '<?php echo $script['style']; ?>',
			omitWeeks: <?php echo $script['omitweeks'];
				if($script['content']){
					echo ", onComplete: function() {
						jQuery('#".$script['id']."-".$script['launchtarget']."').css({'width' : '".$script['launchwidth']."', 'height' : '".$script['launchheight']."'});
						jQuery('#".$script['id']."-".$script['launchtarget']."').html('".do_shortcode($script['content'])."');
					}";
				}?>
		});
	<?php
	}
	?>
			});
		</script>
	<?php
}

//the short code
function kony2012($atts, $content=null) {
	global $add_my_script;
	//find a random number, incase there is no id assigned
	$ran = rand(1, 10000);
	
    extract(shortcode_atts(array(
		'id' => $ran,
        'weeks' => 'weeks',
		'days' => 'days',
		'hours' => 'hours',
		'minutes' => 'minutes',
		'seconds' => 'seconds',
		'before' => '',
		'after' => '',
		'width' => 'auto',
		'height' => 'auto',
		'launchwidth' => 'auto',
		'launchheight' => 'auto',
		'launchtarget' => 'countdown',
		'jsplacement' => 'footer',
	), $atts));
		
	$now = time() + ( get_option( 'gmt_offset' ) * 3600);
	if($now > 1334966399){
		$t = "01-01-2013 00:00:00";
	}
	else{
		$t = "20-04-2012 00:00:00";
	}
	$target = strtotime($t, $now);
	$omitweeks = 'true';
	
	//difference in seconds
	$diffSecs = $target - $now;

	$day = date ( 'd', $target );
	$month = date ( 'm', $target );
	$year = date ( 'Y', $target );
	$hour = date ( 'H', $target );
	$min = date ( 'i', $target );
	$sec = date ( 's', $target );
	
	//countdown digits
	$date_arr = array();
	$date_arr['secs'] = $diffSecs % 60;
	$date_arr['mins'] = floor($diffSecs/60)%60;
	$date_arr['hours'] = floor($diffSecs/60/60)%24;
	
	if($omitweeks == 'false'){
		$date_arr['days'] = floor($diffSecs/60/60/24)%7;
	}
	else{
		$date_arr['days'] = floor($diffSecs/60/60/24); 
	}
	$date_arr['weeks']	= floor($diffSecs/60/60/24/7);
	
	foreach ($date_arr as $i => $d) {
		$d1 = $d%10;
		if($d < 100){
			$d2 = ($d-$d1) / 10;
			$d3 = 0;
		}
		else{
			$dr = $d%100;
			$dm = $d-$dr;
			$d2 = ($d-$dm-$d1) / 10;
			$d3 = $dm / 100;
		}
		/* here is where the 1000's support will go... someday. */
		
		//now assign all the digits to the array
		$date_arr[$i] = array(
			(int)$d3,
			(int)$d2,
			(int)$d1,
			(int)$d
		);
	}
	
	if(is_numeric($width)){
		$width .= 'px';
	}
	if(is_numeric($height)){
		$height .= 'px';
	}
	$tminus = '<div id="'.$id.'-countdown" style="width:'.$width.'; height:'.$height.';">';
	$tminus .= '<div id="'.$id.'-above" class="kony-tophtml">';
    if($before){
        $tminus .=  $before; 
    }
	$tminus .=  '</div>';
		
	//drop in the dashboard
	$tminus .=  '<div id="'.$id.'-dashboard" class="kony-dashboard">';
	if($omitweeks == 'false'){
		//set up correct style class for double or triple digit love
		$wclass = 'kony-dash kony-weeks_dash';
		if($date_arr['weeks'][0] > 0){
			$wclass = 'kony-tripdash kony-weeks_trip_dash';
		}
			
		$tminus .=  '<div class="'.$wclass.'"><span class="kony-dash_title">'.$weeks.'</span>';
		if($date_arr['weeks'][0] > 0){
			$tminus .=  '<div class="kony-digit">'.$date_arr['weeks'][0].'</div>';
		}
		$tminus .=  '<div class="kony-digit">'.$date_arr['weeks'][1].'</div><div class="kony-digit">'.$date_arr['weeks'][2].'</div></div>'; 
	}
					
	//set up correct style class for double or triple digit love
	$dclass = 'kony-dash kony-days_dash';
	if($omitweeks == 'true' && $date_arr['days'][3] > 99){
		$dclass = 'kony-tripdash kony-days_trip_dash';
	}
			
	$tminus .= '<div class="'.$dclass.'"><span class="kony-dash_title">'.$days.'</span>';
	//show thrid day digit if there are NO weeks and the number of days is greater that 99
	if($omitweeks == 'true' && $date_arr['days'][3] > 99){
		$tminus .= '<div class="kony-digit">'.$date_arr['days'][0].'</div>';
	}
		$tminus .= '<div class="kony-digit">'.$date_arr['days'][1].'</div><div class="kony-digit">'.$date_arr['days'][2].'</div>';
	$tminus .= '</div>';
	$tminus .= '<div class="kony-dash kony-hours_dash">';
		$tminus .= '<span class="kony-dash_title">'.$hours.'</span>';
		$tminus .= '<div class="kony-digit">'.$date_arr['hours'][1].'</div>';
		$tminus .= '<div class="kony-digit">'.$date_arr['hours'][2].'</div>';
	$tminus .= '</div>';
		$tminus .= '<div class="kony-dash kony-minutes_dash">';
		$tminus .= '<span class="kony-dash_title">'.$minutes.'</span>';
		$tminus .= '<div class="kony-digit">'.$date_arr['mins'][1].'</div>';
		$tminus .= '<div class="kony-digit">'.$date_arr['mins'][2].'</div>';
	$tminus .= '</div>';
		$tminus .= '<div class="kony-dash kony-seconds_dash">';
		$tminus .= '<span class="kony-dash_title">'.$seconds.'</span>';
		$tminus .= '<div class="kony-digit">'.$date_arr['secs'][1].'</div>';
		$tminus .= '<div class="kony-digit">'.$date_arr['secs'][2].'</div>';
	$tminus .= '</div>';
	$tminus .= '</div>'; //close the dashboard

	$tminus .= '<div id="'.$id.'-below" class="kony-bothtml">';
	if($after){
		$tminus .= $after;    
	}
	$tminus .= '</div></div>';

	$t = date( 'n/j/Y H:i:s', gmmktime() + ( get_option( 'gmt_offset' ) * 3600));
	
	if(is_numeric($launchwidth)){
		$launchwidth .= 'px';
	}
	if(is_numeric($launchheight)){
		$launchheight .= 'px';
	}
	$content = mysql_real_escape_string( $content);
	$content = str_replace(array('\r\n', '\r', '\n<p>', '\n'), '', $content);
	$content = stripslashes($content);
	if($jsplacement == "footer"){
		$add_my_script[$id] = array(
			'id' => $id,
			'day' => $day,
			'month' => $month,
			'year' => $year,
			'hour' => $hour,
			'min' => $min,
			'sec' => $sec,
			'localtime' => $t,
			'style' => 'kony',
			'omitweeks' => $omitweeks,
			'content' => $content,
			'launchtarget' => $launchtarget,
			'launchwidth' => $launchwidth,
			'launchheight' => $launchheight
		);
	}
	else{
		?>
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#<?php echo $id; ?>-dashboard').countDown({	
					targetDate: {
						'day': 	<?php echo $day; ?>,
						'month': <?php echo $month; ?>,
						'year': <?php echo $year; ?>,
						'hour': <?php echo $hour; ?>,
						'min': 	<?php echo $min; ?>,
						'sec': 	<?php echo $sec; ?>,
						'localtime': '<?php echo $t; ?>'
					},
					style: 'kony',
					omitWeeks: <?php echo $omitweeks;
						if($content){
							echo ", onComplete: function() {
								jQuery('#".$id."-".$launchtarget."').css({'width' : '".$launchwidth."', 'height' : '".$launchheight."'});
								jQuery('#".$id."-".$launchtarget."').html('".do_shortcode($content)."');	
							}";
						}?>
				});
			});
		</script>
		<?php		
	}
	return $tminus;
}

?>