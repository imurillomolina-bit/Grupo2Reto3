<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" omit-xml-declaration="yes"/>
    <xsl:param name="temporadaId"/>
    <xsl:param name="equipoId"/>

    <xsl:template match="/">
        <xsl:variable name="temporada" select="liga/temporadas/temporada[@id=$temporadaId]"/>
        <xsl:variable name="equipoSeleccionado" select="$temporada/equipos/equipo[@id=$equipoId]"/>

        <xsl:choose>
            <xsl:when test="not($temporada)">
                <article class="panel-error">
                    <h2>Error</h2>
                    <p>No se encontro la temporada seleccionada en el XML.</p>
                </article>
            </xsl:when>

            <xsl:when test="string-length(normalize-space($equipoId)) &gt; 0 and not($equipoSeleccionado)">
                <article class="panel-error">
                    <h2>Error</h2>
                    <p>No existe el equipo solicitado en la temporada activa.</p>
                    <p>
                        <a href="equipo.php?temporada_id={$temporadaId}">Ver listado de equipos</a>
                    </p>
                </article>
            </xsl:when>

            <xsl:when test="string-length(normalize-space($equipoId)) &gt; 0">
                <article class="team-headline">
                    <img class="team-shield-large" src="{$equipoSeleccionado/escudo}" alt="Escudo de {$equipoSeleccionado/nombre}"/>
                    <div>
                        <p class="meta">Temporada: <xsl:value-of select="$temporada/@nombre"/></p>
                        <h2><xsl:value-of select="$equipoSeleccionado/nombre"/></h2>
                        <p><xsl:value-of select="$equipoSeleccionado/descripcion"/></p>
                        <p><strong>Estadio: </strong> <xsl:value-of select="$equipoSeleccionado/estadio"/></p>
                        <p><strong>Ciudad: </strong> <xsl:value-of select="$equipoSeleccionado/ciudad"/></p>
                        <p>
                            <a href="partidos.php?equipo_id={$equipoSeleccionado/@id}">Ver partidos de este equipo</a>
                        </p>
                        <p>
                            <a href="equipo.php?temporada_id={$temporadaId}">Volver al listado</a>
                        </p>
                    </div>
                </article>

                <article>
                    <h3>Plantilla de jugadores</h3>
                    <div class="player-grid">
                        <xsl:for-each select="$equipoSeleccionado/jugadores/jugador">
                            <figure class="player-card">
                                <img src="../img/Jugadores2024_2025/J{$equipoSeleccionado/@id}00000{orden}.png" alt="Foto de {nombre}"/>
                                <figcaption>
                                    <strong><xsl:value-of select="nombre"/></strong>
                                    <span><xsl:value-of select="posicion"/></span>
                                </figcaption>
                            </figure>
                        </xsl:for-each>
                    </div>
                </article>
            </xsl:when>

            <xsl:otherwise>
                <article class="cards-grid team-summary-grid">
                    <xsl:for-each select="$temporada/equipos/equipo">
                        <a class="info-card team-summary-card" href="equipo.php?id={@id}&amp;temporada_id={$temporadaId}">
                            <img src="{escudo}" alt="Escudo de {nombre}"/>
                            <div>
                                <h3><xsl:value-of select="nombre"/></h3>
                                <p><xsl:value-of select="descripcion"/></p>
                                <span>
                                    <xsl:value-of select="ciudad"/>
                                    <xsl:text> · </xsl:text>
                                    <xsl:value-of select="estadio"/>
                                    <xsl:text> · </xsl:text>
                                    <xsl:value-of select="count(jugadores/jugador)"/>
                                    <xsl:text> jugadores</xsl:text>
                                </span>
                            </div>
                        </a>
                    </xsl:for-each>
                </article>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
