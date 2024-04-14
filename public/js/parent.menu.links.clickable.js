document.addEventListener('DOMContentLoaded', () => {
    const dropdownLinks = document.querySelectorAll('.navbar .dropdown > a');

    for (let i = 0; i < dropdownLinks.length; i++) {
        dropdownLinks[i].addEventListener('click', function() {
            location.href = dropdownLinks[i].href;
        });
    }
});
