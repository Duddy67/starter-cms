(function() {

    // Run a function when the page is fully loaded including graphics.
    document.addEventListener('DOMContentLoaded', () => {

        document.getElementById('locale').onchange = function(e) {
            let action = document.getElementById('itemForm').getAttribute('action');
            let url = '';
            const currentLocale = document.getElementById('currentLocale').value;
            const cancelChangeLocale = document.getElementById('cancelChangeLocale');

            // Check for locale canceling to prevent infinite loop.
            if (cancelChangeLocale.value == 1) {
                // Reset the value for the next time then leave the function.
                cancelChangeLocale.value = 0;
                return;
            }

            // The url has query parameters..
            if (action.split('?').length - 1) {
                // First add the "edit" part to the url. 
                url = action.substring(0, action.indexOf('?'));
                url = url+'/edit?'+action.substring(action.indexOf('?') + 1);

                // The "locale" query parameter is set already.
                if (action.split('locale=').length - 1) {
                    // Replace the locale value with the one selected.
                    url = url.replace('locale='+currentLocale, 'locale='+this.value);
                }
                else {
                    url = url+'&locale='+this.value;
                }
            }
            else {
                url = action+'/edit?locale='+this.value;
            }

            if (window.confirm("Do you really want to leave?")) {
                // Reload the edit form.
                window.location.replace(url);
            }
            else {
                // First cancel the locale change.
                document.getElementById('cancelChangeLocale').value = 1;
                // Set the locale back to the previous selection.
                document.getElementById('locale').value = currentLocale;
                $('#locale').trigger('change.select2');
            }
        }
    });

})();
