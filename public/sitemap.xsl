<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml">
<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>XML Sitemap — GN Industrial</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #374151; margin: 0; padding: 0; background: #f8f8fa; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1 { color: #50529D; font-size: 24px; margin: 20px 0 5px; }
        p.desc { color: #6b7280; font-size: 14px; margin: 0 0 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        th { background: #50529D; color: #fff; text-align: left; padding: 10px 14px; font-size: 13px; font-weight: 600; }
        td { padding: 8px 14px; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
        tr:hover td { background: #f8f8fc; }
        td a { color: #50529D; text-decoration: none; }
        td a:hover { text-decoration: underline; }
        .count { color: #6b7280; font-size: 13px; margin: 10px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>XML Sitemap</h1>
    <p class="desc">This sitemap is generated for search engines. <a href="/" style="color:#50529D">← Back to site</a></p>

    <xsl:choose>
        <xsl:when test="sitemap:sitemapindex">
            <p class="count">Sitemaps: <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/></p>
            <table>
                <tr><th>Sitemap</th><th>Last Modified</th></tr>
                <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                    <tr>
                        <td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
                        <td><xsl:value-of select="substring(sitemap:lastmod, 1, 10)"/></td>
                    </tr>
                </xsl:for-each>
            </table>
        </xsl:when>
        <xsl:otherwise>
            <p class="count">URLs: <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></p>
            <table>
                <tr><th>URL</th><th>Priority</th><th>Change Freq</th><th>Last Modified</th></tr>
                <xsl:for-each select="sitemap:urlset/sitemap:url">
                    <tr>
                        <td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
                        <td><xsl:value-of select="sitemap:priority"/></td>
                        <td><xsl:value-of select="sitemap:changefreq"/></td>
                        <td><xsl:value-of select="substring(sitemap:lastmod, 1, 10)"/></td>
                    </tr>
                </xsl:for-each>
            </table>
        </xsl:otherwise>
    </xsl:choose>
</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
