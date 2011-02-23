<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/slideshow">
<![CDATA[<div class="slideshow"> ]]>
	<xsl:if test="title">
		 <![CDATA[<h3 class="sidebar_header" style="margin-bottom:5px !important;">Slideshow title:]]><xsl:value-of select="title"/><![CDATA[</h3><br />]]>
	</xsl:if>

	<xsl:if test="description">
		Slideshow description:<xsl:value-of select="description"/><![CDATA[<br />]]>
	</xsl:if>

	<xsl:if test="photo_credit">
		Slideshow photo credit:<xsl:value-of select="photo_credit"/><![CDATA[<br />]]>
	</xsl:if>

	<xsl:if test="geo_location">
		Slideshow geo location:<xsl:value-of select="geo_location"/><![CDATA[<br />]]>
	</xsl:if>
	
	<!--launching the slideshow; this pulls the caption and url from the first photo in the slideshow to kickstart thickbox -->
	
	<![CDATA[<a class="thickbox" rel="slideshow" title="]]>
		<xsl:value-of select="photo[1]/caption"/>
	<![CDATA[" href="]]><xsl:value-of select="photo[1]/url"/> <![CDATA[" >Launch!</a>]]>
	
	
	<![CDATA[<div class="photo-list">]]>
	
	<xsl:for-each select="photo">
		<xsl:if test="thumb_url">
			<![CDATA[<img src="]]><xsl:value-of select="thumb_url"/><![CDATA[" /><br />]]>
		</xsl:if>

		<xsl:if test="url">
			<![CDATA[<a rel="slideshow" title="]]>
			<xsl:value-of select="caption"/>
			<![CDATA[" href="]]><xsl:value-of select="url"/> <![CDATA[" >Full photo</a><br />]]>
		</xsl:if>

		<xsl:if test="title">
			Photo title:<![CDATA[<b>]]><xsl:value-of select="title"/> <![CDATA[</b><br />]]>
		</xsl:if>
		<xsl:if test="photo_credit">
			Photo credit:<xsl:value-of select="photo_credit"/><![CDATA[<br />]]>
		</xsl:if>
		<xsl:if test="alt">
			alt text:<xsl:value-of select="alt"/><![CDATA[<br />]]>
		</xsl:if>
		<xsl:if test="caption">
			Photo caption:<xsl:value-of select="caption"/><![CDATA[<br />]]>
		</xsl:if>
		<xsl:if test="geo_location">
			Geo Location:<xsl:value-of select="geo_location"/><![CDATA[<br />]]>
		</xsl:if>


	</xsl:for-each>
	
	<![CDATA[</div>]]>

<![CDATA[</div>]]> <!-- end class slideshow -->

</xsl:template>

<xsl:template match="/photo">
		<xsl:if test="thumb_url">
			<![CDATA[<img src="]]><xsl:value-of select="thumb_url"/> <![CDATA[" /><br />]]>
		</xsl:if>

		<xsl:if test="url">
			<![CDATA[<a href="]]><xsl:value-of select="url"/> <![CDATA[" >Full photo</a><br />]]>
		</xsl:if>

		<xsl:if test="title">
			Photo title:<![CDATA[<b>]]><xsl:value-of select="title"/> <![CDATA[</b><br />]]>
		</xsl:if>
		<xsl:if test="photo_credit">
			Photo credit:<xsl:value-of select="photo_credit"/><![CDATA[<br />]]>
		</xsl:if>
		<xsl:if test="alt">
			alt text:<xsl:value-of select="alt"/><![CDATA[<br />]]>
		</xsl:if>
		<xsl:if test="caption">
			Photo caption:<xsl:value-of select="caption"/><![CDATA[<br />]]>
		</xsl:if>
		<xsl:if test="geo_location">
			Geo Location:<xsl:value-of select="geo_location"/><![CDATA[<br />]]>
		</xsl:if>


</xsl:template>
</xsl:stylesheet>
