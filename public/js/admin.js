// Filter list rows without replacing server forms, links, or Alpine modal state.
document.querySelectorAll('[data-admin-search]').forEach((input) => {
    const table = document.getElementById(input.dataset.adminSearch);
    if (!table) return;
    const rows = Array.from(table.querySelectorAll('tbody > tr[data-admin-row]'));
    if (!rows.length) return;
    const empty = document.createElement('div');
    empty.className = 'admin-search-empty';
    empty.textContent = 'No matching records. Try a different name or code.';
    empty.hidden = true;
    const summary = document.createElement('div');
    summary.className = 'admin-search-summary';
    summary.setAttribute('role', 'status');
    summary.setAttribute('aria-live', 'polite');
    table.parentElement.after(empty, summary);
    const filter = () => {
        const query = input.value.trim().toLocaleLowerCase();
        let visible = 0;
        rows.forEach((row) => {
            const matches = row.textContent.toLocaleLowerCase().includes(query);
            row.hidden = !matches;
            if (matches) visible++;
        });
        empty.hidden = visible !== 0;
        summary.textContent = `Showing ${visible} of ${rows.length} records`;
    };
    input.addEventListener('input', filter);
    filter();
});
