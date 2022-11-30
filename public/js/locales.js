document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('locales').onchange = function(e) { 
        window.location.replace(this.value);
    }
});
