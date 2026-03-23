<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" omit-xml-declaration="yes"/>
    <xsl:param name="temporadaId"/>

    <xsl:template match="/">
        <xsl:variable name="temporada" select="liga/temporadas/temporada[@id=$temporadaId]"/>

        <xsl:choose>
            <xsl:when test="not($temporada)">
                <article class="panel-error">
                    <p>No se encontro la temporada seleccionada en el XML.</p>
                </article>
            </xsl:when>
            <xsl:otherwise>
                <article class="table-wrap" aria-label="Tabla de clasificacion">
                    <table class="standings-table">
                        <thead>
                            <tr>
                                <th>Pos</th>
                                <th>Equipo</th>
                                <th>PJ</th>
                                <th>PG</th>
                                <th class="hide-mobile">PE</th>
                                <th class="hide-mobile">PP</th>
                                <th class="hide-mobile">GF</th>
                                <th class="hide-mobile">GC</th>
                                <th>DG</th>
                                <th>PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="$temporada/equipos/equipo">
                                <xsl:sort data-type="number" order="descending"
                                    select="count($temporada/partidos/partido[(@local=current()/@id and number(@goles_local) &gt; number(@goles_visitante)) or (@visitante=current()/@id and number(@goles_visitante) &gt; number(@goles_local))]) * 3 + count($temporada/partidos/partido[(@local=current()/@id or @visitante=current()/@id) and number(@goles_local)=number(@goles_visitante)])"/>
                                <xsl:sort data-type="number" order="descending"
                                    select="(sum($temporada/partidos/partido[@local=current()/@id]/@goles_local) + sum($temporada/partidos/partido[@visitante=current()/@id]/@goles_visitante)) - (sum($temporada/partidos/partido[@local=current()/@id]/@goles_visitante) + sum($temporada/partidos/partido[@visitante=current()/@id]/@goles_local))"/>
                                <xsl:sort data-type="number" order="descending"
                                    select="sum($temporada/partidos/partido[@local=current()/@id]/@goles_local) + sum($temporada/partidos/partido[@visitante=current()/@id]/@goles_visitante)"/>
                                <xsl:sort data-type="text" order="ascending" select="nombre"/>

                                <xsl:variable name="id" select="number(@id)"/>
                                <xsl:variable name="pj" select="count($temporada/partidos/partido[@local=$id or @visitante=$id])"/>
                                <xsl:variable name="pg" select="count($temporada/partidos/partido[(@local=$id and number(@goles_local) &gt; number(@goles_visitante)) or (@visitante=$id and number(@goles_visitante) &gt; number(@goles_local))])"/>
                                <xsl:variable name="pe" select="count($temporada/partidos/partido[(@local=$id or @visitante=$id) and number(@goles_local)=number(@goles_visitante)])"/>
                                <xsl:variable name="pp" select="count($temporada/partidos/partido[(@local=$id and number(@goles_local) &lt; number(@goles_visitante)) or (@visitante=$id and number(@goles_visitante) &lt; number(@goles_local))])"/>
                                <xsl:variable name="gf" select="sum($temporada/partidos/partido[@local=$id]/@goles_local) + sum($temporada/partidos/partido[@visitante=$id]/@goles_visitante)"/>
                                <xsl:variable name="gc" select="sum($temporada/partidos/partido[@local=$id]/@goles_visitante) + sum($temporada/partidos/partido[@visitante=$id]/@goles_local)"/>
                                <xsl:variable name="dg" select="$gf - $gc"/>
                                <xsl:variable name="pts" select="$pg * 3 + $pe"/>

                                <tr>
                                    <td><xsl:value-of select="position()"/></td>
                                    <td class="tdNombreEquipo">
                                        <a class="team-link" href="equipo.php?id={@id}">
                                            <img src="{escudo}" alt="Escudo de {nombre}"/>
                                            <span><xsl:value-of select="nombre"/></span>
                                        </a>
                                    </td>
                                    <td><xsl:value-of select="$pj"/></td>
                                    <td><xsl:value-of select="$pg"/></td>
                                    <td class="hide-mobile"><xsl:value-of select="$pe"/></td>
                                    <td class="hide-mobile"><xsl:value-of select="$pp"/></td>
                                    <td class="hide-mobile"><xsl:value-of select="$gf"/></td>
                                    <td class="hide-mobile"><xsl:value-of select="$gc"/></td>
                                    <td><xsl:value-of select="$dg"/></td>
                                    <td><strong><xsl:value-of select="$pts"/></strong></td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </article>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
