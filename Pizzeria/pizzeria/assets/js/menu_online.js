/**
 * ######################################################################
 * # FILE: assets/js/menu_online.js
 * # Logica per il menu a fisarmonica (accordion)
 * ######################################################################
 */

function toggleAccordion(element) {
    // Seleziona il contenuto (il div successivo)
    var content = element.nextElementSibling;
    
    // Controlla se è già aperto
    const isCurrentlyOpen = content.classList.contains('show');

    // Chiude tutti gli altri elementi a fisarmonica aperti nella pagina
    document.querySelectorAll('.accordion-content').forEach(otherContent => {
        otherContent.classList.remove('show');
    });

    // Se l'elemento non era aperto (o dopo aver chiuso tutti gli altri), aprilo
    if (!isCurrentlyOpen) {
        content.classList.add("show");
    }
}