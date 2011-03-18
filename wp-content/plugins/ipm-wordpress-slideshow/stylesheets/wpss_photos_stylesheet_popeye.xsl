<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>



<!-- slideshow -->

<xsl:template match="/slideshow">

<![CDATA[<div class="popeye" id="popeye2"> ]]>
	
	<![CDATA[<h3>]]><xsl:value-of select="title"/><![CDATA[</h3>]]>
	
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




<!-- single photo -->

<xsl:template match="/photo">

<![CDATA[<div class="popeye" id="popeye2"> ]]>

<![CDATA[<h3>]]><xsl:value-of select="title"/><![CDATA[</h3>]]>
	
	<![CDATA[<ul>]]>
	
	
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

	<![CDATA[</ul>]]>

<![CDATA[</div>]]> <!-- end class popeye -->

</xsl:template>

</xsl:stylesheet>
