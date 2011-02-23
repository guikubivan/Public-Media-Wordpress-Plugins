<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/slideshows/slideshow">


			
				<![CDATA[<span class="program-photo-wrapper">]]>
				<![CDATA[<img class="program-photo" alt="]]>
				<xsl:value-of select="description"/>
				<![CDATA[" src="]]><xsl:value-of select="slideshow_thumb/thumb_url"/><![CDATA[" />]]>
				<![CDATA[</span>]]>
				
			



</xsl:template>


<xsl:template match="/photo">



			
				<![CDATA[<span class="program-photo-wrapper">]]>
				<![CDATA[<img class="program-photo" alt="]]>
				<xsl:value-of select="description"/>
				<![CDATA[" src="]]><xsl:value-of select="thumb_url"/><![CDATA[" />]]>
				<![CDATA[</span>]]>

</xsl:template>

</xsl:stylesheet>
