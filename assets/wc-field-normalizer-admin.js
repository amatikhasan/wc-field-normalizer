document.addEventListener('DOMContentLoaded', () => {
    const suggestionsContainer = document.getElementById('wcfn-meta-suggestions');
    const textarea = document.getElementById('wcfn-meta-textarea');

    if (!suggestionsContainer || !textarea) {
        return;
    }

    // Add CSS for chips
    const style = document.createElement('style');
    style.textContent = `
        .wcfn-chip {
            display: inline-block;
            background: #f0f0f1;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 4px 8px;
            margin: 0 8px 8px 0;
            cursor: pointer;
            font-size: 13px;
        }
        .wcfn-chip:hover {
            background: #fff;
            border-color: #2271b1;
            color: #2271b1;
        }
        .wcfn-loading {
            font-style: italic;
            color: #646970;
        }
    `;
    document.head.appendChild(style);

    suggestionsContainer.innerHTML = '<span class="wcfn-loading">Loading available keys...</span>';

    // Fetch keys
    const params = new URLSearchParams({
        action: 'wcfn_get_meta_keys',
        nonce: wcfnConfig.nonce
    });

    fetch(wcfnConfig.ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        suggestionsContainer.innerHTML = '';

        if (!data.success || !data.data || data.data.length === 0) {
            suggestionsContainer.innerHTML = '<em>No custom meta keys found.</em>';
            return;
        }

        const keys = data.data;

        keys.forEach(key => {
            const chip = document.createElement('span');
            chip.className = 'wcfn-chip';
            chip.textContent = key;
            chip.title = `Click to add ${key}`;
            
            chip.addEventListener('click', () => {
                const currentVal = textarea.value.trim();
                if (currentVal) {
                    textarea.value = currentVal + '\n' + key;
                } else {
                    textarea.value = key;
                }
            });

            suggestionsContainer.appendChild(chip);
        });
    })
    .catch(error => {
        console.error('Error fetching meta keys:', error);
        suggestionsContainer.innerHTML = '<span style="color:#d63638">Error loading keys.</span>';
    });
});
