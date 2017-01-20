<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale = 1.0, maximum-scale=1.0, user-scalable=no" />
	<link href='http://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Droid+Serif:regular,bold" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php if (of_get_option('ttrust_favicon') ) : ?>
		<link rel="shortcut icon" href="<?php echo of_get_option('ttrust_favicon'); ?>" />
	<?php endif; ?>
	<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>	
</head>

<body <?php body_class(); ?> >
	<?php $ttrust_menu_type = of_get_option('ttrust_menu_type'); ?>
	<?php if($ttrust_menu_type != "standard"): ?>
		<div id="slideNav" class="panel">
			<a href="javascript:jQuery.pageslide.close()" class="closeBtn"></a>								
			<div id="mainNav">
				<?php wp_nav_menu( array('menu_class' => '', 'theme_location' => 'main', 'fallback_cb' => 'default_nav_slide' )); ?>
			</div>
			<?php if(is_active_sidebar('sidebar_slidenav')) : ?>
			<div class="widgets">
				<?php dynamic_sidebar('sidebar_slidenav'); ?>
			</div>
			<?php endif; ?>			
		</div>
	<?php endif; ?>
<div id="container">	
<div id="header">
	<div class="top">
		<div class="inside clearfix">
			<?php $logoHeadTag = (is_front_page()) ? "h1" : "h3";	?>					
			<?php $ttrust_logo = of_get_option('logo'); ?>
			<div id="logo" class="top__logo-container">
			<?php if($ttrust_logo) : ?>				
				<<?php echo $logoHeadTag; ?> class="logo"><a href="<?php bloginfo('url'); ?>"><img src="<?php echo $ttrust_logo; ?>" alt="<?php bloginfo('name'); ?>" /></a></<?php echo $logoHeadTag; ?>>
			<?php else : ?>				
				<<?php echo $logoHeadTag; ?>><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></<?php echo $logoHeadTag; ?>>				
			<?php endif; ?>	
			</div>			
			<a href="/donate" class="top__donate">Donate</a>
			<a href="#slideNav" class="menuToggle top__menu-toggle"></a>				
			
		</div>		
	</div>
	<?php if(is_front_page() && of_get_option('ttrust_banner_enabled')) : ?>
	<div class="bottom">
		<div id="homeBanner" class="hasBackground">
			<div id="bannerText">				
				<div class="main"><span class="innerBannerText"><?php echo of_get_option('ttrust_home_banner_text_main'); ?></span></div>
				<div class="secondary"><?php echo do_shortcode(of_get_option('ttrust_home_banner_text_secondary')); ?></div>
			</div>
			<div id="downButton"></div>
		</div>
	</div>
	<?php endif; ?>	
</div>

