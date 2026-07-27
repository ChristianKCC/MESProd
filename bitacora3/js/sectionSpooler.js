export class PresentacionSpooler {
  async getClaves(){
    const { data } = await axios.get('php/presentacion.php?getClaves');
    return data;
  }

  async saveTblSpooler({ folio, clave, noTabla}){
    const formData = new FormData();
    formData.append('savePresentacionSpooler', true);
    formData.append('folio', folio);
    formData.append('clave', clave);
    formData.append('noTabla', noTabla);
    const { data } = await axios.post("php/presentacion.php", formData);
    return data.idPT;
  }

  async saveRollos({ idPT, rollo }){
    const { data } = await axios.post('php/presentacion.php', {
      saveRollos: true,
      idPT,
      rollo
    });
    return data;

  }

  async saveBajada({ idPT, noBajada, bobinas, kgBajada, mlBajada, mm2Bajada, kgMerma, comentarios }){
    const formData = new FormData();
    formData.append('saveBajada', true);
    formData.append('idPT', idPT);
    formData.append('noBajada', noBajada);
    formData.append('bobinas', bobinas);
    formData.append('kgBajada', kgBajada);
    formData.append('mlBajada', mlBajada);
    formData.append('mm2Bajada', mm2Bajada);
    formData.append('kgMerma', kgMerma);
    formData.append('comentarios', comentarios);
    const { data } = await axios.post("php/presentacion.php", formData);
    return data;

  }

  async getRolloPorNumero(noRollo){
    const { data } = await axios.get("php/presentacion.php", {
      params: {
        getRolloPorNumero: true,
        noRollo
      }
    } );
    return data[0] ? { noRollo: data[0].noRollo, kg: data[0].kg } : null;
  }

  async getSesionPorFolio(folio){
    const { data } = await axios.get('php/presentacion.php', {
      params: {
        getSesionPorFolio: true,
        folio
      }
    });
    return data;
  }
}