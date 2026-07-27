document.getElementById('buscarecibo').addEventListener('click',function(e){
    e.preventDefault();
    const recibo = document.getElementById('noempnom').value;
    if(recibo.length !=5){
      swal.fire('Lo siento','Debes escribir tu numero de empleado a 5 digitos','warning');
      return false;
    }
    pdfjsLib.getDocument('archivo.pdf').promise.then(function(pdf) {
        var totalPages = pdf.numPages;
        function searchInPage(pageNumber) {
          pdf.getPage(pageNumber).then(function(page) {
            page.getTextContent().then(function(textContent) {
              var text = '';
              textContent.items.forEach(function (textItem) {
                text += textItem.str + ' ';
              });
              if (text.includes('0'+recibo)) {
                var scale = 3;
                var viewport = page.getViewport({ scale: scale });
                var canvas = document.getElementById('miCanvas');
                var context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                var renderContext = {
                  canvasContext: context,
                  viewport: viewport
                };
                page.render(renderContext);
              } else {
                if (pageNumber < totalPages) {
                  searchInPage(pageNumber + 1);
                } else {
                  console.log('El texto no se encontró en el documento.');
                }
              }
            });
          });
        }
        searchInPage(1);
      });
})
document.getElementById('enviarrecibo').addEventListener('click', function(e){
    e.preventDefault();
    (async ()=>{
        const img = document.getElementById('miCanvas');
        const correo = document.getElementById('correo').value;
        const url = img.toDataURL();
        const data = new FormData();
        data.append('url',url);
        data.append('correo',correo);
        const respuestaraw = await fetch('php/index.php',{
            method: "POST",
            body: data
        });
        respuestaraw.status == 200 && swal.fire('Listo','Recibo de nomina enviado','success')
    })();
})


document.getElementById('cargaarchivo').addEventListener('click', async function() {
  const inputFile = document.getElementById('inputFile');
  if (inputFile.files.length > 0) {
      let formData = new FormData();
      formData.append('archivo', inputFile.files[0]);
          const response = await fetch('php/cargaarchivo.php', {
              method: 'POST',
              body: formData
          });
          response.status === 200 && swal.fire('Listo','Archivo cargado correctamente','success') 
          response.status === 500 && swal.fire('Error','Hay un problema al cargar el archivo','error')
  } else {
    swal.fire('Ups','Selecciona un archivo','warning')
  }
});

