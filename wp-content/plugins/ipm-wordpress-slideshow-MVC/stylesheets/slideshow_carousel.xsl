<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>



<!-- slideshow -->

<xsl:template match="/slideshows/slideshow">

<![CDATA[<div class="slideshow_wrapper"> ]]>
<![CDATA[<h3 class='slideshow_title'>]]><xsl:value-of select="title"/><![CDATA[</h3>]]>
	<![CDATA[<div class="slideshow"> ]]>
	
	<!-- here goes the intro slide stuff -->
	
	
	<!-- and now, the rest of the images -->
	
	<xsl:for-each select="photo">
	
		<![CDATA[<div class='photoitem'>]]>
	

			<![CDATA[<img alt="]]> <xsl:value-of select="alt"/> <![CDATA[" src="]]><xsl:value-of select="large_url"/><![CDATA[" />]]>
			<![CDATA[<div class='photo_meta'>]]>
				<xsl:if test="title">
					<![CDATA[<span class="title">]]><xsl:value-of select="title"/><![CDATA[</span>]]>
				</xsl:if>
			<![CDATA[</div>]]>

		<![CDATA[</div>]]>

	</xsl:for-each>
	
	

	<![CDATA[</div>]]> <!-- end class popeye -->

	<![CDATA[<ul id='thumb_list' class='jcarousel-skin-tango'> ]]>
	<xsl:for-each select="photo">
		<![CDATA[<li> ]]>
			<![CDATA[<img style='width:75px;height:75px;cursor:pointer;' onclick="viewpic(jQuery(this).parent().attr('jcarouselindex')-1);" alt="]]> <xsl:value-of select="title"/> <![CDATA[" src="]]><xsl:value-of select="thumb_url"/><![CDATA[" />]]>
		<![CDATA[</li>]]>

	</xsl:for-each>
	<![CDATA[</ul>]]>
<![CDATA[</div> ]]>
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
    scroll:2 ,
    visible: 5

  });

  jQuery('.slideshow div:first').addClass('active');

});
</script>
]]>


</xsl:template>

</xsl:stylesheet>
