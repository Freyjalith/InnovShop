// Script de test pour vérifier le fonctionnement du menu
console.log('Script de test du menu chargé');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé, vérification des éléments...');
    
    // Vérifier la présence des éléments
    const userDropdown = document.querySelector('#userDropdown');
    const userMenu = document.querySelector('#userMenu');
    const logoutBtn = document.querySelector('a[href*="app_logout"]');
    
    console.log('Dropdown utilisateur:', userDropdown ? 'Trouvé' : 'Non trouvé');
    console.log('Menu utilisateur:', userMenu ? 'Trouvé' : 'Non trouvé');
    console.log('Bouton déconnexion:', logoutBtn ? 'Trouvé' : 'Non trouvé');
    
    // Test du menu déroulant
    if (userDropdown && userMenu) {
        userDropdown.addEventListener('click', function() {
            console.log('Clic sur le dropdown détecté');
        });
    }
    
    // Test du bouton de déconnexion
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            console.log('Clic sur déconnexion détecté');
        });
    }
});