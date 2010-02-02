<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/nprml/list">

	<xsl:if test="(story)">
<![CDATA[<h3>NPR Story</h3>]]>
	</xsl:if>

	<xsl:for-each select="story">


<![CDATA[
	<div>

]]>
	
	<xsl:if test="link">
		<![CDATA[<a href="]]><xsl:value-of select="link"/><![CDATA[">Original link</a> ]]>
	</xsl:if>

	<xsl:if test="teaser">
		<![CDATA[<p>]]><xsl:value-of select="teaser"/><![CDATA[</p> ]]>
	</xsl:if>
	
	<xsl:if test="image">
		<xsl:for-each select="image">
			<![CDATA[<img src="]]><xsl:value-of select="@src" /><![CDATA[" alt="NPR image" />]]>
		</xsl:for-each>
	</xsl:if>
		

	<xsl:if test="audio">
		<![CDATA[<a href="]]><xsl:value-of select="//audio//mp3"/><![CDATA[">Listen</a></b><br />]]>
	</xsl:if>
<![CDATA[
	</div>
]]>
	
	

	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>
