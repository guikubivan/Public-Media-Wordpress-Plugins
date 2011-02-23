<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="text"/>

<!-- slideshow -->
<xsl:template match="/slideshows/slideshow">
&lt;div class="program-slideshow"&gt;

	&lt;h3&gt;<xsl:value-of select="title"/>&lt;/h3&gt;

	&lt;div class="scrollable" rel="#overlay<xsl:value-of select="ID"/>"&gt;&lt;ul class="items"&gt;
	<!-- here goes the intro slide stuff -->
	
		&lt;li class="item"&gt;
		
			&lt;a rel="slideshow-colorbox" class="colorbox" href="<xsl:value-of select="slideshow_thumb/large_url"/> " &gt;
				&lt;img alt="
				<xsl:value-of select="description"/>
				" src="<xsl:value-of select="slideshow_thumb/large_url"/>" /&gt;
				&lt;div class="slideshow-enlarge enlarge-title"&gt;&lt;/div&gt;
			&lt;/a&gt;
			
			&lt;p class="photo-number"&gt;
			<xsl:value-of select="count(//photo)"/>
			 Images&lt;/p&gt;
			
		<xsl:choose>
			<xsl:when test="original_url">
				&lt;p class="photo-credit"&gt;Photo: &lt;a href="<xsl:value-of select="original_url"/>"&gt;<xsl:value-of select="photo_credit"/>&lt;/a&gt;&lt;/p&gt;
			</xsl:when>
        	<xsl:otherwise>
				&lt;p class="photo-credit"&gt;Slideshow: <xsl:value-of select="photo_credit"/>&lt;/p&gt;
			</xsl:otherwise>
		</xsl:choose>
			&lt;div class="clear"&gt;&lt;/div&gt;
		&lt;p class="photo-caption"&gt;<xsl:value-of select="description"/>&lt;/p&gt;
	&lt;/li&gt;<!--item-->
	
	<!-- and now, the rest of the images -->
	<xsl:for-each select="photo">
	&lt;li class="item"&gt;
		<xsl:if test="url">
			&lt;a rel="slideshow-colorbox" class="colorbox" href="<xsl:value-of select="large_url"/> " &gt;
			<xsl:if test="thumb_url">
				&lt;img alt="<xsl:value-of select="alt"/>
				" src="<xsl:value-of select="large_url"/>" /&gt;	
			</xsl:if>
			&lt;div class="slideshow-enlarge enlarge-slide"&gt;&lt;/div&gt;
			&lt;/a&gt;
			
			&lt;p class="photo-number"&gt;Image <xsl:value-of select="position()"/>
			of
			<xsl:value-of select="count(//photo)"/>
			&lt;/p&gt;
			
			<xsl:choose>
			<xsl:when test="original_url">
				&lt;p class="photo-credit"&gt;Photo: &lt;a href="<xsl:value-of select="original_url"/>"&gt;<xsl:value-of select="photo_credit"/>&lt;/a&gt;&lt;/p&gt;
			</xsl:when>
        	<xsl:otherwise>
				&lt;p class="photo-credit"&gt;Photo: <xsl:value-of select="photo_credit"/>&lt;/p&gt;
			</xsl:otherwise>
		</xsl:choose>
			&lt;div class="clear"&gt;&lt;/div&gt;
			&lt;p class="photo-caption"&gt;<xsl:value-of select="caption"/>&lt;/p&gt;
		</xsl:if>

	&lt;/li&gt;<!--item-->

	</xsl:for-each>

	&lt;/ul&gt;<!--items-->
	&lt;/div&gt;<!--scrollable-->

	&lt;a class="scrollable-nav prevPage"&gt;&lt;/a&gt;
	&lt;a class="scrollable-nav nextPage"&gt;&lt;/a&gt;
	
&lt;/div&gt;<!-- program-slideshow -->





&lt;div class="the-overlay" id="overlay]]&gt;<xsl:value-of select="ID"/>" style="display:none;"&gt;

	&lt;h3&gt;<xsl:value-of select="title"/>&lt;/h3&gt;
	
	&lt;div class="scrollable"&gt;&lt;ul class="items"&gt;
	
	
	
	&lt;li&gt;&lt;a class="colorbox"&gt;&lt;img alt="<xsl:value-of select="alt"/>
				" src="<xsl:value-of select="slideshow_thumb/large_url"/>" /&gt;&lt;/a&gt;
				
				&lt;p class="photo-number"&gt;
			<xsl:value-of select="count(//photo)"/>
			Images&lt;/p&gt;
			
				<xsl:choose>
			<xsl:when test="original_url">
				&lt;p class="photo-credit"&gt;Photo: &lt;a href="<xsl:value-of select="slideshow_thumb/original_url"/>"&gt;<xsl:value-of select="photo_credit"/>&lt;/a&gt;&lt;/p&gt;
			</xsl:when>
        	<xsl:otherwise>
				&lt;p class="photo-credit"&gt;Slideshow: <xsl:value-of select="photo_credit"/>&lt;/p&gt;
			</xsl:otherwise>
		</xsl:choose>
		&lt;div class="clear"&gt;&lt;/div&gt;
		&lt;p class="photo-caption"&gt;<xsl:value-of select="description"/>&lt;/p&gt;
		&lt;/li&gt;
	
	
	
	
	<xsl:for-each select="photo">
		&lt;li&gt;&lt;a class="colorbox"&gt;&lt;img alt="
				" src="<xsl:value-of select="large_url"/>" /&gt;&lt;a&gt;
				
				&lt;p class="photo-number"&gt;Image<xsl:value-of select="position()"/>
			of
			<xsl:value-of select="count(//photo)"/>
			&lt;/p&gt;
				
				<xsl:choose>
			<xsl:when test="original_url">
				&lt;p class="photo-credit"&gt;Photo: &lt;a href="<xsl:value-of select="original_url"/>"&gt;<xsl:value-of select="photo_credit"/>&lt;/a&gt;&lt;/p&gt;
			</xsl:when>
        	<xsl:otherwise>
				&lt;p class="photo-credit"&gt;Photo: <xsl:value-of select="photo_credit"/>&lt;/p&gt;
			</xsl:otherwise>
		</xsl:choose>
		&lt;div class="clear"&gt;&lt;/div&gt;
		&lt;p class="photo-caption"&gt;<xsl:value-of select="caption"/>&lt;/p&gt;
		&lt;/li&gt;
	</xsl:for-each>
	&lt;/ul&gt;&lt;/div&gt; <!--the-images-->
	&lt;a class="prevPage"&gt;&lt;/a&gt; 
	&lt;a class="nextPage"&gt;&lt;/a&gt; 
&lt;/div&gt;<!-- the-overlay --> 

</xsl:template>







<xsl:template match="/photo">

		<xsl:if test="url">
			&lt;div class="postimage-wrapper"&gt;&lt;div class="the-image-wrapper"&gt;&lt;img class="postimage" src="<xsl:value-of select="large_url"/> " alt="
			<xsl:value-of select="alt"/>" /&gt;&lt;/div&gt;
		</xsl:if>
        <xsl:choose>
			<xsl:when test="original_url">
				&lt;p class="photo-credit"&gt;Photo: &lt;a href="<xsl:value-of select="original_url"/>"&gt;<xsl:value-of select="photo_credit"/>&lt;/a&gt;&lt;/p&gt;
			</xsl:when>
        	<xsl:otherwise>
				&lt;p class="photo-credit"&gt;Photo: <xsl:value-of select="photo_credit"/>&lt;/p&gt;
			</xsl:otherwise>
		</xsl:choose>
		&lt;div class="clear"&gt;&lt;/div&gt;
        <xsl:if test="caption">
			&lt;p class="photo-caption"&gt;<xsl:value-of select="caption"/>
		</xsl:if>
		<xsl:if test="url">
		&lt;/p&gt;&lt;/div&gt;<!--end postimage-wrapper-->
		</xsl:if>

</xsl:template>
</xsl:stylesheet>
