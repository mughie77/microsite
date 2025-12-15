// JS remains the same
document.addEventListener('DOMContentLoaded', function () {
    const menuContainer = document.getElementById('menu-items-container');
    const addButton = document.getElementById('add-menu-item');

    const updateIndexes = () => {
        const items = menuContainer.querySelectorAll('.menu-item');
        items.forEach((item, index) => {
            item.dataset.index = index;
            const header = item.querySelector('h3');
            if (header) header.textContent = `Menu Item ${index + 1}`;
            item.querySelectorAll('input, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
            });
        });
    };

    addButton.addEventListener('click', () => {
        const newIndex = menuContainer.querySelectorAll('.menu-item').length;
        const newItem = document.createElement('div');
        newItem.className = 'menu-item';
        newItem.dataset.index = newIndex;
        newItem.innerHTML = `
            <div class="menu-item-header"><h3>Menu Item ${newIndex + 1}</h3><button type="button" class="btn btn-danger remove-menu-item">Remove</button></div>
            <div class="menu-item-fields">
                <div class="form-group"><label>Text:</label><input type="text" name="menu[${newIndex}][text]" value=""></div>
                <div class="form-group"><label>Icon:</label><div class="icon-picker-container"><input type="text" name="menu[${newIndex}][icon]" value="" readonly class="icon-input"><button type="button" class="btn btn-secondary select-icon-btn">Select Icon</button></div></div>
                <div class="form-group">
                    <label>Type:</label>
                    <select name="menu[${newIndex}][type]">
                        <option value="link" selected>Link</option>
                        <option value="modal">Modal</option>
                    </select>
                </div>
                <div class="form-group"><label>URL (for type 'link'):</label><input type="text" name="menu[${newIndex}][url]" value="#"></div>
                <div class="form-group" style="grid-column: span 2;"><label>Content (for type 'modal'):</label><input type="text" name="menu[${newIndex}][content]" value=""></div>
            </div>
        `;
        menuContainer.appendChild(newItem);
        updateIndexes();
    });

    menuContainer.addEventListener('click', e => {
        if (e.target.classList.contains('remove-menu-item')) {
            e.target.closest('.menu-item').remove();
            updateIndexes();
        }
    });

    const modal = document.getElementById('icon-modal');
    const closeModalBtn = modal.querySelector('.close-modal');
    const iconGrid = modal.querySelector('#icon-grid');
    const iconSearch = modal.querySelector('#icon-search');
    let currentTargetInput = null;

    document.body.addEventListener('click', e => {
        if (e.target.classList.contains('select-icon-btn')) {
            currentTargetInput = e.target.previousElementSibling;
            modal.style.display = 'block';
            iconSearch.focus();
        }
    });

    const closeModal = () => modal.style.display = 'none';
    closeModalBtn.addEventListener('click', closeModal);
    window.addEventListener('click', e => { if (e.target == modal) closeModal(); });

    iconGrid.addEventListener('click', e => {
        const iconPreview = e.target.closest('.icon-preview');
        if (iconPreview) {
            if (currentTargetInput) currentTargetInput.value = iconPreview.dataset.iconClass;
            closeModal();
        }
    });

    iconSearch.addEventListener('keyup', () => {
        const filter = iconSearch.value.toLowerCase();
        iconGrid.querySelectorAll('.icon-preview').forEach(div => {
            div.style.display = div.dataset.iconClass.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
    updateIndexes();
});
