<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/nprml/list">

	<xsl:if test="(story)">
<![CDATA[
  		<div class="topstory">]]>

	</xsl:if>
	<xsl:for-each select="story">


<![CDATA[

	<p>
	
]]>


	
	<xsl:if test="link">
		<![CDATA[<h3 class="npr_headline_lg"><a href="]]><xsl:value-of select="link"/><![CDATA[">]]>
	</xsl:if>
	<xsl:if test="title">
		<xsl:value-of select="title"/><![CDATA[</a></h3><p> ]]>
	</xsl:if>
    <xsl:if test="image">
		<xsl:for-each select="image">
			<xsl:if test="@type = 'primary'">
				<xsl:if test="@width = '200'">
					<![CDATA[<img class="npr_thumb left" src="]]><xsl:value-of select="@src" /><![CDATA[" alt="NPR image" />]]>
				</xsl:if>
			</xsl:if>
		</xsl:for-each>
		
	</xsl:if>
	<xsl:if test="teaser">
		<xsl:value-of select="teaser"/><![CDATA[</p>]]>
	</xsl:if>
		

<!--
	<xsl:if test="audio">
		<![CDATA[<a href="]]><xsl:value-of select="//audio//mp3"/><![CDATA[">Listen</a></b><br />]]>
	</xsl:if>
-->
<![CDATA[
	</p></p>
]]>
		
	</xsl:for-each>
<xsl:if test="(story)">
  <![CDATA[</div>]]>
</xsl:if>
</xsl:template>
</xsl:stylesheet>
