  // ==========================================
// SCRIPT: EXPORTAR CONSOLIDADO (TODAS LAS MATERIAS)
// ==========================================

// Listener para el botón nuevo
document.getElementById('btnExportarConsolidado').addEventListener('click', () => {
    exportarPDFConsolidado();
});

function exportarPDFConsolidado() {
    if (!window.jspdf || !window.jspdf.jsPDF) { alert("Falta la librería jsPDF"); return; }
    
    const { jsPDF } = window.jspdf;
    // 🟢 CAMBIO IMPORTANTE: 'landscape' (horizontal) para que quepan las materias
    const doc = new jsPDF('l', 'mm', 'a4'); 

    // 1. OBTENCIÓN DE DATOS (Reutilizamos las globales existentes)
    // Usamos window.datosPeriodoSeleccionado si existe (historial cargado), sino infoGrupoCompleta
    const infoHistorial = window.datosPeriodoSeleccionado || {};
    const infoGeneral = infoGrupoCompleta || {};

    const dataPDF = {
        fecha_inicio:    infoHistorial.fecha_inicio    || infoGeneral.fecha_inicio    || '',
        fecha_fin:       infoHistorial.fecha_fin       || infoGeneral.fecha_fin       || '',
        numero_trayecto: infoHistorial.numero_trayecto || infoGeneral.numero_trayecto || 'I',
        numero_periodo:  infoHistorial.numero_periodo  || infoGeneral.numero_periodo  || 'N/A',
        estado:    infoGeneral.estado    || 'YARACUY',
        municipio: infoGeneral.municipio || 'NIRGUA',
        aldea:     infoGeneral.aldea     || '"SIMON BOLIVAR"',
        seccion:   infoGeneral.seccion   || 'FIN DE SEMANA',
        vocero_ci: infoGeneral.vocero_ci || ''
    };

    // Parsear fechas (Reutilizamos la función auxiliar del otro script si está disponible, sino la definimos localmente)
    const pFecha = (f) => {
        if (!f) return { d: '', m: '', a: '' };
        const p = String(f).split('-');
        return (p.length === 3) ? { d: p[2], m: p[1], a: p[0] } : { d: '', m: '', a: '' };
    };

    const fInicio = pFecha(dataPDF.fecha_inicio);
    const fFin = pFecha(dataPDF.fecha_fin);
    const colorHeader = [204, 204, 255]; // Lila
    const colorBorder = [0, 0, 0];

    // --- 2. LOGO Y TÍTULO ---
    const imgLogo = "../../../images/logo_mision_sucre.png"; // 🔴 TU BASE64 AQUÍ
    if (imgLogo) {
        try { doc.addImage(imgLogo, 'PNG', 130, 10, 30, 30); } catch(e){}
    } else {
        doc.setFontSize(8);
        doc.text("[LOGO]", 148, 20, { align: 'center' });
    }

    doc.setFillColor(...colorHeader);
    doc.rect(14, 5, 269, 6, 'F'); // Rectángulo más ancho por ser landscape
    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    doc.setTextColor(0,0,0);
    doc.text("RESUMEN CONSOLIDADO DE CALIFICACIONES", 148, 9, { align: 'center' });

    // --- 3. TABLA ENCABEZADO (ESTILO EXACTO, ADAPTADO A LANDSCAPE) ---
    // Al ser landscape (297mm ancho), ajustamos los anchos de columnas
    doc.autoTable({
        startY: 40,
        theme: 'plain',
        styles: {
            lineColor: colorBorder,
            lineWidth: 0.1,
            fontSize: 8,
            textColor: [0,0,0],
            valign: 'middle',
            halign: 'center',
            cellPadding: 1
        },
        columnStyles: {
            0: { cellWidth: 70 }, // Estado / Materia
            1: { cellWidth: 40 }, // Municipio / Trayecto
            2: { cellWidth: 40 }, // Aldea / Periodo
            3: { cellWidth: 40 }, // Seccion
            4: { cellWidth: 25 }, // Etiqueta Lapso
            5: { cellWidth: 18 }, // Día
            6: { cellWidth: 18 }, // Mes
            7: { cellWidth: 18 }  // Año
        },
        headStyles: { fillColor: colorHeader, fontStyle: 'bold', textColor: [0,0,0] },
        head: [
            [
                { content: 'Estado' },
                { content: 'Municipio' },
                { content: 'Aldea' },
                { content: 'Lapso', colSpan: 5 } // Ajustado colspan
            ]
        ],
        body: [
            // Fila 1
            [
                { content: dataPDF.estado, rowSpan: 2, styles: { fontStyle: 'bold' } },
                { content: dataPDF.municipio, rowSpan: 2, styles: { fontStyle: 'bold' } },
                { content: dataPDF.aldea, rowSpan: 2, colSpan: 2, styles: { fontStyle: 'bold' } }, // Aldea mas ancha

                { content: 'Inicio', styles: { fillColor: colorHeader, fontStyle: 'bold' } },
                { content: fInicio.d, styles: { fontStyle: 'bold' } },
                { content: fInicio.m, styles: { fontStyle: 'bold' } },
                { content: fInicio.a, styles: { fontStyle: 'bold' } }
            ],
            // Fila 2
            [
                { content: 'Fin', styles: { fillColor: colorHeader, fontStyle: 'bold' } },
                { content: fFin.d, styles: { fontStyle: 'bold' } },
                { content: fFin.m, styles: { fontStyle: 'bold' } },
                { content: fFin.a, styles: { fontStyle: 'bold' } }
            ],
            // Fila 3
            [
                { content: 'Descripción del Reporte', styles: { fillColor: colorHeader, fontStyle: 'bold' } }, // Cambio de nombre
                { content: 'Trayecto', styles: { fillColor: colorHeader, fontStyle: 'bold' } },
                { content: 'Periodo', styles: { fillColor: colorHeader, fontStyle: 'bold' } },
                { content: 'Sección', styles: { fillColor: colorHeader, fontStyle: 'bold' } },
                { content: '', colSpan: 4, styles: { fillColor: [255,255,255], lineWidth: 0 } } // Vacio
            ],
            // Fila 4
            [
                { content: 'CONSOLIDADO DE NOTAS', styles: { fontStyle: 'bold' } },
                { content: dataPDF.numero_trayecto, styles: { fontStyle: 'bold' } },
                { content: dataPDF.numero_periodo, styles: { fontStyle: 'bold' } },
                { content: dataPDF.seccion, styles: { fontStyle: 'bold' } },
                { content: '', colSpan: 4, styles: { lineWidth: 0 } }
            ]
        ],
        margin: { left: 14, right: 14 }
    });

    // --- 4. PREPARACIÓN DE LA TABLA DE NOTAS ---
    
    // a. Construir cabeceras dinámicas
    // Fila 1: ["Nº", "Cédula", "Estudiante", ...NombresMaterias...]
    // Fila 2: ["", "", "", ...NombresProfesores...]

    const headerRow1 = [
        { content: 'Nº', rowSpan: 2, styles: { valign: 'middle' } },
        { content: 'Cédula', rowSpan: 2, styles: { valign: 'middle' } },
        { content: 'Apellidos y Nombres', rowSpan: 2, styles: { valign: 'middle' } }
    ];
    
    const headerRow2 = []; // Las celdas vacías de las primeras 3 col se manejan con rowSpan arriba

    // Recuperamos los inputs de profesor del DOM para tener los datos actuales
    const inputsProf = document.querySelectorAll('#theadMaterias input[type="text"]');

    materias.forEach((mat, index) => {
        // Fila Superior: Nombre Materia
        headerRow1.push({ 
            content: mat.nombre_materia, 
            styles: { halign: 'center', fontStyle: 'bold' } 
        });

        // Fila Inferior: Nombre Profesor
        // Intentamos sacar el profesor del input en pantalla, sino del historial
        let nombreProf = "";
        if (inputsProf[index]) {
            nombreProf = inputsProf[index].value;
        } else {
            // Fallback por si no se leyó del DOM
            const reg = historial.find(h => String(h.id_materia) === String(mat.id_materia));
            nombreProf = reg?.profesor || "";
        }

        headerRow2.push({
            content: nombreProf || "(Sin Prof.)",
            styles: { halign: 'center', fontSize: 6, fontStyle: 'italic', textColor: [50,50,50] }
        });
    });

    // b. Construir cuerpo de datos
    const bodyRows = [];
    
    estudiantes.forEach((est, i) => {
        const fila = [
            (i + 1).toString(),
            est.cedula || est.ci || "S/I",
            `${est.primer_apellido} ${est.primer_nombre}`
        ];

        // Buscar nota para cada materia
        materias.forEach(mat => {
            const reg = historial.find(h => String(h.id_estudiante) === String(est.id) && String(h.id_materia) === String(mat.id_materia));
            let nota = "";
            if (reg && reg.nota !== null && reg.nota !== undefined && reg.nota !== "") {
                nota = parseFloat(reg.nota) < 10 ? "0" + parseFloat(reg.nota) : reg.nota; // Formato 09, 15
            } else {
                nota = "-";
            }
            fila.push(nota);
        });

        bodyRows.push(fila);
    });

    // --- 5. DIBUJAR TABLA DE NOTAS ---
    let finalY = doc.lastAutoTable.finalY + 5;

    doc.autoTable({
        startY: finalY,
        head: [headerRow1, headerRow2],
        body: bodyRows,
        theme: 'grid',
        styles: {
            lineColor: [0,0,0],
            lineWidth: 0.1,
            fontSize: 8,
            textColor: [0,0,0],
            valign: 'middle',
            halign: 'center'
        },
        headStyles: {
            fillColor: colorHeader,
            textColor: [0,0,0],
            lineWidth: 0.1,
            lineColor: [0,0,0]
        },
        columnStyles: {
            2: { halign: 'left', cellWidth: 60 }, // Nombre estudiante ancho y a la izquierda
            0: { cellWidth: 10 }, // Nº
            1: { cellWidth: 25 }  // Cédula
        },
        margin: { left: 14, right: 14 }
    });

    // --- 6. FIRMAS (Misma lógica, ajustada al ancho landscape) ---
    finalY = doc.lastAutoTable.finalY + 10;
    
    // Si la tabla es muy larga y queda poco espacio, salta de página
    if (finalY > 180) { 
        doc.addPage();
        finalY = 20;
    }

    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.text("VA SIN ENMIENDAS", 148, finalY, { align: 'center' });

    const yFirmas = finalY + 25;
    doc.setLineWidth(0.5);
    doc.setFont("helvetica", "normal");

    // Ajuste de posiciones X para Landscape (Ancho ~297mm)
    // Vocero (Izq)
    doc.line(30, yFirmas, 90, yFirmas);
    doc.text("Vocera o Vocero estudiantil", 60, yFirmas + 4, { align: 'center' });
    doc.text(`C.I: ${dataPDF.vocero_ci || ''}`, 60, yFirmas + 8, { align: 'center' });

    // Coordinador (Der) - En resumen consolidado suele firmar solo coordinación y vocero, 
    // pero si quieres profesor, como son "Varios", no se suele poner firma de profesor específico.
    // Pondremos Coordinación a la derecha.
    
    doc.line(200, yFirmas, 260, yFirmas);
    doc.text("Coordinadora o Coordinador de Aldea", 230, yFirmas + 4, { align: 'center' });
    doc.text("C.I:", 230, yFirmas + 8, { align: 'center' });

    doc.save(`Acta_Completa_${dataPDF.numero_trayecto}_${dataPDF.numero_periodo}.pdf`);
}