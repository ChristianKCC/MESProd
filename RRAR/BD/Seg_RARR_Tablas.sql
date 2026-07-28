/* ============================================================================
   SISTEMA DE ANÁLISIS Y REPORTE RARR (Risk Assessment)
   Base de datos : TLX011MXDB
   Nomenclatura  : Seg_(NombreTabla)
   Empleados     : dbo.ri_cat_empleados  (columna de referencia a no_emp)
   ============================================================================ */
USE TLX002MXDB;
GO

/* ============================================================================
   1. CATÁLOGOS
   ============================================================================ */

-- Categorías de peligro (Tab 1, punto 3)
IF OBJECT_ID('dbo.Seg_CatCategoriaPeligro','U') IS NULL
CREATE TABLE dbo.Seg_CatCategoriaPeligro (
    IdCategoria     INT IDENTITY(1,1) PRIMARY KEY,
    Descripcion     VARCHAR(150) NOT NULL,
    Activo          BIT NOT NULL DEFAULT 1
);
GO

-- Severidad (Tab 1, punto 5)
IF OBJECT_ID('dbo.Seg_CatSeveridad','U') IS NULL
CREATE TABLE dbo.Seg_CatSeveridad (
    IdSeveridad     INT IDENTITY(1,1) PRIMARY KEY,
    Descripcion     VARCHAR(100) NOT NULL,
    Valor           INT NOT NULL,            -- peso para el cálculo de riesgo
    Activo          BIT NOT NULL DEFAULT 1
);
GO

-- Probabilidad (Tab 1, punto 6)
IF OBJECT_ID('dbo.Seg_CatProbabilidad','U') IS NULL
CREATE TABLE dbo.Seg_CatProbabilidad (
    IdProbabilidad  INT IDENTITY(1,1) PRIMARY KEY,
    Descripcion     VARCHAR(100) NOT NULL,
    Valor           INT NOT NULL,
    Activo          BIT NOT NULL DEFAULT 1
);
GO

-- Frecuencia (Tab 1, punto 7)
IF OBJECT_ID('dbo.Seg_CatFrecuencia','U') IS NULL
CREATE TABLE dbo.Seg_CatFrecuencia (
    IdFrecuencia    INT IDENTITY(1,1) PRIMARY KEY,
    Descripcion     VARCHAR(100) NOT NULL,
    Valor           INT NOT NULL,
    Activo          BIT NOT NULL DEFAULT 1
);
GO

-- Estatus para acciones/seguimientos (Tab 3, punto 7)
IF OBJECT_ID('dbo.Seg_CatEstatus','U') IS NULL
CREATE TABLE dbo.Seg_CatEstatus (
    IdEstatus       INT IDENTITY(1,1) PRIMARY KEY,
    Descripcion     VARCHAR(50) NOT NULL,
    Activo          BIT NOT NULL DEFAULT 1
);
GO

-- Tipo de control / jerarquía de control (Tab 2 punto 5 y Tab 3 punto 5)
IF OBJECT_ID('dbo.Seg_CatTipoControl','U') IS NULL
CREATE TABLE dbo.Seg_CatTipoControl (
    IdTipoControl   INT IDENTITY(1,1) PRIMARY KEY,
    Descripcion     VARCHAR(100) NOT NULL,
    Activo          BIT NOT NULL DEFAULT 1
);
GO

-- Prioridad (Tab 2 punto 6)
IF OBJECT_ID('dbo.Seg_CatPrioridad','U') IS NULL
CREATE TABLE dbo.Seg_CatPrioridad (
    IdPrioridad     INT IDENTITY(1,1) PRIMARY KEY,
    Descripcion     VARCHAR(50) NOT NULL,
    Activo          BIT NOT NULL DEFAULT 1
);
GO

/* ============================================================================
   2. TABLA MAESTRA RARR
   Un RARR por combinación Máquina + Sección/Equipo dentro de un departamento.
   El estatus alimenta la vista "Reporte RRAR" (Concluidos / Total / Pendientes)
   ============================================================================ */
IF OBJECT_ID('dbo.Seg_RARR','U') IS NULL
CREATE TABLE dbo.Seg_RARR (
    IdRARR          INT IDENTITY(1,1) PRIMARY KEY,
    IdDepartamento  INT NOT NULL,             -- id del catálogo existente tblDepartamentos
    Departamento    VARCHAR(100) NOT NULL,    -- nombre desnormalizado para reporte rápido
    IdMaquina       INT NOT NULL,             -- id del catálogo existente tblMaquinas
    Maquina         VARCHAR(100) NOT NULL,
    SeccionEquipo   VARCHAR(150) NULL,
    Estatus         VARCHAR(20) NOT NULL DEFAULT 'Pendiente',  -- Pendiente | Concluido
    NivelRiesgo     VARCHAR(20) NULL,         -- Aceptable | Bajo | Alto | Inaceptable (peor escenario)
    no_emp          VARCHAR(20) NOT NULL,     -- IBM de quien crea el RARR
    FechaCreacion   DATETIME NOT NULL DEFAULT GETDATE(),
    FechaConclusion DATETIME NULL
);
GO
CREATE INDEX IX_Seg_RARR_Depto   ON dbo.Seg_RARR (IdDepartamento, Estatus);
CREATE INDEX IX_Seg_RARR_Maquina ON dbo.Seg_RARR (IdMaquina);
GO

/* ============================================================================
   3. TAB 1 — ESCENARIOS DE RIESGO
   Calificacion = Severidad.Valor * Probabilidad.Valor * Frecuencia.Valor
   NivelRiesgo según rangos (ajustables en Hooks/helpers.php):
       <= 10  Aceptable | 11-30 Bajo | 31-60 Alto | > 60 Inaceptable
   ============================================================================ */
IF OBJECT_ID('dbo.Seg_EscenarioRiesgo','U') IS NULL
CREATE TABLE dbo.Seg_EscenarioRiesgo (
    IdEscenario         INT IDENTITY(1,1) PRIMARY KEY,
    IdRARR              INT NOT NULL FOREIGN KEY REFERENCES dbo.Seg_RARR(IdRARR),
    IdCategoriaPeligro  INT NOT NULL FOREIGN KEY REFERENCES dbo.Seg_CatCategoriaPeligro(IdCategoria),
    DescripcionPeligro  VARCHAR(500) NOT NULL,   -- punto 4: campo libre
    EscenarioRiesgo     VARCHAR(500) NULL,       -- punto 4: campo libre complementario
    IdSeveridad         INT NOT NULL FOREIGN KEY REFERENCES dbo.Seg_CatSeveridad(IdSeveridad),
    IdProbabilidad      INT NOT NULL FOREIGN KEY REFERENCES dbo.Seg_CatProbabilidad(IdProbabilidad),
    IdFrecuencia        INT NOT NULL FOREIGN KEY REFERENCES dbo.Seg_CatFrecuencia(IdFrecuencia),
    PersonalExpuesto    VARCHAR(100) NULL,       -- punto 8: input libre
    Calificacion        INT NOT NULL,            -- Sev*Prob*Frec (se calcula en el endpoint)
    NivelRiesgo         VARCHAR(20) NOT NULL,    -- Aceptable | Bajo | Alto | Inaceptable
    no_emp              VARCHAR(20) NOT NULL,
    FechaRegistro       DATETIME NOT NULL DEFAULT GETDATE(),
    Activo              BIT NOT NULL DEFAULT 1
);
GO
CREATE INDEX IX_Seg_EscenarioRiesgo_RARR ON dbo.Seg_EscenarioRiesgo (IdRARR, Activo);
GO

/* ============================================================================
   4. TAB 2 — EVALUACIÓN / PLAN DE ACCIÓN POR COMPONENTE
   ============================================================================ */
IF OBJECT_ID('dbo.Seg_EvaluacionRARR','U') IS NULL
CREATE TABLE dbo.Seg_EvaluacionRARR (
    IdEvaluacion        INT IDENTITY(1,1) PRIMARY KEY,
    IdRARR              INT NOT NULL FOREIGN KEY REFERENCES dbo.Seg_RARR(IdRARR),
    Componente          VARCHAR(150) NULL,       -- punto 2: se llena automático según máquina
    FechaSistema        DATETIME NOT NULL DEFAULT GETDATE(),  -- punto 3: fecha zona MX
    DescripcionHallazgo VARCHAR(MAX) NULL,       -- punto 4: textarea
    IdTipoControl       INT NULL FOREIGN KEY REFERENCES dbo.Seg_CatTipoControl(IdTipoControl), -- punto 5
    IdPrioridad         INT NULL FOREIGN KEY REFERENCES dbo.Seg_CatPrioridad(IdPrioridad),     -- punto 6
    AreaResponsable     VARCHAR(150) NULL,       -- punto 7: campo libre
    PorcentajeAvance    INT NOT NULL DEFAULT 0,  -- punto 8: input 0-100, alimenta la barra
    AccionesPropuestas  VARCHAR(MAX) NULL,       -- punto 9: textarea
    NombreResponsable   VARCHAR(150) NULL,       -- punto 10: input texto
    FechaCompromiso     DATE NULL,               -- punto 11: input date
    no_emp              VARCHAR(20) NOT NULL,
    FechaRegistro       DATETIME NOT NULL DEFAULT GETDATE(),
    Activo              BIT NOT NULL DEFAULT 1,
    CONSTRAINT CK_Seg_EvaluacionRARR_Avance CHECK (PorcentajeAvance BETWEEN 0 AND 100)
);
GO
CREATE INDEX IX_Seg_EvaluacionRARR_RARR ON dbo.Seg_EvaluacionRARR (IdRARR, Activo);
GO

/* ============================================================================
   5. TAB 3 (BLOQUE A) — ACCIONES DE MEJORA
   Puntos 1-3: campo libre + tabla con Fecha implementación, Inversión estimada,
   Estatus y Acciones (campos por definir)
   ============================================================================ */
IF OBJECT_ID('dbo.Seg_AccionMejora','U') IS NULL
CREATE TABLE dbo.Seg_AccionMejora (
    IdAccion            INT IDENTITY(1,1) PRIMARY KEY,
    IdRARR              INT NOT NULL FOREIGN KEY REFERENCES dbo.Seg_RARR(IdRARR),
    Descripcion         VARCHAR(500) NOT NULL,   -- punto 1: campo libre
    FechaImplementacion DATE NULL,               
    InversionEstimada   DECIMAL(12,2) NULL,      
    IdEstatus           INT NULL FOREIGN KEY REFERENCES dbo.Seg_CatEstatus(IdEstatus),
    no_emp              VARCHAR(20) NOT NULL,
    FechaRegistro       DATETIME NOT NULL DEFAULT GETDATE(),
    Activo              BIT NOT NULL DEFAULT 1
);
GO
CREATE INDEX IX_Seg_AccionMejora_RARR ON dbo.Seg_AccionMejora (IdRARR, Activo);
GO

/* ============================================================================
   6. TAB 3 (BLOQUE B) — SEGUIMIENTO DE MEDIDAS DE CONTROL
   Puntos 4-9: textarea, select tipo, date, select estatus, tabla
   ============================================================================ */
IF OBJECT_ID('dbo.Seg_SeguimientoControl','U') IS NULL
CREATE TABLE dbo.Seg_SeguimientoControl (
    IdSeguimiento       INT IDENTITY(1,1) PRIMARY KEY,
    IdRARR              INT NOT NULL FOREIGN KEY REFERENCES dbo.Seg_RARR(IdRARR),
    Descripcion         VARCHAR(MAX) NOT NULL,   -- punto 4: textarea
    IdTipoControl       INT NULL FOREIGN KEY REFERENCES dbo.Seg_CatTipoControl(IdTipoControl), -- punto 5
    FechaImplementacion DATE NULL,               -- punto 6: input date
    IdEstatus           INT NULL FOREIGN KEY REFERENCES dbo.Seg_CatEstatus(IdEstatus),         -- punto 7
    no_emp              VARCHAR(20) NOT NULL,
    FechaRegistro       DATETIME NOT NULL DEFAULT GETDATE(),
    Activo              BIT NOT NULL DEFAULT 1
);
GO
CREATE INDEX IX_Seg_SeguimientoControl_RARR ON dbo.Seg_SeguimientoControl (IdRARR, Activo);
GO

/* ============================================================================
   7. PERSONAL CAPACITADO (para las cards de la vista Reporte:
      Total personal / Capacitados / Pendientes)
   Total personal se saca de dbo.ri_cat_empleados por departamento;
   los capacitados se registran aquí por no_emp.
   ============================================================================ */
IF OBJECT_ID('dbo.Seg_PersonalCapacitado','U') IS NULL
CREATE TABLE dbo.Seg_PersonalCapacitado (
    IdCapacitacion      INT IDENTITY(1,1) PRIMARY KEY,
    IdDepartamento      INT NOT NULL,
    no_emp              VARCHAR(20) NOT NULL,    -- IBM del empleado capacitado
    IdRARR              INT NULL FOREIGN KEY REFERENCES dbo.Seg_RARR(IdRARR),
    FechaCapacitacion   DATETIME NOT NULL DEFAULT GETDATE(),
    no_emp_registro     VARCHAR(20) NOT NULL,    -- IBM de quien registra
    Activo              BIT NOT NULL DEFAULT 1
);
GO
CREATE UNIQUE INDEX UX_Seg_PersonalCapacitado ON dbo.Seg_PersonalCapacitado (IdDepartamento, no_emp) WHERE Activo = 1;
GO

/* ============================================================================
   8. DATOS SEMILLA DE CATÁLOGOS
   ============================================================================ */
IF NOT EXISTS (SELECT 1 FROM dbo.Seg_CatCategoriaPeligro)
INSERT INTO dbo.Seg_CatCategoriaPeligro (Descripcion) VALUES
 ('Mecánico'),('Eléctrico'),('Térmico'),('Ruido'),('Vibración'),
 ('Radiaciones'),('Materiales y sustancias'),('Ergonómico'),
 ('Caídas / trabajo en alturas'),('Atrapamiento'),('Corte / cizallamiento'),('Otro');

IF NOT EXISTS (SELECT 1 FROM dbo.Seg_CatSeveridad)
INSERT INTO dbo.Seg_CatSeveridad (Descripcion, Valor) VALUES
 ('Lesión menor sin días perdidos',1),
 ('Lesión con días perdidos',3),
 ('Lesión incapacitante',5),
 ('Fatalidad / incapacidad permanente',10);

IF NOT EXISTS (SELECT 1 FROM dbo.Seg_CatProbabilidad)
INSERT INTO dbo.Seg_CatProbabilidad (Descripcion, Valor) VALUES
 ('Remota',1),('Poco probable',2),('Probable',4),('Muy probable',6);

IF NOT EXISTS (SELECT 1 FROM dbo.Seg_CatFrecuencia)
INSERT INTO dbo.Seg_CatFrecuencia (Descripcion, Valor) VALUES
 ('Rara vez (anual)',1),('Ocasional (mensual)',2),('Frecuente (semanal)',3),('Continua (diaria)',4);

IF NOT EXISTS (SELECT 1 FROM dbo.Seg_CatEstatus)
INSERT INTO dbo.Seg_CatEstatus (Descripcion) VALUES
 ('Pendiente'),('En proceso'),('Concluido'),('Cancelado');

IF NOT EXISTS (SELECT 1 FROM dbo.Seg_CatTipoControl)
INSERT INTO dbo.Seg_CatTipoControl (Descripcion) VALUES
 ('Eliminación'),('Sustitución'),('Control de ingeniería'),
 ('Control administrativo'),('Equipo de protección personal (EPP)');

IF NOT EXISTS (SELECT 1 FROM dbo.Seg_CatPrioridad)
INSERT INTO dbo.Seg_CatPrioridad (Descripcion) VALUES
 ('Baja'),('Media'),('Alta'),('Urgente');
GO
