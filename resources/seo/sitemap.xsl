<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <xsl:output method="html" encoding="UTF-8"/>

    <xsl:template match="/">
        <html lang="vi">
            <head>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <title>Sitemap</title>
                <style>
                    body { margin: 0; background: #f8fafc; color: #1e293b; font: 15px/1.5 system-ui, sans-serif; }
                    main { width: min(1080px, calc(100% - 32px)); margin: 48px auto; }
                    h1 { margin-bottom: 6px; font-size: 30px; }
                    p { margin-top: 0; color: #64748b; }
                    .card { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 14px; background: white; box-shadow: 0 8px 24px rgba(15, 23, 42, .06); }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 13px 16px; border-bottom: 1px solid #e2e8f0; text-align: left; }
                    th { background: #f1f5f9; font-size: 13px; text-transform: uppercase; }
                    tr:last-child td { border-bottom: 0; }
                    a { color: #0369a1; overflow-wrap: anywhere; }
                    .number { width: 50px; color: #64748b; }
                    @media (max-width: 700px) { .optional { display: none; } main { margin-top: 24px; } }
                </style>
            </head>
            <body>
                <main>
                    <h1>Sitemap</h1>
                    <p>
                        Có <strong><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></strong>
                        đường dẫn công khai trên website.
                    </p>
                    <div class="card">
                        <table>
                            <thead>
                                <tr>
                                    <th class="number">#</th>
                                    <th>Đường dẫn</th>
                                    <th class="optional">Cập nhật lần cuối</th>
                                    <th class="optional">Ảnh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:for-each select="sitemap:urlset/sitemap:url">
                                    <tr>
                                        <td class="number"><xsl:value-of select="position()"/></td>
                                        <td>
                                            <a href="{sitemap:loc}">
                                                <xsl:value-of select="sitemap:loc"/>
                                            </a>
                                        </td>
                                        <td class="optional"><xsl:value-of select="sitemap:lastmod"/></td>
                                        <td class="optional"><xsl:value-of select="count(image:image)"/></td>
                                    </tr>
                                </xsl:for-each>
                            </tbody>
                        </table>
                    </div>
                </main>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
