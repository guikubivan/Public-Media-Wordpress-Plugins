<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

	<xsl:template match="/photo">
		<xsl:value-of select="medium_url"/>
	</xsl:template>
	
	<xsl:template match="/slideshow">
		<xsl:value-of select="slideshow_thumb/medium_url"/>
	</xsl:template>



</xsl:stylesheet>
