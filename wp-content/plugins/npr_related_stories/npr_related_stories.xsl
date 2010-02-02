<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/nprml/list">

	<xsl:if test="(story)">
<![CDATA[
<h3 class="sidebar_header">NPR Related Stories</h3>
  		<div class="entireplaylist">]]>

	</xsl:if>
	<xsl:for-each select="story">


<![CDATA[

	<div class="playlist-item-title">
	<img class="right-arrow" src="http://wfiu.org/wp-content/plugins/wfiu_playlist/images/right-arrow.png" />
	<span><b>
	
]]>
	
	<xsl:if test="link">
		<![CDATA[<a href="]]><xsl:value-of select="link"/><![CDATA[">]]>
	</xsl:if>
	<xsl:if test="title">
		<xsl:value-of select="title"/><![CDATA[</a> ]]>
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
	</span>
]]>
	
	
<![CDATA[
	</div>
]]>	
	</xsl:for-each>
<xsl:if test="(story)">
  <![CDATA[</div>]]>
</xsl:if>
</xsl:template>
</xsl:stylesheet>
