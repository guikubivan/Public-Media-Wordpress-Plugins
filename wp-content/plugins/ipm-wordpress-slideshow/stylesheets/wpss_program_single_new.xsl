<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<!-- slideshow -->
<xsl:template match="/slideshow">
<![CDATA[<div class="program-slideshow"> ]]>

	<![CDATA[<h3>]]><xsl:value-of select="title"/><![CDATA[</h3>]]>

	
	<![CDATA[<div class="scrollable"><ul class="items">]]>
	<!-- here goes the intro slide stuff -->
		<![CDATA[<li class="item">]]>
			<![CDATA[<a rel="slideshow-colorbox" class="colorbox" href="]]><xsl:value-of select="slideshow_thumb/medium_url"/> <![CDATA[" >]]>
				<![CDATA[<img alt="]]>
				<xsl:value-of select="description"/>
				<![CDATA[" src="]]><xsl:value-of select="slideshow_thumb/medium_url"/><![CDATA[" />]]>
			<![CDATA[</a>]]>
		<xsl:choose>
			<xsl:when test="original_url">
				<![CDATA[<p class="photo-credit">]]>Photo: <![CDATA[<a href="]]><xsl:value-of select="original_url"/><![CDATA[">]]><xsl:value-of select="photo_credit"/><![CDATA[</a>]]><![CDATA[</p>]]>
			</xsl:when>
        	<xsl:otherwise>
				<![CDATA[<p class="photo-credit">]]>Photo: <xsl:value-of select="photo_credit"/><![CDATA[</p>]]>
			</xsl:otherwise>
		</xsl:choose>
	<![CDATA[
	</li><!--item-->
	]]>
	<!-- and now, the rest of the images -->
	<xsl:for-each select="photo">
	<![CDATA[<li class="item">]]>
		<xsl:if test="url">
			<![CDATA[<a rel="slideshow-colorbox" class="colorbox" href="]]><xsl:value-of select="medium_url"/> <![CDATA[" >]]>
			<xsl:if test="thumb_url">
				<![CDATA[<img alt="]]>
				<![CDATA[" src="]]><xsl:value-of select="medium_url"/><![CDATA[" />]]>	
			</xsl:if>
			<![CDATA[</a>]]>
			<xsl:choose>
			<xsl:when test="original_url">
				<![CDATA[<p class="photo-credit">]]>Photo: <![CDATA[<a href="]]><xsl:value-of select="original_url"/><![CDATA[">]]><xsl:value-of select="photo_credit"/><![CDATA[</a>]]><![CDATA[</p>]]>
			</xsl:when>
        	<xsl:otherwise>
				<![CDATA[<p class="photo-credit">]]>Photo: <xsl:value-of select="photo_credit"/><![CDATA[</p>]]>
			</xsl:otherwise>
		</xsl:choose>
			<![CDATA[<p>]]><xsl:value-of select="caption"/><![CDATA[</p>]]>
		</xsl:if>
	<![CDATA[
	</li><!--item-->
	]]>
	</xsl:for-each>
	<![CDATA[
	</ul><!--items-->
	</div><!--scrollable-->
	]]>
	<![CDATA[<a class="prevPage">&laquo;</a>]]>
	<![CDATA[<a class="nextPage">&raquo;</a>]]>
	
<![CDATA[
</div><!-- program-slideshow -->
]]>
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
