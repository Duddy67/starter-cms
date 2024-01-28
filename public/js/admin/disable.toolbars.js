document.addEventListener('DOMContentLoaded', () => {
    document.getElementsByClassName('sidebar-disabled')[0].insertAdjacentHTML('afterbegin', '<div class="disable-panel">&nbsp;</div>');
    document.getElementsByClassName('navbar-disabled')[0].insertAdjacentHTML('afterbegin', '<div class="disable-panel top-panel">&nbsp;</div>');
});
