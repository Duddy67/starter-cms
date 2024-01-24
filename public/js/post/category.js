document.addEventListener('DOMContentLoaded', () => {

    if (document.getElementById('search-btn')) {
        document.getElementById('search-btn').addEventListener('click', function(e) {
            if (document.getElementById('search').value !== '') {
                checkEmptyFilters();
                document.getElementById('item-filters').submit();
            }
        });

        document.getElementById('clear-search-btn').addEventListener('click', function(e) {
            document.getElementById('search').value = '';
            checkEmptyFilters();
            document.getElementById('item-filters').submit();
        });
    }

    function checkEmptyFilters() {
        document.querySelectorAll('select[id^="filters-"]').forEach(function(elem) { 
            if (elem.value === null || elem.value === '') {
                elem.disabled = true;
            }

	    // Reinitialize pagination on each request.
            if (document.getElementById('filters-pagination').length) {
                document.getElementById('filters-pagination').disabled = true;
            }

            if (document.getElementById('search').value === '') {
                document.getElementById('search').disabled = true;
            }
        });
    }
});


