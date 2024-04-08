document.addEventListener('DOMContentLoaded', () => {

    // Hide the batch window.
    document.getElementById('cancel').addEventListener('click', function(e) {
        const modalWindow = window.parent.document.getElementById('batch-window');
        modalWindow.style.display = 'none';
    });

    // Submit the batch form.
    document.getElementById('massUpdate').addEventListener('click', function(e) {
          const batchForm = document.getElementById('batchForm');
          batchForm.submit();
    });
});
