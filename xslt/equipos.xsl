<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    XSLT para mostrar las tarjetas de equipos con la info basica de cada equipo
    Se filtra por la temporada activa (o la indicada por parametro) para usar
    los escudos correspondientes.
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" doctype-system="about:legacy-compat"/>

    <xsl:param name="temporadaId" select="/Futsal/Temporadas/Temporada[Estado='abierta'][1]/ID"/>

    <xsl:template match="/">
        <xsl:variable name="equipoIdsSeleccionados" select="/Futsal/Temporadas/Temporada[ID=$temporadaId][1]/EquipoIds"/>
        <xsl:variable name="equipoIdsStr" select="concat(',', $equipoIdsSeleccionados, ',')"/>
        <div class="cards-grid">
            <xsl:apply-templates select="/Futsal/Equipos/Equipo[contains($equipoIdsStr, concat(',', ID, ','))]"/>
        </div>
    </xsl:template>
    
    <!-- Template que genera una tarjeta por cada equipo del XML -->
    <xsl:template match="Equipo">
        <div class="team-card">
            <!-- Buscar el escudo del equipo en la temporada seleccionada -->
            <xsl:variable name="myId" select="string(ID)"/>
            <xsl:variable name="escudoTemporada" select="/Futsal/Temporadas/Temporada[ID=$temporadaId]/EscudosEquipos/Equipo[@id=$myId][1]"/>
            <xsl:variable name="escudoEquipo" select="/Futsal/Temporadas/Temporada/EscudosEquipos/Equipo[@id=$myId][1]"/>
            <xsl:variable name="escudoPropio" select="Escudo"/>
            <xsl:variable name="rawEscudo">
                <xsl:choose>
                    <xsl:when test="$escudoTemporada and normalize-space($escudoTemporada) != ''">
                        <xsl:value-of select="$escudoTemporada"/>
                    </xsl:when>
                    <xsl:when test="$escudoEquipo and normalize-space($escudoEquipo) != ''">
                        <xsl:value-of select="$escudoEquipo"/>
                    </xsl:when>
                    <xsl:when test="$escudoPropio and normalize-space($escudoPropio) != ''">
                        <xsl:value-of select="$escudoPropio"/>
                    </xsl:when>
                    <xsl:otherwise/>
                </xsl:choose>
            </xsl:variable>
            <xsl:variable name="normalizedEscudo" select="translate($rawEscudo, '\\', '/')"/>
            <xsl:variable name="resolvedEscudo">
                <xsl:choose>
                    <xsl:when test="starts-with($normalizedEscudo, 'assets/')">
                        <xsl:value-of select="concat('data/', $normalizedEscudo)"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$normalizedEscudo"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <xsl:choose>
                <xsl:when test="normalize-space($resolvedEscudo) != ''">
                    <img class="team-logo">
                        <xsl:attribute name="src">
                            <xsl:value-of select="$resolvedEscudo"/>
                        </xsl:attribute>
                        <xsl:attribute name="alt">
                            Escudo <xsl:value-of select="Nombre"/>
                        </xsl:attribute>
                        <xsl:attribute name="onerror">
                            this.src='assets/default-team.svg'
                        </xsl:attribute>
                    </img>
                </xsl:when>
                <xsl:otherwise>
                    <img src="assets/default-team.svg" alt="Escudo por defecto" class="team-logo"/>
                </xsl:otherwise>
            </xsl:choose>
            
            <h3 class="team-name">
                <xsl:value-of select="Nombre"/>
            </h3>
            
            <p class="team-city">
                <xsl:value-of select="Pabellon"/>
            </p>
            <p class="team-country">
                <xsl:value-of select="Pais"/>
            </p>
            
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                <p style="color: #666; font-size: 0.9rem;">
                    <strong>ID del Equipo:</strong> <xsl:value-of select="ID"/>
                </p>
            </div>
            
            <!-- El data-equipo-id lo usamos luego en JS para filtrar jugadores -->
            <button class="ver-jugadores-btn">
                <xsl:attribute name="data-equipo-id">
                    <xsl:value-of select="ID"/>
                </xsl:attribute>
                Ver Jugadores
            </button>
        </div>
    </xsl:template>
    
</xsl:stylesheet>