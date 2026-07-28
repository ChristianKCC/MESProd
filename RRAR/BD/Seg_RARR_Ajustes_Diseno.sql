/* ============================================================================
   AJUSTES: 
   Agrega columnas opcionales que el diseño de las pantallas requiere.
   ============================================================================ */
USE TLX002MXDB;
GO

/* Tab 2 — Análisis de Protección de Maquinaria */
IF COL_LENGTH('dbo.Seg_EvaluacionRARR','CriterioGuarda') IS NULL
    ALTER TABLE dbo.Seg_EvaluacionRARR ADD CriterioGuarda VARCHAR(200) NULL;
IF COL_LENGTH('dbo.Seg_EvaluacionRARR','NivelRiesgoActual') IS NULL
    ALTER TABLE dbo.Seg_EvaluacionRARR ADD NivelRiesgoActual VARCHAR(30) NULL;
IF COL_LENGTH('dbo.Seg_EvaluacionRARR','MedidasMitigacion') IS NULL
    ALTER TABLE dbo.Seg_EvaluacionRARR ADD MedidasMitigacion VARCHAR(MAX) NULL;
GO

/* Tab 3 — Plan de Acción: columna de responsable */
IF COL_LENGTH('dbo.Seg_SeguimientoControl','Responsable') IS NULL
    ALTER TABLE dbo.Seg_SeguimientoControl ADD Responsable VARCHAR(150) NULL;
GO
