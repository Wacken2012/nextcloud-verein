/* global OC */
(function(){
    window.__verein_menu_fix_loaded = true;
    console.debug('verein: menu-fix.js loaded');

    function getAppUrl(){
        if (typeof OC !== 'undefined' && typeof OC.generateUrl === 'function'){
            try { return OC.generateUrl('/apps/verein/'); } catch(e){}
        }
        // best-effort fallback: derive from current location
        var base = window.location.pathname.replace(/\/index.php.*$/,'/');
        return base + 'apps/verein/';
    }

    function fixLinks(){
        var links = document.querySelectorAll('.app-menu-entry__link');
        if (!links || links.length === 0) return;
        var appUrl = getAppUrl();
        links.forEach(function(a){
            try{
                var labelEl = a.querySelector('.app-menu-entry__label');
                var label = labelEl ? labelEl.textContent.trim() : a.textContent.trim();
                if (label === 'Verein' && (!a.getAttribute('href') || a.getAttribute('href').trim() === '')){
                    a.setAttribute('href', appUrl);
                    a.setAttribute('target','_self');
                    console.debug('verein: fixed menu href to', appUrl);
                }
            }catch(e){/* swallow */}
        });
    }

    if (document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', fixLinks);
    } else { fixLinks(); }

    // Observe header to catch dynamic updates
    var header = document.getElementById('header');
    if (header){
        var mo = new MutationObserver(function(){ fixLinks(); });
        mo.observe(header, { childList: true, subtree: true });
    }
})();
