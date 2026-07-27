document.getElementById("btnExcel").addEventListener("click", async (e) => {
  e.preventDefault();
  const loader = document.getElementById("loader");
  loader.style.display = "flex";

  const formData = new FormData(document.getElementById("formReporte"));

  try {
    const response = await fetch("../ReporteProduccion/php/reporteProduccionMesexcel.php", {
      method: "POST",
      body: formData
    });

    if (!response.ok) throw new Error("Error al generar el reporte");

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = "ReporteProduccionMes.xlsx";
    document.body.appendChild(a);
    a.click();
    a.remove();

    loader.style.display = "none";
  } catch (err) {
    console.error(err);
    loader.style.display = "none";
    alert("Hubo un problema al generar el reporte");
  }
});
