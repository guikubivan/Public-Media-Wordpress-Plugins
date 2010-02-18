<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/slideshow">


			

				<![CDATA[<img class="program-photo" alt="]]>
				<xsl:value-of select="description"/>
				<![CDATA[" src="]]><xsl:value-of select="slideshow_thumb/medium_url"/><![CDATA[" />]]>
				
			



</xsl:template>


<xsl:template match="/photo">



			

				<![CDATA[<img class="program-photo" alt="]]>
				<xsl:value-of select="description"/>
				<![CDATA[" src="]]><xsl:value-of select="medium_url"/><![CDATA[" />]]>


</xsl:template>

</xsl:stylesheet>
