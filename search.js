function searchItems() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#transactions tbody tr');

    // Loop through each row in the transactions table
    rows.forEach((row) => {
        const cells = row.getElementsByTagName('td');
        let match = false;

        // Check each cell to see if it matches the search input
        for (let i = 0; i < cells.length; i++) {
            const cellText = cells[i].textContent.toLowerCase();
            if (cellText.includes(input)) {
                match = true;
                break;
            }
        }

        // Show or hide the row based on matching result
        row.style.display = match ? '' : 'none';
    });
}
