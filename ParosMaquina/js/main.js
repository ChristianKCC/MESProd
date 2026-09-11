import { SearchableSelect } from "./components/SearchableSelect.js";
import { getSecciones } from "./api/secciones.js";

const ssSec = new SearchableSelect({
    container: document.querySelector("#ss-comb-sec"),
    placeholder: "Seleccione una sección...",
    items: [],
    onCgange: (selectedItem) => console.log("Selected section:", selectedItem),
});

// Carga inicial
async function init() {
    try {
        const secciones = await getSecciones();
        ssSec.setItems(secciones); 
    } catch (error) {
        console.error("Error fetching sections:", error);
    }
}

init();