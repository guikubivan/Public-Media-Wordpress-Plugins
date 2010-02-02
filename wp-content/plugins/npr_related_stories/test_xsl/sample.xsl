<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>
    
    <xsl:output method="html"/>
    <xsl:template match="/">
      <html>
        <head><title><xsl:value-of select="title"/></title></head>
        <body>
          <xsl:apply-templates/>
        </body>
      </html>
    </xsl:template>
    
    <xsl:template match="article/title">
      <h1><xsl:value-of select="."/></h1>
    </xsl:template>
    
    <xsl:template match="section">
        <xsl:apply-templates/>
    </xsl:template>
        
        <!-- Formatting for JUST section titles -->
        <xsl:template match="section/title">
          <h2><xsl:value-of select="."/></h2>
        </xsl:template>
    
    <xsl:template match="para">
      <P><xsl:apply-templates/></P>
    </xsl:template>
</xsl:stylesheet> 
