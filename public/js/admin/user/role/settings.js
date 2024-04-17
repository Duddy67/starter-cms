document.addEventListener('DOMContentLoaded', () => {
    // A new role is being created.
    if (document.getElementById('permissions')) {
        document.getElementById('role_type').addEventListener('change', function() {
            setCheckboxes(true);
        });

        setCheckboxes();
    }

    const checkboxes = document.querySelectorAll('input[type="checkbox"]');

    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].addEventListener('change', function() {
            if (checkboxes[i].checked) {
                permissionChecked(checkboxes[i].value);
            }
            else {
                permissionUnchecked(checkboxes[i].value);
            }
        });
    }

    function permissionChecked(name) {
        let item = name.match(/-([a-z0-9]+)$/)[1];
        
        if (item == 'categories') {
            item = name.match(/-([a-z0-9]+-[a-z0-9]+)$/)[1];
        }

        // A user able to update an item is also able to create one.
        if (/^update-/.test(name)) {
            document.getElementById('create-' + item).checked = true;

            if (document.getElementById('update-own-' + item)) {
                document.getElementById('update-own-' + item).checked = false;
            }

            if (document.getElementById('delete-own-' + item)) {
                document.getElementById('delete-own-' + item).checked = false;
            }

            return;
        }

        // A user able to delete an item is also able to update and create one.
        if (/^delete-/.test(name)) {
            document.getElementById('create-' + item).checked = true;
            document.getElementById('update-' + item).checked = true;

            return;
        }
    }

    function permissionUnchecked(name) {
        let item = name.match(/-([a-z0-9]+)$/)[1];

        if (item == 'categories') {
            item = name.match(/-([a-z0-9]+-[a-z0-9]+)$/)[1];
        }

        // A user not able to create an item is also not able to update or delete one.
        if (/^create-/.test(name)) {
            document.getElementById('update-' + item).checked = false;
            document.getElementById('delete-' + item).checked = false;
        }
    }

    /*
     * Sets the role permissions according to the permission list.
     */
    function setCheckboxes(hasChanged) {
        let permissions = JSON.parse(document.getElementById('permissions').value);
        let reloaded = document.getElementById('reloaded').value;
        let regex = new RegExp(document.getElementById('role_type').value);

        const checkboxes = document.querySelectorAll('input[type="checkbox"]');

        for (let i = 0; i < checkboxes.length; i++) {
            let name = checkboxes[i].id;
            let section = checkboxes[i].dataset.section;

            for (let j = 0; j < permissions[section].length; j++) {
                if (permissions[section][j].name == name) {
                   
                    if (permissions[section][j].optional !== undefined && regex.test(permissions[section][j].optional)) {
                        document.getElementById(name).disabled = false;
                    }
                    else {
                        document.getElementById(name).disabled = true;
                    }

                    // The page has been reloaded due to a validation error.
                    if (permissions[section][j].optional !== undefined && reloaded && hasChanged === undefined) {
                        // Let the old() function handle the checked and unchecked values of the optional checkboxes.
                        break;
                    }

                    if (regex.test(permissions[section][j].roles)) {
                        document.getElementById(name).checked = true;
                    }
                    else {
                        document.getElementById(name).checked = false;
                    }

                    break;
                }
            }
        }
    }
});

