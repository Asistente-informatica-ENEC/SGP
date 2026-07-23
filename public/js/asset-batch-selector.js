let batchListenersAdded = false;

function initBatchDischarge() {
    const batchDropdown = document.querySelector('#batch-discharge-normal-btn, #batch-discharge-bad-condition-btn');
    if (!batchDropdown) return;
    const dd = batchDropdown.closest('.dropdown');

    function updateButtonVisibility() {
        const checkedCount = document.querySelectorAll('input[name="selected_assets"]:checked').length;
        if (dd) dd.style.display = checkedCount > 0 ? '' : 'none';
    }

    function collectSelectedAssets() {
        return Array.from(document.querySelectorAll('input[name="selected_assets"]:checked'))
            .map(cb => ({
                id: cb.value,
                sicoin: cb.getAttribute('data-sicoin') || cb.value,
                description: cb.getAttribute('data-description') || '',
            }));
    }

    // Global listeners (once) — survive Turbo navigations via delegation
    if (!batchListenersAdded) {
        batchListenersAdded = true;

        document.addEventListener('change', function (e) {
            if (e.target.matches('input[name="selected_assets"]')) {
                updateButtonVisibility();
            }
        });

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('#batch-discharge-normal-btn, #batch-discharge-bad-condition-btn');
            if (!btn) return;
            const assets = collectSelectedAssets();
            if (assets.length === 0) { alert('Por favor selecciona al menos un bien'); return; }
            if (btn.id === 'batch-discharge-normal-btn') {
                initStepper(assets, 'normal', 'screen-modal-batchDischargeNormalModal');
            } else {
                initStepper(assets, 'bad', 'screen-modal-batchDischargeBadConditionModal');
            }
        });
    }

    updateButtonVisibility();
}

function submitDischarge(assets, obsStore, inputPrefix, modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;

    const form = modalEl.querySelector('form');
    if (!form) return;

    form.querySelectorAll('.batch-discharge-input').forEach(el => el.remove());

    assets.forEach(a => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'selected_asset_ids_' + inputPrefix + '[]';
        idInput.value = a.id;
        idInput.className = 'batch-discharge-input';
        form.appendChild(idInput);

        if (obsStore[a.id]) {
            const obsInput = document.createElement('input');
            obsInput.type = 'hidden';
            obsInput.name = 'observations_' + inputPrefix + '[' + a.id + ']';
            obsInput.value = obsStore[a.id];
            obsInput.className = 'batch-discharge-input';
            form.appendChild(obsInput);
        }
    });

    const submitBtn = document.createElement('button');
    submitBtn.type = 'submit';
    submitBtn.style.display = 'none';
    form.appendChild(submitBtn);
    submitBtn.click();
}

function initStepper(assets, prefix, modalId) {
    const container = document.getElementById(prefix + '-stepper');
    if (!container || assets.length === 0) return;

    container.style.display = '';

    const obsStore = {};
    const sicoinEl = document.getElementById(prefix + '-sicoin');
    const descEl = document.getElementById(prefix + '-desc');
    const obsTextarea = document.getElementById(prefix + '-obs');
    const counterEl = document.getElementById(prefix + '-counter');

    let prevBtn = document.getElementById(prefix + '-prev');
    let nextBtn = document.getElementById(prefix + '-next');

    if (!sicoinEl || !descEl || !obsTextarea || !counterEl || !prevBtn || !nextBtn) return;

    prevBtn.replaceWith(prevBtn.cloneNode(true));
    nextBtn.replaceWith(nextBtn.cloneNode(true));

    prevBtn = document.getElementById(prefix + '-prev');
    nextBtn = document.getElementById(prefix + '-next');

    let currentIdx = 0;
    const total = assets.length;

    function showAsset(index) {
        const asset = assets[index];
        sicoinEl.textContent = asset.sicoin;
        descEl.textContent = asset.description;
        counterEl.textContent = (index + 1) + ' / ' + total;

        obsTextarea.value = obsStore[asset.id] || '';
        obsTextarea.classList.remove('is-invalid');
        obsTextarea.focus();

        prevBtn.style.display = index > 0 ? '' : 'none';

        if (index === total - 1) {
            nextBtn.textContent = 'Confirmar';
            nextBtn.className = 'btn btn-warning ms-auto';
        } else {
            nextBtn.textContent = 'Siguiente';
            nextBtn.className = 'btn btn-primary ms-auto';
        }
    }

    function saveCurrentObservation() {
        const asset = assets[currentIdx];
        const val = obsTextarea.value.trim();
        if (val) obsStore[asset.id] = val;
        else delete obsStore[asset.id];
    }

    prevBtn.addEventListener('click', function () {
        saveCurrentObservation();
        if (currentIdx > 0) { currentIdx--; showAsset(currentIdx); }
    });

    nextBtn.addEventListener('click', function () {
        obsTextarea.value = obsTextarea.value.trim();
        if (!obsTextarea.value) {
            obsTextarea.classList.add('is-invalid');
            obsTextarea.focus();
            return;
        }
        obsTextarea.classList.remove('is-invalid');
        saveCurrentObservation();

        if (currentIdx === total - 1) {
            const inputPrefix = prefix === 'bad' ? 'bad_condition' : prefix;
            submitDischarge(assets, obsStore, inputPrefix, modalId);
            return;
        }

        currentIdx++;
        showAsset(currentIdx);
    });

    showAsset(0);
}

document.addEventListener('turbo:load', initBatchDischarge);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBatchDischarge);
} else {
    initBatchDischarge();
}
