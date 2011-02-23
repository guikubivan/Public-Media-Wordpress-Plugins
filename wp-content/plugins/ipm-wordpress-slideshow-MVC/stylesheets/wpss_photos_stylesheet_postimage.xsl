<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/slideshow">


			<![CDATA[<a href="]]><xsl:value-of select="slideshow_thumb/medium_url"/> <![CDATA[" >]]>
			

				<![CDATA[<img class="postimage" alt="]]>
				<xsl:value-of select="description"/>
				<![CDATA[" src="]]><xsl:value-of select="slideshow_thumb/thumb_url"/><![CDATA[" />]]>
				
			
			<![CDATA[</a>]]>


</xsl:template>


<xsl:template match="/photo">


			<![CDATA[<a href="]]><xsl:value-of select="medium_url"/> <![CDATA[" >]]>
			

				<![CDATA[<img class="postimage" alt="]]>
				<xsl:value-of select="description"/>
				<![CDATA[" src="]]><xsl:value-of select="thumb_url"/><![CDATA[" />]]>
				
			
			<![CDATA[</a>]]>


</xsl:template>

</xsl:stylesheet>
