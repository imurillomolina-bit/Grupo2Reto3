<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes"/>

    <!-- Temporada activa por defecto -->
    <xsl:param name="temporadaId" select="/Futsal/Temporadas/Temporada[Estado='abierta'][1]/ID"/>

    <xsl:template match="/Futsal">
        <xsl:variable name="equipoIdsSeleccionados" select="/Futsal/Temporadas/Temporada[ID=$temporadaId][1]/EquipoIds"/>
        <xsl:variable name="equipoIdsStr" select="concat(',', $equipoIdsSeleccionados, ',')"/>
        <xsl:variable name="partidosTemporada" select="/Futsal/Partidos/Partido[TemporadaID = $temporadaId]"/>

        <div class="table-container">
            <table>
                <!-- Encabezado de la tabla -->
                <thead>
                    <tr>
                        <th>Posición</th>
                        <th>Equipo</th>
                        <th>PJ</th>
                        <th>PG</th>
                        <th>PE</th>
                        <th>PP</th>
                        <th>GF</th>
                        <th>GC</th>
                        <th>DG</th>
                        <th>Puntos</th>
                    </tr>
                </thead>
                
                <!-- Cuerpo de la tabla -->
                <tbody>
                    <xsl:for-each select="Equipos/Equipo[contains($equipoIdsStr, concat(',', ID, ','))]">
                        <!-- Ordenar por puntos, diferencia de goles y goles a favor -->
                        <xsl:sort select="(count($partidosTemporada[(EquipoID1=current()/ID or EquipoID2=current()/ID) and Estado='jugado' and ((EquipoID1=current()/ID and GolesEquipo1 &gt; GolesEquipo2) or (EquipoID2=current()/ID and GolesEquipo2 &gt; GolesEquipo1))]) * 3) + count($partidosTemporada[(EquipoID1=current()/ID or EquipoID2=current()/ID) and Estado='jugado' and GolesEquipo1 = GolesEquipo2])" data-type="number" order="descending"/>
                        <xsl:variable name="equipoID" select="ID"/>
                        <xsl:variable name="partidos" select="$partidosTemporada[(EquipoID1=$equipoID or EquipoID2=$equipoID) and Estado='jugado']"/>
                        
                        <!-- Partidos jugados -->
                        <xsl:variable name="PJ" select="count($partidos)"/>
                        
                        <!-- Calcular victorias -->
                        <xsl:variable name="PG" select="count($partidos[(EquipoID1=$equipoID and GolesEquipo1 &gt; GolesEquipo2) or (EquipoID2=$equipoID and GolesEquipo2 &gt; GolesEquipo1)])"/>
                        
                        <!-- Calcular empates -->
                        <xsl:variable name="PE" select="count($partidos[GolesEquipo1 = GolesEquipo2])"/>
                        
                        <!-- Calcular derrotas -->
                        <xsl:variable name="PP" select="count($partidos[(EquipoID1=$equipoID and GolesEquipo1 &lt; GolesEquipo2) or (EquipoID2=$equipoID and GolesEquipo2 &lt; GolesEquipo1)])"/>
                        
                        <!-- Calcular goles a favor -->
                        <xsl:variable name="GF">
                            <xsl:call-template name="calcular-goles-favor">
                                <xsl:with-param name="equipoID" select="$equipoID"/>
                                <xsl:with-param name="partidos" select="$partidos"/>
                            </xsl:call-template>
                        </xsl:variable>
                        
                        <!-- Calcular goles en contra -->
                        <xsl:variable name="GC">
                            <xsl:call-template name="calcular-goles-contra">
                                <xsl:with-param name="equipoID" select="$equipoID"/>
                                <xsl:with-param name="partidos" select="$partidos"/>
                            </xsl:call-template>
                        </xsl:variable>
                        
                        <!-- Diferencia de goles -->
                        <xsl:variable name="DG" select="$GF - $GC"/>
                        
                        <!-- Puntos (victoria = 3, empate = 1) -->
                        <xsl:variable name="puntos" select="($PG * 3) + $PE"/>
                        
                        <!-- Crear fila de la tabla -->
                        <tr>
                            <td><xsl:value-of select="position()"/></td>
                            <td><strong><xsl:value-of select="Nombre"/></strong></td>
                            <td><xsl:value-of select="$PJ"/></td>
                            <td><xsl:value-of select="$PG"/></td>
                            <td><xsl:value-of select="$PE"/></td>
                            <td><xsl:value-of select="$PP"/></td>
                            <td><xsl:value-of select="$GF"/></td>
                            <td><xsl:value-of select="$GC"/></td>
                            <td>
                                <xsl:choose>
                                    <xsl:when test="$DG &gt; 0">+<xsl:value-of select="$DG"/></xsl:when>
                                    <xsl:otherwise><xsl:value-of select="$DG"/></xsl:otherwise>
                                </xsl:choose>
                            </td>
                            <td><strong><xsl:value-of select="$puntos"/></strong></td>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </div>
        
        <!-- Leyenda de la tabla -->
        <div style="margin-top: 2rem; padding: 1.5rem; background-color: var(--card-bg); border-radius: 8px; box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1rem;">Leyenda</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                <div><strong>PJ:</strong> Partidos Jugados</div>
                <div><strong>PG:</strong> Partidos Ganados</div>
                <div><strong>PE:</strong> Partidos Empatados</div>
                <div><strong>PP:</strong> Partidos Perdidos</div>
                <div><strong>GF:</strong> Goles a Favor</div>
                <div><strong>GC:</strong> Goles en Contra</div>
                <div><strong>DG:</strong> Diferencia de Goles</div>
            </div>
        </div>
    </xsl:template>
    
    <!-- Template para calcular goles a favor -->
    <xsl:template name="calcular-goles-favor">
        <xsl:param name="equipoID"/>
        <xsl:param name="partidos"/>
        
        <xsl:variable name="goles-local" select="sum($partidos[EquipoID1=$equipoID]/GolesEquipo1)"/>
        <xsl:variable name="goles-visitante" select="sum($partidos[EquipoID2=$equipoID]/GolesEquipo2)"/>
        
        <xsl:value-of select="$goles-local + $goles-visitante"/>
    </xsl:template>
    
    <!-- Template para calcular goles en contra -->
    <xsl:template name="calcular-goles-contra">
        <xsl:param name="equipoID"/>
        <xsl:param name="partidos"/>
        
        <xsl:variable name="goles-contra-local" select="sum($partidos[EquipoID1=$equipoID]/GolesEquipo2)"/>
        <xsl:variable name="goles-contra-visitante" select="sum($partidos[EquipoID2=$equipoID]/GolesEquipo1)"/>
        
        <xsl:value-of select="$goles-contra-local + $goles-contra-visitante"/>
    </xsl:template>
    
</xsl:stylesheet>