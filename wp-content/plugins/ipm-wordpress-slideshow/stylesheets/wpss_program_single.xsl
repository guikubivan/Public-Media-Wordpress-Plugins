<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<!-- slideshow -->
<xsl:template match="/slideshow">
<![CDATA[<div class="popeye" id="popeye2"> ]]>
	<![CDATA[<h3>]]><xsl:value-of select="title"/><![CDATA[</h3>]]>
	<![CDATA[<h4>]]>Click image to close<![CDATA[</h4>]]>
	<![CDATA[<ul>]]>
	<!-- here goes the intro slide stuff -->
		<![CDATA[<li>]]>
			<![CDATA[<a href="]]><xsl:value-of select="slideshow_thumb/medium_url"/> <![CDATA[" >]]>
				<![CDATA[<img alt="]]>
				<xsl:value-of select="description"/>
				<![CDATA[" src="]]><xsl:value-of select="slideshow_thumb/thumb_url"/><![CDATA[" />]]>
				<![CDATA[<span class="credit">]]><xsl:value-of select="photo_credit"/><![CDATA[</span>]]>
			<![CDATA[</a>]]>
	<![CDATA[</li>]]>
	<!-- and now, the rest of the images -->
	<xsl:for-each select="photo">
	<![CDATA[<li>]]>
		<xsl:if test="url">
			<![CDATA[<a href="]]><xsl:value-of select="medium_url"/> <![CDATA[" >]]>
			<xsl:if test="thumb_url">
				<![CDATA[<img alt="]]>
				<xsl:value-of select="caption"/>
				<![CDATA[" src="]]><xsl:value-of select="thumb_url"/><![CDATA[" />]]>
				<![CDATA[<span class="credit">]]><xsl:value-of select="photo_credit"/><![CDATA[</span>]]>
			</xsl:if>
			<![CDATA[</a>]]>
		</xsl:if>
	<![CDATA[</li>]]>
	</xsl:for-each>
	<![CDATA[</ul>]]>
<![CDATA[</div>]]> <!-- end class popeye -->
</xsl:template>

<xsl:template match="/photo">

		<xsl:if test="url">
			<![CDATA[<div class="postimage-wrapper"><img class="postimage" src="]]><xsl:value-of select="large_url"/> <![CDATA[" alt="]]>
			<xsl:value-of select="alt"/><![CDATA[" />]]>
		</xsl:if>
        <xsl:choose>
			<xsl:when test="original_url">
				<![CDATA[<p class="photo-credit">]]>Photo: <![CDATA[<a href="]]><xsl:value-of select="original_url"/><![CDATA[">]]><xsl:value-of select="photo_credit"/><![CDATA[</a>]]><![CDATA[</p>]]>
			</xsl:when>
        	<xsl:otherwise>
				<![CDATA[<p class="photo-credit">]]>Photo: <xsl:value-of select="photo_credit"/><![CDATA[</p>]]>
			</xsl:otherwise>
		</xsl:choose>
        <xsl:if test="caption">
			<![CDATA[<p class="photo-caption">]]><xsl:value-of select="caption"/>
		</xsl:if>
		<xsl:if test="url">
		<![CDATA[</p></div><!--end postimage-wrapper-->]]>
		</xsl:if>

</xsl:template>
</xsl:stylesheet>
