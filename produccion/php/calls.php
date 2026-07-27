<?php
require_once "index.php";

if (isset($_GET["datosmaquinabcm4"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM BCM4_Operacion WHERE OperMaq is not null ORDER BY BCM4_Operacion_ndx DESC) as T
	ORDER BY BCM4_Operacion_ndx ASC;");
}
else if (isset($_GET["datosmaquinabcm3"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM BCM3_Operacion WHERE OperMaq is not null ORDER BY BCM3_Operacion_ndx DESC) as T
	ORDER BY BCM3_Operacion_ndx ASC;");
}
else if (isset($_GET["datosmaquinabcm1"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM BCM1_Operacion WHERE OperMaq is not null ORDER BY BCM1_Operacion_ndx DESC) as T
	ORDER BY BCM1_Operacion_ndx ASC;");
}
else if (isset($_GET["datosmaquinamp25"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM PE08_Operacion WHERE OperMaq is not null ORDER BY PE08_Operacion_ndx DESC) as T
	ORDER BY PE08_Operacion_ndx ASC;");
}
else if (isset($_GET["datosmaquinape10"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM PE10_Operacion WHERE OperMaq is not null ORDER BY PE10_Operacion_ndx DESC) as T
	ORDER BY PE10_Operacion_ndx ASC;");
}
else if (isset($_GET["datosmaquinamp22"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM TP_Operacion WHERE OperMaq is not null ORDER BY TP_Operacion_ndx DESC) as T
	ORDER BY TP_Operacion_ndx ASC;");
}
else if (isset($_GET["datosmaquinapa01"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) pa01_operacion_ndx,Cortes,(Rechazos+Rechazos2) AS Rechazos,DATEADD(HOUR,-1,t_stamp) AS t_stamp,Velocidad,OperMaq FROM PA01_Operacion WHERE OperMaq is not null ORDER BY PA01_Operacion_ndx DESC) as T
	ORDER BY PA01_Operacion_ndx ASC;");
}

else if (isset($_GET["datosmaquinapa02"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM PA02_Operacion WHERE OperMaq is not null ORDER BY PA02_Operacion_ndx DESC) as T
	ORDER BY PA02_Operacion_ndx ASC;");
}

else if (isset($_GET["datosmaquinapa03"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM PA03_Operacion WHERE OperMaq is not null ORDER BY PA03_Operacion_ndx DESC) as T
	ORDER BY PA03_Operacion_ndx ASC;");
}

else if (isset($_GET["datosmaquinapa04"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT * FROM (SELECT TOP ($numhrs) * FROM PA04_Operacion WHERE OperMaq is not null ORDER BY PA04_Operacion_ndx DESC) as T
	ORDER BY PA04_Operacion_ndx ASC;");
}
else if (isset($_GET["datosmaquinapa05"])) {
	$monitor = new Monitor();
	$numhrs=$_GET["numhrs"]*120;
	$monitor->datosinicio("SELECT *, t_stamp2 as t_stamp FROM (SELECT TOP ($numhrs) * FROM pa05_Operacion WHERE OperMaq is not null ORDER BY pa05_Operacion_ndx DESC) as T
	ORDER BY pa05_Operacion_ndx ASC;");
}


else if (isset($_GET["infopa01"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 pa01_operacion_ndx,Cortes,(Rechazos+Rechazos2) AS Rechazos,DATEADD(HOUR,-1,t_stamp) AS t_stamp,Velocidad,OperMaq FROM PA01_Operacion WHERE OperMaq is not null ORDER BY pa01_operacion_ndx DESC");
}
else if (isset($_GET["infopa02"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM PA02_Operacion WHERE OperMaq is not null ORDER BY pa02_operacion_ndx DESC");
}
else if (isset($_GET["infopa03"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM PA03_Operacion WHERE OperMaq is not null ORDER BY pa03_operacion_ndx DESC");
}
else if (isset($_GET["infopa04"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM PA04_Operacion WHERE OperMaq is not null ORDER BY pa04_operacion_ndx DESC");
}
else if (isset($_GET["infopa05"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM pa05_Operacion WHERE OperMaq is not null ORDER BY pa05_operacion_ndx DESC");
}
// else if (isset($_GET["TPmaquina"])) {
// 	$monitor = new Monitor();
// 	$monitor->TPmaquina();
// }
else if (isset($_GET["infobcm4"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM BCM4_Operacion WHERE OperMaq is not null ORDER BY BCM4_Operacion_ndx DESC");
}
else if (isset($_GET["infobcm3"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM BCM3_Operacion WHERE OperMaq is not null ORDER BY BCM3_Operacion_ndx DESC");
}
else if (isset($_GET["infobcm1"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM BCM1_Operacion WHERE OperMaq is not null ORDER BY BCM1_Operacion_ndx DESC");
}
else if (isset($_GET["infomp25"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM PE08_Operacion WHERE OperMaq is not null ORDER BY PE08_Operacion_ndx DESC");
}
else if (isset($_GET["infope10"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM PE10_Operacion WHERE OperMaq is not null ORDER BY PE10_Operacion_ndx DESC");
}
else if (isset($_GET["infomp22"])) {
	$monitor = new Monitor();
	$monitor->informacionmaquina("SELECT TOP 1 * FROM TP_Operacion WHERE OperMaq is not null ORDER BY TP_Operacion_ndx DESC");
}

?>