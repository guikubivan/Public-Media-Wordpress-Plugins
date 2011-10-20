<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 * Modified to meet PBS MERLIN spec
 * @package WordPress
 */
 
$options = get_option('merlin_channel_elements');

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
$more = 1;

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:pbscontent="http://www.pbs.org/rss/pbscontent/" 
	xmlns:media="http://search.yahoo.com/mrss/" 
	xmlns:dcterms="http://purl.org/dc/terms/"
	<?php do_action('rss2_ns'); ?>
>
<channel>
	<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description")?>
	</description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<?php the_generator( 'rss2' ); ?>
	<language><?php echo get_option('rss_language'); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
	
	<category><![CDATA[<?php echo $options['category'];	?>]]></category>
	<image>
		<url><?php echo $options['image'];?></url>
		<title><?php echo $options['image_title'];?></title>
		<link><?php echo $options['image_link'];?></link>
	</image>
	<pbscontent:program_name><?php echo $options['program_name']; ?></pbscontent:program_name>
	<pbscontent:producing_member_station><?php echo $options['producing_member_station']; ?></pbscontent:producing_member_station>
	<pbscontent:owner_member_station><?php echo $options['owner_member_station']; ?></pbscontent:owner_member_station>
	<?php 
	/*
	Items to add:
	category - default categories for all items in channel
	image - image to be displayed with channel
	pbscontent:program_name
	pbscontent:producing_member_station
	pbscontent:owner_member_station
	*/
	?>
	<?php do_action('rss2_head'); ?>
	
	<?php while( have_posts()) : the_post(); ?>
	<?php $program_name = get_post_meta($post->ID, 'program_name', true); 
		$producing_member_station = get_post_meta($post->ID, 'producing_member_station',  true);
		$owner_member_station = get_post_meta($post->ID, 'owner_member_station', true);
		$thumbnail = get_post_meta($post->ID, 'thumbnail', true);
		$expiration_date = get_post_meta($post->ID, 'expiration_date', true);
		$distribution = get_post_meta($post->ID, 'distribution', true);
	?>
	<item>
		<title><?php the_title_rss();?></title>
		<link><?php the_permalink_rss() ?></link>
		<comments><?php comments_link(); ?></comments>
		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
		<dc:creator><?php the_author() ?></dc:creator>
		<?php
		/*
		to add:
		media:description (same as description?)
		pbscontent:program_name
		pbscontent:producing_member_station
		pbscontent:owner_member_station
		media:thumbnail (288x162)
		dcterms:valid
		pbscontent:distribution
		*/
		
		/*
		$program_name = get_post_meta($post->ID, 'program_name', true);
		$producing_member_station = get_post_meta($post->ID, 'producing_member_station',  true);
		$owner_member_station = get_post_meta($post->ID, 'owner_member_station', true);
		$thumbnail = get_post_meta($post->ID, 'thumbnail', true);
		$expiration_date = get_post_meta($post->ID, 'expiration_date', true);
		$distribution = get_post_meta($post->ID, 'distribution', true);
		*/
		//$merlin_excerpt = get_the_excerpt();
		//$merlin_long_excerpt = shorten_text($merlin_excerpt, 395);	
		$merlin_long_excerpt = shorten_text(get_the_excerpt(), 390);
		$merlin_short_excerpt = shorten_text(get_the_excerpt(), 80);
		
		
		?>
		<?php the_category_merlin() ?>
		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<description><![CDATA[<?php echo ($merlin_long_excerpt); ?>]]></description>
		<media:description>
		<![CDATA[<?php echo ($merlin_short_excerpt); ?>]]>
		
		</media:description>
		<?php if ( strlen( $post->post_content ) > 0 ) : ?>
			<content:encoded><![CDATA[<?php the_content_feed('rss2') ?>]]></content:encoded>
		<?php else : ?>
			<content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
		<?php endif; ?>
		
		<pbscontent:program_name><?php echo($program_name); ?></pbscontent:program_name>
		<pbscontent:producing_member_station><?php echo($producing_member_station); ?></pbscontent:producing_member_station>
		<pbscontent:owner_member_station><?php echo($owner_member_station); ?></pbscontent:owner_member_station>
		<pbscontent:distribution><?php echo($distribution); ?></pbscontent:distribution>
		<dcterms:valid><?php echo($expiration_date); ?> </dcterms:valid>
		<media:thumbnail><?php if ($thumbnail == null) {
										//if item-level thumb is left blank, use channel-level instead.
										echo $options['image'];
									} else {
										echo($thumbnail);	
									}?></media:thumbnail>
		<wfw:commentRss><?php echo get_post_comments_feed_link(null, 'rss2'); ?></wfw:commentRss>
		<slash:comments><?php echo get_comments_number(); ?></slash:comments>

		<?php rss_enclosure(); ?>
		<?php do_action('rss2_item'); ?>
	</item>
	<?php endwhile; ?>

</channel>
</rss>
