console.log("Admin JS v2.0 - Full Rewrite");

// --- STATE ---
let currentTheme = localStorage.getItem('theme') || 'light';
let pizzaCategories = []; // Loaded from DB, used for groups and forms
let allPizzas = []; // Cache for filtering

document.documentElement.setAttribute('data-theme', currentTheme);

// --- THEME ---
function toggleTheme() {
    currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);
    localStorage.setItem('theme', currentTheme);
}

// --- NAVIGATION ---
function showSection(id) {
    console.log("Switching to section:", id);
    localStorage.setItem('admin_last_section', id);
    document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    const menuItem = document.querySelector(`.menu-item[onclick*="${id}"]`);
    if (menuItem) menuItem.classList.add('active');

    if (id === 'home') loadStats();
    else if (id === 'menu') { loadCategories().then(() => loadPizzas()); }
    else if (id === 'ingredients') loadIngredients();
    else if (id === 'products') loadProducts();
    else if (id === 'settings') loadCategories().then(() => renderCategorySettings());
}

// --- MODALS ---
function openModal(type, id = null) {
    if (type === 'pizza') {
        const modal = document.getElementById('pizza-modal');
        const form = document.getElementById('pizza-form');
        const title = document.getElementById('pizza-modal-title');
        form.reset();
        document.getElementById('pizza-id').value = '';
        refreshPizzaTypeSelect();

        if (id) {
            title.textContent = 'Modifica Pizza';
            fetch(`api/api_admin.php?action=get_pizza&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        const p = data.data;
                        document.getElementById('pizza-id').value = p.id;
                        document.getElementById('pizza-name').value = p.name;
                        document.getElementById('pizza-type').value = p.type;
                        document.getElementById('pizza-price').value = parseFloat(p.base_price).toFixed(2);
                        document.getElementById('pizza-ingredients-text').value = p.ingredients_text || '';
                        document.getElementById('pizza-visible').checked = p.is_visible == 1;
                        modal.classList.add('show');
                    } else { alert('Errore: ' + data.message); }
                }).catch(e => { console.error(e); alert('Errore di rete.'); });
        } else {
            title.textContent = 'Aggiungi Nuova Pizza';
            modal.classList.add('show');
        }
    } else if (type === 'ingredient') {
        const modal = document.getElementById('ingredient-modal');
        const form = document.getElementById('ingredient-form');
        const title = document.getElementById('ingredient-modal-title');
        form.reset();
        document.getElementById('ingredient-id').value = '';

        if (id) {
            title.textContent = 'Modifica Ingrediente';
            fetch(`api/api_admin.php?action=get_ingredient&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('ingredient-id').value = data.data.id;
                        document.getElementById('ingredient-name').value = data.data.name;
                        document.getElementById('ingredient-price').value = parseFloat(data.data.price).toFixed(2);
                        modal.classList.add('show');
                    } else { alert('Errore caricamento ingrediente.'); }
                }).catch(e => { console.error(e); alert('Errore di rete.'); });
        } else {
            title.textContent = 'Aggiungi Nuovo Ingrediente';
            modal.classList.add('show');
        }
    } else if (type === 'product') {
        const modal = document.getElementById('product-modal');
        const form = document.getElementById('product-form');
        const title = document.getElementById('product-modal-title');
        form.reset();
        document.getElementById('product-id').value = '';

        if (id) {
            title.textContent = 'Modifica Prodotto';
            fetch(`api/api_admin.php?action=get_product&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('product-id').value = data.data.id;
                        document.getElementById('product-name').value = data.data.name;
                        document.getElementById('product-category').value = data.data.category;
                        document.getElementById('product-price').value = parseFloat(data.data.price).toFixed(2);
                        document.getElementById('product-visible').checked = data.data.is_visible == 1;
                        modal.classList.add('show');
                    } else { alert('Errore caricamento prodotto.'); }
                }).catch(e => { console.error(e); alert('Errore di rete.'); });
        } else {
            title.textContent = 'Aggiungi Nuovo Prodotto';
            modal.classList.add('show');
        }
    }
}

function closeModal(type) {
    const ids = { pizza: 'pizza-modal', ingredient: 'ingredient-modal', product: 'product-modal' };
    const el = document.getElementById(ids[type]);
    if (el) el.classList.remove('show');
}

// --- UPDATE PIZZA TYPE SELECT ---
function refreshPizzaTypeSelect() {
    const sel = document.getElementById('pizza-type');
    if (!sel) return;
    sel.innerHTML = '';
    pizzaCategories.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.slug;
        opt.textContent = cat.name;
        sel.appendChild(opt);
    });
    if (sel.options.length === 0) {
        sel.innerHTML = '<option value="classica">Classica</option>';
    }
}

// --- SAVE FUNCTIONS ---
function savePizza(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('pizza-form'));
    const data = Object.fromEntries(formData.entries());
    data.is_visible = document.getElementById('pizza-visible').checked ? 1 : 0;
    data.ingredients_text = document.getElementById('pizza-ingredients-text').value;

    fetch('api/api_admin.php?action=save_pizza', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                alert(d.message);
                closeModal('pizza');
                loadPizzas();
            } else { alert('Errore: ' + d.message); }
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function saveIngredient(event) {
    event.preventDefault();
    const data = {
        ingredient_id: document.getElementById('ingredient-id').value,
        name: document.getElementById('ingredient-name').value,
        price: document.getElementById('ingredient-price').value
    };
    fetch('api/api_admin.php?action=save_ingredient', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                alert('Ingrediente salvato!');
                closeModal('ingredient');
                loadIngredients();
            } else { alert('Errore: ' + d.message); }
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function saveProduct(event) {
    event.preventDefault();
    const data = {
        product_id: document.getElementById('product-id').value,
        name: document.getElementById('product-name').value,
        category: document.getElementById('product-category').value,
        price: document.getElementById('product-price').value,
        is_visible: document.getElementById('product-visible').checked ? 1 : 0
    };
    fetch('api/api_admin.php?action=save_product', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                alert('Prodotto salvato!');
                closeModal('product');
                loadProducts();
            } else { alert('Errore: ' + d.message); }
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

// --- DELETE ---
function deleteItem(type, id) {
    // Al posto del confirm nativo che può bloccarsi, procediamo ed eliminiamo mostrando un banner
    const actions = { pizza: 'delete_pizza', ingredient: 'delete_ingredient', product: 'delete_product' };
    const action = actions[type];
    if (!action) { console.error('Tipo non supportato.'); return; }

    function showBanner(msg, isError) {
        const d = document.createElement('div');
        d.style.cssText = `position:fixed;top:0;left:0;width:100%;padding:15px;z-index:10000;text-align:center;font-weight:bold;color:white;box-shadow:0 2px 10px rgba(0,0,0,0.5);background:${isError?'#e74c3c':'#2ecc71'};`;
        d.innerText = msg;
        document.body.appendChild(d);
        setTimeout(() => d.remove(), 5000);
    }

    fetch(`api/api_admin.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
        .then(async r => {
            const raw = await r.text();
            try { return JSON.parse(raw); } 
            catch(e) { showBanner("ERRORE SERVER (invia testo al dev): " + raw.substring(0, 200), true); throw new Error(raw); }
        })
        .then(d => {
            if (d.status === 'success') {
                showBanner('Eliminato con successo!', false);
                if (type === 'pizza') loadPizzas();
                else if (type === 'ingredient') loadIngredients();
                else if (type === 'product') loadProducts();
            } else { showBanner('Errore: ' + d.message, true); }
        })
        .catch(e => { console.error(e); showBanner('Errore di rete durante eliminazione.', true); });
}

// --- SETTINGS ---
function saveSettings() {
    console.log("Saving settings...");
    const val = id => { const el = document.getElementById(id); return el ? el.value : null; };
    const chk = id => { const el = document.getElementById(id); return el ? (el.checked ? 1 : 0) : null; };

    const data = {
        enableSecondScreen: chk('enableSecondScreen'),
        screenStuzzicherie: val('screenStuzzicherie'),
        refreshTime: val('refreshTime'),
        qrUrl: val('qrUrl'),
        screenColumns: val('screenColumns'),
        screenRows: val('screenRows'),
        visibilitySpecials: chk('vis-specials'),
        // Stuzzicherie remains in settings table for now
        innerColsStuzzicherie: val('innerColsStuzzicherie'),
        rowStuzzicherie: val('rowStuzzicherie'),
        colStuzzicherie: val('colStuzzicherie'),
        spanStuzzicherie: val('spanStuzzicherie')
    };

    // Remove null values to avoid overwriting with defaults in PHP
    Object.keys(data).forEach(key => data[key] === null && delete data[key]);

    console.log("Sending settings data:", data);

    fetch('api/api_admin.php?action=save_settings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        console.log("Settings save response:", d);
        if (d.status === 'success') {
            alert('Impostazioni salvate!');
            refreshPreviewMenu(); // Automatically refresh preview
        } else alert('Errore: ' + d.message);
    })
    .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function saveCategoryPosition(id) {
    const data = {
        id: id,
        screen_number: document.querySelector(`.cat-screen-num[data-id="${id}"]`).value,
        inner_cols: document.querySelector(`.cat-inner-cols[data-id="${id}"]`).value,
        row_pos: document.querySelector(`.cat-row-pos[data-id="${id}"]`).value,
        col_pos: document.querySelector(`.cat-col-pos[data-id="${id}"]`).value,
        col_span: document.querySelector(`.cat-col-span[data-id="${id}"]`).value
    };

    fetch('api/api_admin.php?action=save_category_position', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') alert('Posizione salvata!');
        else alert('Errore: ' + d.message);
    })
    .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function savePizzeriaName() {
    const name = document.getElementById('pizzeriaName').value;
    fetch('api/api_admin.php?action=save_pizzeria_name', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name })
    })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') alert('Nome pizzeria salvato!');
            else alert('Errore: ' + d.message);
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

// --- REFRESH / SORT ---
function refreshPreviewMenu() {
    console.log("Refreshing TV preview iframe...");
    const iframe = document.getElementById('tv-preview-frame');
    if (iframe && iframe.src && iframe.src.includes('menu_screen.php')) {
        let base = iframe.src.split('&t=')[0].split('?t=')[0];
        iframe.src = base + (base.includes('?') ? '&' : '?') + 't=' + new Date().getTime();
    }
    alert('Comando di aggiornamento schermi inviato!');
}

function sortClassicPizzas() {
    if (!confirm('Riordinare tutte le Pizze Classiche e Famiglia in ordine alfabetico?')) return;
    fetch('api/api_admin.php?action=sort_classic_pizzas', { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') { alert('Pizze ordinate!'); loadPizzas(); }
            else alert('Errore: ' + d.message);
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

// --- QR CODE ---
function generateQrCode() {
    const qrContainer = document.getElementById('qr-placeholder');
    qrContainer.style.display = 'block';
    qrContainer.innerHTML = '';
    const url = document.getElementById('qrUrl').value;
    const currentImg = document.getElementById('current-qr-img');
    if (currentImg) currentImg.style.display = 'none';

    if (typeof QRCode === 'undefined') {
        alert('Libreria QR Code non caricata.');
        qrContainer.style.display = 'none';
        return;
    }
    new QRCode(qrContainer, { text: url, width: 200, height: 200, colorDark: "#000", colorLight: "#fff", correctLevel: QRCode.CorrectLevel.H });

    setTimeout(() => {
        const canvas = qrContainer.querySelector('canvas');
        if (canvas) {
            fetch('api/api_qr.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ image: canvas.toDataURL("image/png"), qrUrl: url })
            })
                .then(r => r.json())
                .then(d => {
                    if (d.status === 'success') {
                        alert('QR Code generato e salvato!');
                        const placeholder = document.getElementById('current-qr-placeholder');
                        if (placeholder) placeholder.style.display = 'none';
                        if (currentImg) { 
                            currentImg.src = d.path + '?' + Date.now(); 
                            currentImg.style.display = 'block'; 
                        }
                        qrContainer.style.display = 'none';
                    } else { alert('Errore QR: ' + d.message); }
                })
                .catch(e => { console.error(e); alert('Errore salvataggio QR.'); });
        }
    }, 500);
}

// --- CATEGORY MANAGEMENT ---
function loadCategories() {
    return fetch('api/api_admin.php?action=get_categories')
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                pizzaCategories = d.data;
            }
        })
        .catch(e => console.error('Errore caricamento categorie:', e));
}

function renderCategorySettings() {
    const container = document.getElementById('category-settings-container');
    if (!container) return;

    container.innerHTML = '';
    pizzaCategories.forEach(cat => {
        const div = document.createElement('div');
        div.className = 'category-settings-row';
        div.setAttribute('data-id', cat.id);
        div.innerHTML = `
            <div class="category-row-inner">
                <label class="switch" title="Visibile in Gestione Pizze">
                    <input type="checkbox" onchange="toggleCategoryVisibility(${cat.id}, this.checked)" ${cat.is_visible == 1 ? 'checked' : ''}>
                    <span class="slider"></span>
                </label>
                <strong class="cat-name-display">${cat.name}</strong>
                <span class="cat-slug-display">(slug: ${cat.slug})</span>
                <button class="btn btn-del btn-sm" onclick="deleteCategory(${cat.id}, '${cat.name}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });

    // Add new category form
    const addDiv = document.createElement('div');
    addDiv.className = 'category-add-form';
    addDiv.innerHTML = `
        <h4><i class="fas fa-plus-circle"></i> Aggiungi Nuova Categoria</h4>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="text" id="new-category-name" placeholder="Nome categoria (es: Premium)" style="flex:1; min-width:150px;">
            <button class="btn btn-save" onclick="addCategory()"><i class="fas fa-check"></i> Crea</button>
        </div>
        <p style="font-size:0.85em; color:#888; margin-top:5px;">Il nome viene convertito in uno slug automaticamente. Le pizze di quella categoria saranno mostrate in un accordion separato.</p>
    `;
    container.appendChild(addDiv);
}

function toggleCategoryVisibility(id, visible) {
    fetch('api/api_admin.php?action=toggle_category_visibility', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, is_visible: visible ? 1 : 0 })
    })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                // Update local cache
                const cat = pizzaCategories.find(c => c.id == id);
                if (cat) cat.is_visible = visible ? 1 : 0;
            } else { alert('Errore: ' + d.message); }
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function addCategory() {
    const name = document.getElementById('new-category-name').value.trim();
    if (!name) { alert('Inserisci un nome per la categoria.'); return; }

    fetch('api/api_admin.php?action=save_category', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, is_visible: 1 })
    })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                alert('Categoria "' + name + '" creata!');
                loadCategories().then(() => renderCategorySettings().then ? null : renderCategorySettings());
            } else { alert('Errore: ' + d.message); }
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function deleteCategory(id, name) {
    if (!confirm(`Eliminare la categoria "${name}"? Le pizze in questa categoria saranno spostate alla prima disponibile.`)) return;

    fetch('api/api_admin.php?action=delete_category', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                alert('Categoria eliminata!');
                loadCategories().then(() => { renderCategorySettings(); if (document.getElementById('menu').classList.contains('active')) loadPizzas(); });
            } else { alert('Errore: ' + d.message); }
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

// --- PIZZA LIST (Grouped by Category Accordion) ---
function loadPizzas() {
    fetch('api/api_admin.php?action=get_pizzas')
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                allPizzas = d.data;
                renderPizzas(allPizzas);
            } else { alert('Errore: ' + d.message); }
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function renderPizzas(pizzas) {
    const container = document.getElementById('pizzas-list-container');
    container.innerHTML = '';

    if (pizzaCategories.length === 0) {
        container.innerHTML = '<p>Nessuna categoria trovata. Aggiungine una nelle Impostazioni.</p>';
        return;
    }

    // Group pizzas by category slug
    const grouped = {};
    pizzaCategories.forEach(cat => { grouped[cat.slug] = []; });
    pizzas.forEach(p => {
        if (grouped[p.type] !== undefined) grouped[p.type].push(p);
        else {
            // Unknown category - put in first
            const firstSlug = pizzaCategories[0]?.slug;
            if (firstSlug) { if (!grouped[firstSlug]) grouped[firstSlug] = []; grouped[firstSlug].push(p); }
        }
    });

    pizzaCategories.forEach(cat => {
        if (!cat.is_visible) return;

        const catPizzas = grouped[cat.slug] || [];
        const details = document.createElement('details');
        details.className = 'pizza-category-accordion';
        details.open = false;

        details.innerHTML = `
            <summary>
                <i class="fas fa-pizza-slice"></i>
                ${cat.name}
                <span class="cat-count">${catPizzas.length} pizz${catPizzas.length === 1 ? 'a' : 'e'}</span>
            </summary>
            <div style="padding: 10px; background: #f9f9f9; border-bottom: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px;">
                <button class="btn" style="background: #e67e22; color: #fff; font-size: 0.9em;" onclick="promptIncreaseCategoryPrice('${cat.slug}', \`${cat.name.replace(/`/g, '\\`')}\`)">
                    <i class="fas fa-arrow-up"></i> Aumenta Prezzo
                </button>
                <button class="btn" style="background: #c0392b; color: #fff; font-size: 0.9em;" onclick="promptDecreaseCategoryPrice('${cat.slug}', \`${cat.name.replace(/`/g, '\\`')}\`)">
                    <i class="fas fa-arrow-down"></i> Riduci Prezzo
                </button>
                <button class="btn" style="background: #2980b9; color: #fff; font-size: 0.9em;" onclick="promptSetCategoryPrice('${cat.slug}', \`${cat.name.replace(/`/g, '\\`')}\`)">
                    <i class="fas fa-equals"></i> Imposta Prezzo
                </button>
            </div>
            <ul class="sortable-list" id="sortable-${cat.slug}">
                ${catPizzas.length === 0 ? '<li class="empty-cat"><i class="fas fa-info-circle"></i> Nessuna pizza in questa categoria</li>' : ''}
            </ul>
        `;

        container.appendChild(details);

        const ul = details.querySelector(`#sortable-${cat.slug}`);
        catPizzas.forEach(p => {
            const li = document.createElement('li');
            li.className = 'sortable-item';
            li.setAttribute('data-id', p.id);
            const ingredientsText = p.ingredients || 'Nessun ingrediente associato.';
            li.innerHTML = `
                <i class="fas fa-bars drag-handle"></i>
                <div style="flex-grow:1;">
                    <strong>${p.name}</strong>
                    <span class="price-badge">€ ${parseFloat(p.calculated_price || p.base_price).toFixed(2)}</span>
                    ${!p.is_visible ? '<span class="hidden-badge"><i class="fas fa-eye-slash"></i> Nascosta</span>' : ''}
                    <p class="ingredients-preview">${ingredientsText}</p>
                </div>
                <div class="pizza-actions">
                    <button class="btn btn-edit" onclick="openModal('pizza', ${p.id})"><i class="fas fa-edit"></i> Modifica</button>
                    <button class="btn btn-del" onclick="deleteItem('pizza', ${p.id})"><i class="fas fa-trash"></i> Elimina</button>
                </div>
            `;
            ul.appendChild(li);
        });
    });
}

function promptIncreaseCategoryPrice(slug, name) {
    const amountStr = prompt(`Di quanto vuoi aumentare il prezzo di tutte le pizze nella categoria "${name}"? (Usa il punto per i decimali, es. 0.50)`);
    if (!amountStr) return;
    
    const amount = parseFloat(amountStr.replace(',', '.'));
    if (isNaN(amount) || amount <= 0) {
        alert("Inserisci un importo valido e maggiore di zero.");
        return;
    }

    if (!confirm(`Sei sicuro di voler aumentare di €${amount.toFixed(2)} tutte le pizze della categoria "${name}"?`)) return;

    fetch('api/api_admin.php?action=increase_category_prices', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category_slug: slug, amount: amount })
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            alert('Prezzi aggiornati con successo!');
            loadPizzas();
        } else {
            alert('Errore: ' + d.message);
        }
    })
    .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function promptDecreaseCategoryPrice(slug, name) {
    const amountStr = prompt(`Di quanto vuoi ridurre il prezzo di tutte le pizze nella categoria "${name}"? (Usa il punto per i decimali, es. 0.50)`);
    if (!amountStr) return;
    
    const amount = parseFloat(amountStr.replace(',', '.'));
    if (isNaN(amount) || amount <= 0) {
        alert("Inserisci un importo valido e maggiore di zero.");
        return;
    }

    if (!confirm(`Sei sicuro di voler ridurre di €${amount.toFixed(2)} il prezzo di tutte le pizze della categoria "${name}"?`)) return;

    fetch('api/api_admin.php?action=decrease_category_prices', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category_slug: slug, amount: amount })
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            alert('Prezzi ridotti con successo!');
            loadPizzas();
        } else {
            alert('Errore: ' + d.message);
        }
    })
    .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function promptSetCategoryPrice(slug, name) {
    const amountStr = prompt(`A che valore vuoi impostare il prezzo di tutte le pizze nella categoria "${name}"? (Usa il punto per i decimali, es. 5.50)`);
    if (!amountStr) return;
    
    const amount = parseFloat(amountStr.replace(',', '.'));
    if (isNaN(amount) || amount < 0) {
        alert("Inserisci un importo valido (può essere 0 o maggiore).");
        return;
    }

    if (!confirm(`Sei sicuro di voler impostare il prezzo a €${amount.toFixed(2)} per tutte le pizze della categoria "${name}"?`)) return;

    fetch('api/api_admin.php?action=set_category_prices', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category_slug: slug, amount: amount })
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            alert('Prezzi impostati con successo!');
            loadPizzas();
        } else {
            alert('Errore: ' + d.message);
        }
    })
    .catch(e => { console.error(e); alert('Errore di rete.'); });
}

// --- INGREDIENTS ---
function loadIngredients() {
    fetch('api/api_admin.php?action=get_ingredients')
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') renderIngredients(d.data);
            else alert('Errore: ' + d.message);
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function renderIngredients(ingredients) {
    const tbody = document.getElementById('ingredients-table').querySelector('tbody');
    tbody.innerHTML = '';
    if (ingredients.length === 0) { tbody.innerHTML = '<tr><td colspan="3">Nessun ingrediente trovato.</td></tr>'; return; }
    ingredients.forEach(i => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${i.name}</td>
            <td>€ ${parseFloat(i.price).toFixed(2)}</td>
            <td>
                <button class="btn btn-edit" onclick="openModal('ingredient', ${i.id})"><i class="fas fa-edit"></i> Modifica</button>
                <button class="btn btn-del" onclick="deleteItem('ingredient', ${i.id})"><i class="fas fa-trash"></i> Elimina</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// --- PRODUCTS ---
function loadProducts() {
    fetch('api/api_admin.php?action=get_products')
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') renderProducts(d.data);
            else alert('Errore: ' + d.message);
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function renderProducts(products) {
    const tbody = document.getElementById('products-table').querySelector('tbody');
    tbody.innerHTML = '';
    if (products.length === 0) { tbody.innerHTML = '<tr><td colspan="5">Nessun prodotto trovato.</td></tr>'; return; }
    products.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${p.name}</td>
            <td>${p.category}</td>
            <td>€ ${parseFloat(p.price).toFixed(2)}</td>
            <td>${p.is_visible ? '<i class="fas fa-check-circle" style="color:#27ae60;"></i>' : '<i class="fas fa-times-circle" style="color:#e74c3c;"></i>'}</td>
            <td>
                <button class="btn btn-edit" onclick="openModal('product', ${p.id})"><i class="fas fa-edit"></i> Modifica</button>
                <button class="btn btn-del" onclick="deleteItem('product', ${p.id})"><i class="fas fa-trash"></i> Elimina</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// --- STATS ---
function loadStats() {
    fetch('api/api_admin.php?action=get_stats')
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                document.getElementById('pizzas-stat').textContent = d.data.pizzas_count;
                document.getElementById('ingredients-stat').textContent = d.data.ingredients_count;
                document.getElementById('last-update').textContent = d.data.last_update;
            }
        })
        .catch(e => console.error(e));
}

// --- TV PREVIEW ---
function toggleTvPreview(screenNum = 1) {
    const container = document.getElementById('tv-preview-container');
    const iframe = document.getElementById('tv-preview-frame');
    const title = document.getElementById('tv-preview-title');
    
    // Se c'è un argomento e container è aperto, cambiamo solo URL e titolo
    if (container.style.display === 'flex' && arguments.length > 0) {
        iframe.src = 'views/menu_screen.php?screen=' + screenNum + '&t=' + new Date().getTime();
        if(title) title.textContent = 'Anteprima TV - Schermo ' + screenNum;
        return;
    }

    if (container.style.display === 'flex') {
        container.style.display = 'none';
        iframe.src = '';
    } else {
        container.style.display = 'flex';
        iframe.src = 'views/menu_screen.php?screen=' + screenNum + '&t=' + new Date().getTime();
        if(title) title.textContent = 'Anteprima TV - Schermo ' + screenNum;
    }
}

function downloadPdf(screenNum) {
    alert("Generazione del PDF in corso per lo Schermo " + screenNum + "...\nAssicurati di non chiudere la nuova scheda finché non parte il download. Il PDF sarà un'esatta riproduzione grafica del televisore formato A4 orizzontale.");
    window.open('views/menu_screen.php?screen=' + screenNum + '&download_pdf=1', '_blank');
}

// --- IMAGE UPLOADS ---
function uploadCategoryImage(category, inputId, catId = null) {
    const input = document.getElementById(inputId);
    if (!input.files || !input.files[0]) { alert('Seleziona un file prima.'); return; }
    const formData = new FormData();
    formData.append('image', input.files[0]);
    formData.append('category', category);
    if (catId) formData.append('category_id', catId);

    fetch('api/upload_image.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => { 
            if (d.status === 'success') {
                alert('Immagine caricata!');
                location.reload(); // Ricarichiamo per aggiornare i percorsi visualizzati
            } else { 
                alert('Errore: ' + d.message); 
            } 
        })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

function loadCategoryItems() {
    const cat = document.getElementById('upload-item-category').value;
    const container = document.getElementById('item-select-container');
    const select = document.getElementById('upload-item-id');
    select.innerHTML = '';
    if (!cat) { container.style.display = 'none'; return; }
    fetch(`api/get_category_items.php?category=${cat}`)
        .then(r => r.json())
        .then(data => {
            if (data.length > 0) {
                data.forEach(item => { const opt = document.createElement('option'); opt.value = item.id; opt.textContent = item.name; select.appendChild(opt); });
                container.style.display = 'block';
            } else { alert('Nessun prodotto trovato.'); container.style.display = 'none'; }
        }).catch(e => console.error(e));
}

function uploadItemImage() {
    const cat = document.getElementById('upload-item-category').value;
    const itemId = document.getElementById('upload-item-id').value;
    const input = document.getElementById('file-item-upload');
    if (!cat || !itemId || !input.files[0]) { alert('Seleziona tutti i campi.'); return; }
    const formData = new FormData();
    formData.append('category', cat);
    formData.append('item_id', itemId);
    formData.append('image', input.files[0]);
    fetch('api/upload_item_image.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => { if (d.status === 'success') alert('Foto caricata!'); else alert('Errore: ' + d.message); })
        .catch(e => { console.error(e); alert('Errore di rete.'); });
}

// --- INIT ---
document.addEventListener('DOMContentLoaded', () => {
    const lastSection = localStorage.getItem('admin_last_section') || 'home';
    showSection(lastSection);

    const screenColsInput = document.getElementById('screenColumns');
    if (screenColsInput) {
        const updateMaxAttributes = () => {
            const maxCols = parseInt(screenColsInput.value) || 2;
            ['colClassiche', 'colStuzzicherie', 'colSpeciali', 'spanClassiche', 'spanStuzzicherie', 'spanSpeciali'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.max = maxCols;
            });
            
            // Update dynamic category row inputs
            document.querySelectorAll('.cat-col-pos, .cat-col-span').forEach(el => {
                el.max = maxCols;
            });

            // Update header text in the table
            const th = document.querySelector('.pos-table thead th:last-child');
            if (th) th.textContent = `Col Span (1-${maxCols})`;
        };
        screenColsInput.addEventListener('change', updateMaxAttributes);
        updateMaxAttributes();
    }

    // Auto-save QR URL as requested
    const qrUrlInput = document.getElementById('qrUrl');
    if (qrUrlInput) {
        qrUrlInput.addEventListener('blur', () => {
            console.log("Auto-saving QR URL...");
            saveSettings();
        });
    }
});