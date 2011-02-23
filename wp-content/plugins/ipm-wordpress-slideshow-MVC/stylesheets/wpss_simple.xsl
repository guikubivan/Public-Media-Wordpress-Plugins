<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

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
