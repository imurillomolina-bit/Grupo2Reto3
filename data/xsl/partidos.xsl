<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" omit-xml-declaration="yes"/>
    <!-- Parametros enviados desde la vista: temporada y fecha/jornada activa. -->
    <xsl:param name="temporadaId"/>
    <xsl:param name="fechaSeleccionada"/>

    <xsl:template match="/">
        <!-- Nodo de temporada sobre el que se aplica el filtro de fecha. -->
        <xsl:variable name="temporada" select="liga/temporadas/temporada[@id=$temporadaId]"/>

        <xsl:choose>
            <xsl:when test="not($temporada)">
                <article class="panel-error">
                    <p>No se encontro la temporada seleccionada en el XML.</p>
                </article>
            </xsl:when>

            <xsl:otherwise>
                <article class="matches-wrap" aria-label="Listado de partidos">
                    <table class="matches-table">
                        <thead>
                            <tr>
                                <th class="team-name-col">Local</th>
                                <th class="team-name-col">Visitante</th>
                                <th>Marcador</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:choose>
                                <!-- Mensaje amigable cuando la jornada no tiene partidos. -->
                                <xsl:when test="count($temporada/partidos/partido[@fecha=$fechaSeleccionada]) = 0">
                                    <tr>
                                        <td colspan="4">No hay partidos disponibles para este filtro.</td>
                                    </tr>
                                </xsl:when>
                                <xsl:otherwise>
                                    <!-- Render fila por partido para la fecha seleccionada. -->
                                    <xsl:for-each select="$temporada/partidos/partido[@fecha=$fechaSeleccionada]">
                                        <tr>
                                            <td class="team-name-col">
                                                <xsl:value-of select="$temporada/equipos/equipo[@id=current()/@local]/nombre"/>
                                            </td>
                                            <td class="team-name-col">
                                                <xsl:value-of select="$temporada/equipos/equipo[@id=current()/@visitante]/nombre"/>
                                            </td>
                                            <td>
                                                <strong>
                                                    <xsl:value-of select="@goles_local"/>
                                                    <xsl:text> - </xsl:text>
                                                    <xsl:value-of select="@goles_visitante"/>
                                                </strong>
                                            </td>
                                            <td><xsl:value-of select="@fecha"/></td>
                                        </tr>
                                    </xsl:for-each>
                                </xsl:otherwise>
                            </xsl:choose>
                        </tbody>
                    </table>
                </article>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>