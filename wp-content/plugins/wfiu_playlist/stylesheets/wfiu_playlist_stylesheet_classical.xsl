<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/post">
<![CDATA[
<h3 class="sidebar_header" style="margin-bottom:5px !important;">Music on This Episode</h3>
<p><strong>Your Amazon.com Purchase Helps Support this Program. <a href="#TB_inline?height=350&width=500&inlineId=about_amazon" title="Support WFIU by Shopping Amazon.com" class="thickbox">How?</a></strong></p>
  		<div class="entireplaylist">]]>


	<xsl:for-each select="item">


<![CDATA[

	<div class="playlist-item-title">
	<img class="right-arrow" src="http://wfiu.org/wp-content/plugins/wfiu_playlist/images/right-arrow.png" />
	<img class="down-arrow" src="http://wfiu.org/wp-content/plugins/wfiu_playlist/images/down-arrow.png" />
	<div class="additional-info">
	
]]>
	
	<xsl:if test="composer">
		<![CDATA[<b>]]><xsl:value-of select="composer"/> <![CDATA[:</b><br />]]>
		</xsl:if>
		<xsl:if test="title">
			<xsl:value-of select="title"/>
		</xsl:if>
		
<![CDATA[
	</div>
]]>
	
	<xsl:if test="asin">
<![CDATA[
<div class="amazonbuttonwrapper" id="classical">
]]>
			<![CDATA[<a target="_blank" href="http://www.amazon.com/gp/product/]]>
			<xsl:value-of select="asin"/>
			<![CDATA[?ie=UTF8&amp;tag=wfipubradfroi-20">]]>
			<![CDATA[<img src="http://wfiu.org/wp-content/plugins/wfiu_playlist/images/amazon-button-verysmall.png" alt="Amazon button" /></a>]]> <![CDATA[ ]]>	
<![CDATA[
	</div>
]]>
</xsl:if>
	
	<![CDATA[
	</div>
]]>
<![CDATA[
<div class="togglebox">
	<div class="infobox">
]]>
		<xsl:if test="asin">
		<![CDATA[ <img src="http://images.amazon.com/images/P/]]><xsl:value-of select="asin"/><![CDATA[.01_SL75_.jpg" alt="album cover" style="height: 50px;float:right;border: 1px solid #999;padding:1px;" /><br /> ]]>
		</xsl:if>
		
		<xsl:if test="artist">
			<![CDATA[ <b> ]]><xsl:value-of select="artist"/> <![CDATA[ </b><br /> ]]>
		</xsl:if>


		<xsl:if test="label">
			<xsl:value-of select="label"/>
		</xsl:if>

		<xsl:if test="release_year">
			(<xsl:value-of select="release_year"/>) <![CDATA[ <br /> ]]>
		</xsl:if>
		<xsl:if test="notes">
			Notes: <xsl:value-of select="notes"/> <![CDATA[ <br /> ]]>
		</xsl:if>
<![CDATA[		
	</div>	
]]>		
<![CDATA[
	</div>
]]>	
	</xsl:for-each>
  <![CDATA[</div>]]>
</xsl:template>
</xsl:stylesheet>
