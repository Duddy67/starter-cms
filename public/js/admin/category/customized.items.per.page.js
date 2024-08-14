document.addEventListener('DOMContentLoaded', () => {
    const collectionType = document.getElementById('collectionType').value;

    if (document.getElementById('posts_per_page')) {
        toggleInput(document.getElementById(collectionType + '_per_page').value);
    }

    document.addEventListener('change', function(evt) {
        if (evt.target.id == collectionType + '_per_page') {
            toggleInput(evt.target.value);
        }
    });
        
    function toggleInput(perPage) {
        // Get the input used to set the customized number of items per page.
        const input = document.getElementById('customized_' + collectionType + '_per_page');

        // -2 means that the number of items per page can be customized.
        // The input must be visible.
        if (perPage == -2) {
            input.style.display = 'block';
            input.previousElementSibling.style.display = 'block';
        }
        // Hide the input.
        else {
            input.value = '';
            input.style.display = 'none';
            input.previousElementSibling.style.display = 'none';
        }
    }
});

