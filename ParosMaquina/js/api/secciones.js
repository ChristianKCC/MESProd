const BASE = 'php/secciones.php';

export async function getSecciones() {
    const res = await axios.get(BASE, {
        params: {
            action: 'getSecciones'
        }
    });
    console.log(res)
    return res.data;
}