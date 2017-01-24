<?php
/*
Plugin Name: Lux Vimeo
Plugin URI: http://www.partnervermittlung-ukraine.net/info/lux-vimeo-wordpress-plugin
Description: Allows the user to embed Vimeo movie clips by entering a shortcode ([vimeo]) into the post area.
Author: Matroschka
Version: 1.3
Author URI: http://www.pastukhova-floeder.de/
License: GPL 2.0, @see http://www.gnu.org/licenses/gpl-2.0.html
*/

class lux_vimeo
{
	function shortcode($atts, $content = null)
	{
		extract(shortcode_atts(array(
			'clip_id' => '',
			'width' => '400',
			'height' => '225',
			'title' => '1',
			'byline' => '1',
			'portrait' => '1',
			'color' => '',
		), $atts));

		if (empty($clip_id) || !is_numeric($clip_id)) return '<!-- Lux Vimeo: Invalid clip_id -->';
		if ($height && !$atts['width']) $width = intval($height * 16 / 9);
		if (!$atts['height'] && $width) $height = intval($width * 9 / 16);

		return
			"<iframe src='http://player.vimeo.com/video/$clip_id?title=$title&amp;byline=$byline&amp;portrait=$portrait&amp;color=$color' width='$width' height='$height' frameborder='0'></iframe>";
	}
}

add_shortcode('vimeo', array('lux_vimeo', 'shortcode'));
