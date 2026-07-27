import { SalaJuntas } from "../module/index.js";

const SalaJuntasObj = new SalaJuntas();
SalaJuntasObj.tblConsultaInfoSalaReservada("informacionSalas");

setInterval(() => {
  SalaJuntasObj.tblConsultaInfoSalaReservada("informacionSalas");
}, 5000);
