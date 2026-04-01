<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" omit-xml-declaration="yes"/>
    <!-- Parametros de entrada: temporada activa y jugador opcional para ficha. -->
    <xsl:param name="temporadaId"/>
    <xsl:param name="jugadorId"/>

    <!-- Normaliza valores vacios o "no disponible" para salida amigable. -->
    <xsl:template name="safe-value">
        <xsl:param name="value"/>
        <xsl:choose>
            <xsl:when test="string-length(normalize-space($value)) = 0">-</xsl:when>
            <xsl:when test="translate(normalize-space($value), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = 'no disponible'">-</xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$value"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="/">
        <!-- Nodos base para decidir entre listado general o ficha individual. -->
        <xsl:variable name="temporada" select="liga/temporadas/temporada[@id=$temporadaId]"/>
        <xsl:variable name="jugadorSeleccionado" select="$temporada/equipos/equipo/jugadores/jugador[@id=$jugadorId]"/>
        <xsl:variable name="equipoJugador" select="$temporada/equipos/equipo[jugadores/jugador[@id=$jugadorId]]"/>
        <!-- Ruta de fotos dependiente de temporada. -->
        <xsl:variable name="rutaFotos">
            <xsl:choose>
                <xsl:when test="$temporadaId = '2026-2027'">../img/Jugadores2026_2027</xsl:when>
                <xsl:otherwise>../img/Jugadores2024_2025</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <xsl:choose>
            <xsl:when test="not($temporada)">
                <article class="panel-error">
                    <p>No se encontro la temporada seleccionada en el XML.</p>
                </article>
            </xsl:when>

            <xsl:when test="string-length(normalize-space($jugadorId)) &gt; 0 and not($jugadorSeleccionado)">
                <article class="panel-error">
                    <p>No existe el jugador solicitado en la temporada activa.</p>
                    <p>
                        <a href="jugadores.php?temporada_id={$temporadaId}">Volver al listado de jugadores</a>
                    </p>
                </article>
            </xsl:when>

            <xsl:when test="string-length(normalize-space($jugadorId)) &gt; 0">
                <!-- Se calcula posicion del jugador para ayudar al fallback de imagen en JS. -->
                <xsl:variable name="ordenJugador" select="count($jugadorSeleccionado/preceding-sibling::jugador) + 1"/>
                <article class="player-basic-detail">
                    <div class="player-basic-layout">
                        <aside class="player-basic-media">
                            <img class="player-basic-photo" src="{$rutaFotos}/{$jugadorSeleccionado/foto}" alt="Foto de {$jugadorSeleccionado/nombre}" data-equipo-id="{$equipoJugador/@id}" data-orden-jugador="{$ordenJugador}"/>
                        </aside>

                        <div class="player-basic-content">
                            <h3 class="player-basic-title">
                                <xsl:value-of select="$jugadorSeleccionado/nombre"/>
                                <xsl:if test="string-length(normalize-space($jugadorSeleccionado/apellidos)) &gt; 0">
                                    <xsl:text> </xsl:text>
                                    <xsl:value-of select="$jugadorSeleccionado/apellidos"/>
                                </xsl:if>
                            </h3>

                            <div class="player-basic-grid">
                                <article class="player-basic-item">
                                    <h4>Nombre</h4>
                                    <p><xsl:call-template name="safe-value"><xsl:with-param name="value" select="$jugadorSeleccionado/nombre"/></xsl:call-template></p>
                                </article>
                                <article class="player-basic-item">
                                    <h4>Apellidos</h4>
                                    <p><xsl:call-template name="safe-value"><xsl:with-param name="value" select="$jugadorSeleccionado/apellidos"/></xsl:call-template></p>
                                </article>
                                <article class="player-basic-item">
                                    <h4>Nacimiento</h4>
                                    <p><xsl:call-template name="safe-value"><xsl:with-param name="value" select="$jugadorSeleccionado/fecha_nacimiento"/></xsl:call-template></p>
                                </article>
                                <article class="player-basic-item">
                                    <h4>Nacionalidad</h4>
                                    <p><xsl:call-template name="safe-value"><xsl:with-param name="value" select="$jugadorSeleccionado/nacionalidad"/></xsl:call-template></p>
                                </article>
                                <article class="player-basic-item">
                                    <h4>Peso</h4>
                                    <p>
                                        <xsl:choose>
                                            <xsl:when test="string-length(normalize-space($jugadorSeleccionado/peso)) = 0 or translate(normalize-space($jugadorSeleccionado/peso), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = 'no disponible'">-</xsl:when>
                                            <xsl:otherwise>
                                                <xsl:value-of select="$jugadorSeleccionado/peso"/>
                                                <xsl:text> kg</xsl:text>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </p>
                                </article>
                                <article class="player-basic-item">
                                    <h4>Altura</h4>
                                    <p>
                                        <xsl:choose>
                                            <xsl:when test="string-length(normalize-space($jugadorSeleccionado/altura)) = 0 or translate(normalize-space($jugadorSeleccionado/altura), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = 'no disponible'">-</xsl:when>
                                            <xsl:otherwise>
                                                <xsl:value-of select="$jugadorSeleccionado/altura"/>
                                                <xsl:text> m</xsl:text>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </p>
                                </article>
                                <article class="player-basic-item">
                                    <h4>Posicion</h4>
                                    <p><xsl:call-template name="safe-value"><xsl:with-param name="value" select="$jugadorSeleccionado/posicion"/></xsl:call-template></p>
                                </article>
                                <article class="player-basic-item">
                                    <h4>Equipo</h4>
                                    <p>
                                        <a href="equipo.php?id={$equipoJugador/@id}&amp;temporada_id={$temporadaId}">
                                            <xsl:value-of select="$equipoJugador/nombre"/>
                                        </a>
                                    </p>
                                </article>
                            </div>
                        </div>
                    </div>
                    <p class="player-back-link">
                        <a class="btn-outline-back" href="jugadores.php?temporada_id={$temporadaId}">Volver a jugadores</a>
                    </p>
                </article>
            </xsl:when>

            <xsl:otherwise>
                <!-- Listado completo de jugadores agrupado por equipo. -->
                <article class="cards-grid player-spotlight-grid">
                    <xsl:for-each select="$temporada/equipos/equipo">
                        <xsl:sort select="nombre" data-type="text" order="ascending"/>
                        <xsl:variable name="equipoId" select="@id"/>
                        <xsl:variable name="equipoNombre" select="nombre"/>

                        <xsl:for-each select="jugadores/jugador">
                            <figure class="player-card spotlight-card">
                                <a class="player-image-link" href="jugador.php?id={@id}&amp;temporada_id={$temporadaId}" aria-label="Ver ficha de {nombre}">
                                    <img src="{$rutaFotos}/{foto}" alt="Foto de {nombre}" data-equipo-id="{$equipoId}" data-orden-jugador="{position()}"/>
                                </a>
                                <figcaption>
                                    <strong class="player-card-name">
                                        <xsl:value-of select="nombre"/>
                                    </strong>
                                    <span class="player-card-position"><xsl:value-of select="posicion"/></span>
                                    <small>
                                        <a href="equipo.php?id={$equipoId}&amp;temporada_id={$temporadaId}">
                                            <xsl:value-of select="$equipoNombre"/>
                                        </a>
                                    </small>
                                </figcaption>
                            </figure>
                        </xsl:for-each>
                    </xsl:for-each>
                </article>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>