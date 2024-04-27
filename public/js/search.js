// Get references to input field and autocomplete container
const inputField = document.getElementById('search');
const autocompleteList = document.getElementById('autocomplete-list');
// The minimum number of characters to start searching.
const minNumCharacters = 2;

// Function to fetch autocomplete suggestions based on user input
async function fetchSuggestions(input) {
    const autocompleteUrl = document.getElementById('autocompleteUrl').value;
    const response = await fetch(autocompleteUrl + '?query=' + input);

    // Throw an error in case the response status is different from 200 (ie: OK).
    if (response.status !== 200) {
        throw new Error('Couldn\'t fetch the data. status: ' + response.status);
    }

    // Wait until the promise returned by the response object is completed.
    const suggestions = await response.json();

    return suggestions;
}

// Function to display autocomplete suggestions
function displaySuggestions(suggestions, input) {
    // Clear previous suggestions
    autocompleteList.innerHTML = '';
    // Show the suggestions list.
    document.getElementById('autocomplete-list').style.display = 'block';

    // Display new suggestions
    suggestions.forEach(suggestion => {
        const suggestionElement = document.createElement('div');
        suggestionElement.setAttribute('class', 'suggestion');
        suggestionElement.textContent = suggestion;
        //highlightInput(suggestion, input);

        suggestionElement.addEventListener('click', () => {
            // Handle selection of suggestion
            inputField.value = suggestion;
            // Clear suggestions after selection
            autocompleteList.innerHTML = ''; 
            // Hide the suggestion list.
            document.getElementById('autocomplete-list').style.display = 'none';
        });

        autocompleteList.appendChild(suggestionElement);
    });
}

function highlightInput(suggestion, input) {
    for (let i = 0; i < suggestion.length; i++) {
        console.log(suggestion.charAt(i));
        if (suggestion.charAt(i) == input[0]) {

        }
    }

}

// Event listener for input field to fetch and display suggestions
inputField.addEventListener('input', () => {
    const input = inputField.value;

    // Don't treat inputs starting with a space character.
    if (input.startsWith(' ')) {
        return;
    }

    if (input.length > minNumCharacters) {
        fetchSuggestions(input).then(suggestions => {
            displaySuggestions(suggestions, input);
        }).catch(error => {
            console.log('Error: ' + error.message);
        })
    }
    else {
        document.getElementById('autocomplete-list').style.display = 'none';
    }
});

