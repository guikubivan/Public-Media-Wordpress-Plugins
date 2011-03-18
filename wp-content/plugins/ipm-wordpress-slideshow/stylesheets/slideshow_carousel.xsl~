<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>



<!-- slideshow -->

<xsl:template match="/slideshows/slideshow">


	<![CDATA[<div class="slideshow"> ]]>
	
	<![CDATA[<h3>]]><xsl:value-of select="title"/><![CDATA[</h3>]]>
	
	
	<!-- here goes the intro slide stuff -->
	
	
	<!-- and now, the rest of the images -->
	
	<xsl:for-each select="photo">
	
		<![CDATA[<div class='photoitem'>]]>
	

			<![CDATA[<img alt="]]> <xsl:value-of select="alt"/> <![CDATA[" src="]]><xsl:value-of select="large_url"/><![CDATA[" />]]>
			<![CDATA[<p class='photometa'>]]>
				<xsl:if test="title">
					<![CDATA[<span class="title">]]><xsl:value-of select="title"/><![CDATA[</span>]]>
				</xsl:if>
				<xsl:if test="photo_credit">
					<![CDATA[<span class="credit">]]><xsl:value-of select="photo_credit"/><![CDATA[</span>]]>
				</xsl:if>
			<![CDATA[</p>]]>

		<![CDATA[</div>]]>

	</xsl:for-each>
	
	

	<![CDATA[</div>]]> <!-- end class popeye -->

	<![CDATA[<ul id='thumb_list' class='jcarousel-skin-tango'> ]]>
	<xsl:for-each select="photo">
		<![CDATA[<li> ]]>
			<![CDATA[<img style='width:150px;height:150px' onclick="viewpic(jQuery(this).parent().attr('jcarouselindex')-1);" alt="]]> <xsl:value-of select="title"/> <![CDATA[" src="]]><xsl:value-of select="thumb_url"/><![CDATA[" />]]>
		<![CDATA[</li>]]>

	</xsl:for-each>
	<![CDATA[</ul>]]>

<![CDATA[
<script type='text/javascript'>
jQuery.noConflict();

function viewpic(index){
  var active = jQuery('.slideshow div.active');
  var curIndex = jQuery('div.photoitem').index(active);
  if(curIndex == index)return;
  var selector = '.slideshow div.photoitem:eq('+index+')';
  var next = jQuery(selector);
  next.css('opacity', '0.0');
  active.removeClass('active last-active');

  active.fadeTo('slow', 0.0);
  //active.css('display', 'hidden').fadeOut('slow');
  next.fadeTo('slow', 1.0);
  next.addClass('active');
}


jQuery(document).ready(function(){


  jQuery('#thumb_list').jcarousel({
    scroll:2 
  });

  jQuery('.slideshow div:first').addClass('active');

});
</script>
]]>


</xsl:template>

</xsl:stylesheet>
