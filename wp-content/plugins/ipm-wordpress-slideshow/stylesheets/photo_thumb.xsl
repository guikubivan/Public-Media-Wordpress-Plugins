<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<xsl:template match="/photo">
	<xsl:if test="url">
		<![CDATA[<div style='float:left;background: #000;padding: 5px;border:2px solid grey;color:#FAF6BB;width:150px;text-align:center;margin: 0 10px;'  class="postimage-wrapper"><img class="postimage" src="]]><xsl:value-of select="thumb_url"/> <![CDATA[" alt="]]>
        		<xsl:value-of select="alt"/><![CDATA["]]>
            <![CDATA[title="]]>
		        <xsl:value-of select="title"/><![CDATA[" />]]>
         		<xsl:if test="caption">
			<![CDATA[<p style='font-size:75%;margin:0' class="photo-caption">]]><xsl:value-of select="caption"/><![CDATA[</p>]]>
		</xsl:if>
		<![CDATA[</div>]]>
	</xsl:if>
</xsl:template>
</xsl:stylesheet>
