<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/nprml/list">

	<xsl:if test="(story)">
<![CDATA[
  		<ul class="headlines_list">]]>

	</xsl:if>
	<xsl:for-each select="story">


<![CDATA[

	<li class="npr_small_title">
	
]]>

<!--<xsl:if test="image">
		<xsl:for-each select="image">
			<xsl:if test="@type = 'primary'">
				<xsl:if test="@width = '200'">
					<![CDATA[<img src="]]><xsl:value-of select="@src" /><![CDATA[" alt="NPR image" />]]>
				</xsl:if>
			</xsl:if>
		</xsl:for-each>
		
	</xsl:if>
-->
	
	<xsl:if test="link">
		<![CDATA[<a href="]]><xsl:value-of select="link"/><![CDATA[">]]>
	</xsl:if>
	<xsl:if test="title">
		<xsl:value-of select="title"/><![CDATA[</a> ]]>
	</xsl:if>
		

<!--
	<xsl:if test="audio">
		<![CDATA[<a href="]]><xsl:value-of select="//audio//mp3"/><![CDATA[">Listen</a></b><br />]]>
	</xsl:if>
-->
<![CDATA[
	</li>
]]>

	</xsl:for-each>
<xsl:if test="(story)">
  <![CDATA[</ul>]]>
</xsl:if>
</xsl:template>
</xsl:stylesheet>
