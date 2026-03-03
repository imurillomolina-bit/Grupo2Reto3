<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    XSLT para mostrar el calendario de partidos
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes"/>

    <!-- Temporada activa por defecto -->
    <xsl:param name="temporadaId" select="/Futsal/Temporadas/Temporada[Estado='abierta'][1]/ID"/>

    <xsl:template match="/">
        <xsl:variable name="partidosTemporada" select="/Futsal/Partidos/Partido[TemporadaID=$temporadaId]"/>
        <div class="matches-list">
            <h2>Calendario de Partidos</h2>
            <xsl:for-each select="$partidosTemporada">
                <xsl:sort select="Fecha"/>
                <div class="match-item">
                    <div class="match-info">
                        <span class="match-date"><xsl:value-of select="Fecha"/></span>
                        <span class="match-status">(<xsl:value-of select="Estado"/>)</span>
                    </div>
                    <div class="match-teams">
                        <!-- 
                            En el XML solo guardamos el ID del equipo, no el nombre
                            Asi que hacemos una consulta para obtener el nombre real
                        -->
                        <div class="team-left">
                            <xsl:variable name="id1" select="EquipoID1"/>
                            <xsl:value-of select="/Futsal/Equipos/Equipo[ID=$id1]/Nombre"/>
                        </div>
                        <div class="match-score">
                            <span class="score-number score-left"><xsl:value-of select="GolesEquipo1"/></span>
                            <span class="score-sep">-</span>
                            <span class="score-number score-right"><xsl:value-of select="GolesEquipo2"/></span>
                        </div>
                        <div class="team-right">
                            <xsl:variable name="id2" select="EquipoID2"/>
                            <xsl:value-of select="/Futsal/Equipos/Equipo[ID=$id2]/Nombre"/>
                        </div>
                    </div>
                    <div class="match-meta">
                        <xsl:text>Jornada: </xsl:text><xsl:value-of select="JornadaID"/>
                        <xsl:text> — Temporada: </xsl:text><xsl:value-of select="TemporadaID"/>
                    </div>
                </div>
            </xsl:for-each>
        </div>
    </xsl:template>
</xsl:stylesheet>
