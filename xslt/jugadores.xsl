<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    Muestra una tarjeta por cada jugador con su foto, nombre, dorsal, posicion, edad, nacionalidad y estado (activo/inactivo)
    Se priorizan las fotos de la temporada seleccionada.
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" doctype-system="about:legacy-compat"/>
    
    <!-- Parametro desde JS: equipo opcional y temporada (abierta por defecto) -->
    <xsl:param name="equipoId" select="''"/>
    <xsl:param name="temporadaId" select="/Futsal/Temporadas/Temporada[Estado='abierta'][1]/ID"/>

    <xsl:template match="/">
        <xsl:variable name="jugadorIds">
            <xsl:choose>
                <xsl:when test="$equipoId != ''">
                    <xsl:choose>
                        <xsl:when test="/Futsal/EquipoTemporada[EquipoID=$equipoId and TemporadaID=$temporadaId]/JugadorIds">
                            <xsl:value-of select="/Futsal/EquipoTemporada[EquipoID=$equipoId and TemporadaID=$temporadaId]/JugadorIds[1]"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="/Futsal/Equipos/Equipo[ID=$equipoId]/JugadorIds"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="/Futsal/Temporadas/Temporada[ID=$temporadaId]/JugadorIds"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <div class="cards-grid">
            <xsl:choose>
                <xsl:when test="normalize-space($jugadorIds) != ''">
                    <xsl:apply-templates select="/Futsal/Jugadores/Jugador[contains(concat(',', $jugadorIds, ','), concat(',', ID, ','))]">
                        <xsl:sort select="Nombre"/>
                    </xsl:apply-templates>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates select="/Futsal/Jugadores/Jugador">
                        <xsl:sort select="Nombre"/>
                    </xsl:apply-templates>
                </xsl:otherwise>
            </xsl:choose>
        </div>
    </xsl:template>

    <xsl:template match="Jugador">
        <div class="player-card">
            <xsl:variable name="myId" select="string(ID)"/>
            <xsl:variable name="fotoTemporada" select="/Futsal/Temporadas/Temporada[ID=$temporadaId]/FotosJugadores/Jugador[@id=$myId][1]"/>
            <xsl:variable name="fotoHistorica" select="/Futsal/Temporadas/Temporada/FotosJugadores/Jugador[@id=$myId][1]"/>
            <xsl:variable name="rawFoto">
                <xsl:choose>
                    <xsl:when test="$fotoTemporada and normalize-space($fotoTemporada) != ''">
                        <xsl:value-of select="$fotoTemporada"/>
                    </xsl:when>
                    <xsl:when test="$fotoHistorica and normalize-space($fotoHistorica) != ''">
                        <xsl:value-of select="$fotoHistorica"/>
                    </xsl:when>
                    <xsl:when test="Imagen and normalize-space(Imagen) != ''">
                        <xsl:value-of select="Imagen"/>
                    </xsl:when>
                    <xsl:otherwise/>
                </xsl:choose>
            </xsl:variable>
            <xsl:variable name="normalizedFoto" select="translate($rawFoto, '\\', '/')"/>
            <xsl:variable name="resolvedFoto">
                <xsl:choose>
                    <xsl:when test="starts-with($normalizedFoto, 'assets/')">
                        <xsl:value-of select="concat('data/', $normalizedFoto)"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$normalizedFoto"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <xsl:choose>
                <xsl:when test="normalize-space($resolvedFoto) != ''">
                    <img class="player-photo">
                        <xsl:attribute name="src">
                            <xsl:value-of select="$resolvedFoto"/>
                        </xsl:attribute>
                        <xsl:attribute name="alt">
                            <xsl:value-of select="Nombre"/>
                        </xsl:attribute>
                        <xsl:attribute name="onerror">
                            this.src='assets/default-player.svg'
                        </xsl:attribute>
                    </img>
                </xsl:when>
                <xsl:otherwise>
                    <img src="assets/default-player.svg" alt="Foto por defecto" class="player-photo"/>
                </xsl:otherwise>
            </xsl:choose>
            
            <div class="player-dorsal">
                <xsl:value-of select="Numero"/>
            </div>
            
            <h3 class="player-name">
                <xsl:value-of select="Nombre"/>
            </h3>
            
            <!-- Equipo al que pertenece en la temporada seleccionada -->
            <xsl:variable name="equipoID" select="/Futsal/EquipoTemporada[contains(concat(',', JugadorIds, ','), concat(',', $myId, ',')) and TemporadaID=$temporadaId]/EquipoID[1]"/>
            <xsl:variable name="teamName">
                <xsl:choose>
                    <xsl:when test="$equipoID != ''">
                        <xsl:value-of select="/Futsal/Equipos/Equipo[ID=$equipoID]/Nombre"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="/Futsal/Equipos/Equipo[contains(concat(',', JugadorIds, ','), concat(',', $myId, ','))]/Nombre"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <p class="player-team">
                <strong>Equipo:</strong>
                <xsl:choose>
                    <xsl:when test="$teamName != ''">
                        <xsl:value-of select="$teamName"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>Sin equipo</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </p>
            
            <p class="player-position">
                <xsl:value-of select="Posicion"/>
            </p>
            
            <div class="player-info">
                <p>
                    <strong>Fecha Nacimiento:</strong> <xsl:value-of select="FechaNacimiento"/>
                </p>
                <p>
                    <strong>Nacionalidad:</strong> <xsl:value-of select="Nacionalidad"/>
                </p>
                <p>
                    <strong>Estado:</strong> 
                    <xsl:choose>
                        <xsl:when test="Activo = 'true'">
                            <span class="player-active">Activo</span>
                        </xsl:when>
                        <xsl:otherwise>
                            <span class="player-inactive">Inactivo</span>
                        </xsl:otherwise>
                    </xsl:choose>
                </p>
            </div>
        </div>
    </xsl:template>
    
</xsl:stylesheet>
