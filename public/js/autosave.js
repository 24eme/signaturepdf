/**
 * Auto-Save and Auto-Restore Module for Signature PDF
 * Automatically saves canvas state and PDF to localStorage for recovery
 */

const AutoSave = (() => {
    let saveTimeout;
    let isAutoSavingEnabled = true;
    let sessionKey = null;
    let pdfBlob = null;
    let debounceDelay = 1500; // milliseconds
    let maxStorageAttempts = 3;
    let hasUnsavedChanges = false;

    /**
     * Initialize the auto-save system
     */
    function init(enabled = true, debounce = 1500) {
        isAutoSavingEnabled = enabled;
        debounceDelay = debounce;
        sessionKey = generateSessionKey();

        // Attempt to restore previous session silently (no prompt)
        const hasSavedPDF = checkForBackup();
        if (hasSavedPDF) {
            console.log('[AutoSave] Found saved PDF, will restore on page load...');
            // Restore will happen when PDF is requested
        } else {
            // Clear any expired sessions
            cleanupOldSessions();
        }

        // Setup warning when user tries to close the window
        setupUnloadWarning();

        console.log('[AutoSave] Initialized. Session:', sessionKey, 'Enabled:', isAutoSavingEnabled);
    }

    /**
     * Setup warning when user tries to close the browser window/tab
     * Only show if there are unsaved changes in localStorage
     */
    function setupUnloadWarning() {
        window.addEventListener('beforeunload', (event) => {
            // Check if there are unsaved changes
            if (hasUnsavedChanges && checkForBackup()) {
                const message = 'You have unsaved annotations. Please download your PDF before closing!';
     */
                function generateSessionKey(pdfName) {
                    // If a filename is provided, use it; otherwise try to read from DOM
                    const name = pdfName || document.getElementById('text_document_name')?.querySelector('span')?.innerText || 'unsigned_pdf';
                    const sanitized = name.replace(/[^a-z0-9]/gi, '_').toLowerCase();
                    const timestamp = new Date().toISOString().split('T')[0]; // Use date only to group same-day sessions
                    return `autosave_${sanitized}_${timestamp}`;
                }

                /**
                 * Get the full storage key with index
                 */
                function getStorageKey(suffix = '') {
                    return sessionKey + (suffix ? '_' + suffix : '');
                }

                /**
                 * Check if a backup exists for current session
                 */
                function checkForBackup() {
                    try {
                        const backupData = localStorage.getItem(getStorageKey('canvas'));
                        return backupData !== null;
                    } catch (e) {
                        console.warn('[AutoSave] Error checking backup:', e);
                        return false;
                    }
                }



                /**
                 * Debounced save function - only saves after user stops making changes
                 */
                function debouncedSave() {
                    if (!isAutoSavingEnabled) {
                        return;
                    }

                    clearTimeout(saveTimeout);
                    showAutoSaveIndicator('saving');

                    saveTimeout = setTimeout(() => {
                        saveState();
                    }, debounceDelay);
                }

                /**
                 * Save the complete canvas state and PDF to localStorage
                 * This function will export an annotated PDF (merging canvas overlays)
                 * and persist it as base64 so the modified PDF can be restored and downloaded.
                 */
                async function saveState() {
                    if (!isAutoSavingEnabled) {
                        return;
                    }

                    try {
                        const canvasObjects = serializeCanvasObjects();
                        const stateData = {
                            canvasObjects: canvasObjects,
                            svgCollections: svgCollections || [],
                            pdfState: {
                                currentScale: currentScale,
                                pageCount: canvasEditions ? canvasEditions.length : 0,
                                watermark: document.querySelector('input[name=watermark]')?.value || '',
                                watermarkColor: document.querySelector('#watermark-color-picker')?.value || '#828282',
                                flatten: document.querySelector('input[name=flatten]')?.checked || false
                            },
                            timestamp: Date.now(),
                            version: 1
                        };

                        // Store canvas state
                        const stateJson = JSON.stringify(stateData);
                        const totalSize = calculateStorageSize(stateJson);

                        localStorage.setItem(getStorageKey('canvas'), stateJson);
                        localStorage.setItem(getStorageKey('timestamp'), stateData.timestamp.toString());

                        // Export annotated PDF (merge canvases onto original PDF) when possible
                        if (pdfBlob && typeof window.PDFLib !== 'undefined' && window.canvasEditions && window.canvasEditions.length > 0) {
                            try {
                                const annotatedBase64 = await exportAnnotatedPDF(pdfBlob);
                                if (annotatedBase64) {
                                    // Save under session-specific key
                                    try {
                                        localStorage.setItem(getStorageKey('pdfBlob'), annotatedBase64);
                                        localStorage.setItem(getStorageKey('pdfName'), pdfBlob.name || 'document.pdf');
                                    } catch (e) {
                                        // Attempt to save into IndexedDB and store pointer
                                        try {
                                            const idbKey = getStorageKey('pdfBlob');
                                            idbPut(idbKey, annotatedBase64).then(() => {
                                                try { localStorage.setItem(getStorageKey('pdfBlobIdb'), idbKey); } catch (_) { }
                                            }).catch(err => console.warn('[AutoSave] idbPut failed for session pdfBlob', err));
                                        } catch (err) {
                                            console.warn('[AutoSave] Failed to save session pdfBlob to localStorage or idb', err);
                                        }
                                    }
                                    // Also save a global latest-edited copy so refresh/restore can find it reliably
                                    try {
                                        localStorage.setItem('autosave_last_edited_pdf', annotatedBase64);
                                        localStorage.setItem('autosave_last_edited_pdf_ts', stateData.timestamp.toString());
                                        localStorage.setItem('autosave_last_edited_pdf_name', pdfBlob.name || 'document.pdf');
                                    } catch (e) {
                                        // Save to IndexedDB and set pointer
                                        const globalKey = 'autosave_last_edited_pdf';
                                        idbPut(globalKey, annotatedBase64).then(() => {
                                            try { localStorage.setItem('autosave_last_edited_pdf_idb', globalKey); } catch (_) { }
                                        }).catch(err => console.warn('[AutoSave] idbPut failed for global pdf', err));
                                    }
                                    console.log('[AutoSave] Annotated PDF saved to localStorage (session + global)');
                                }
                            } catch (e) {
                                console.warn('[AutoSave] Could not export annotated PDF:', e);
                                // fallback: save original PDF blob
                                const reader = new FileReader();
                                reader.onload = function (event) {
                                    try {
                                        localStorage.setItem(getStorageKey('pdfBlob'), event.target.result);
                                    } catch (err) {
                                        console.warn('[AutoSave] Could not save original PDF blob:', err);
                                    }
                                };
                                reader.readAsDataURL(pdfBlob);
                            }
                        } else if (pdfBlob) {
                            // No PDFLib or no canvases: store original PDF
                            const reader = new FileReader();
                            reader.onload = function (event) {
                                try {
                                    localStorage.setItem(getStorageKey('pdfBlob'), event.target.result);
                                } catch (err) {
                                    console.warn('[AutoSave] Could not save original PDF blob:', err);
                                }
                            };
                            reader.readAsDataURL(pdfBlob);
                        }

                        hasUnsavedChanges = true; // Mark as having unsaved changes for close warning
                        showAutoSaveIndicator('saved');
                        console.log('[AutoSave] State saved. Size:', (totalSize / 1024).toFixed(2), 'KB');
                        return true;
                    } catch (e) {
                        showAutoSaveIndicator('failed');
                        console.error('[AutoSave] Error saving state:', e);
                        return false;
                    }
                }

                /**
                 * Serialize canvas objects from fabric.js canvases
                 */
                function serializeCanvasObjects() {
                    if (!window.canvasEditions || canvasEditions.length === 0) {
                        return {};
                    }

                    const serialized = {};
                    canvasEditions.forEach((canvas, index) => {
                        try {
                            serialized[index] = {
                                objects: canvas.toJSON(['svgOrigin', 'type', 'height', 'width']),
                                width: canvas.width,
                                height: canvas.height,
                                viewportTransform: canvas.viewportTransform
                            };
                        } catch (e) {
                            console.warn('[AutoSave] Error serializing canvas', index, ':', e);
                            serialized[index] = { objects: [] };
                        }
                    });
                    return serialized;
                }

                /**
                 * Export an annotated PDF by drawing each canvas overlay onto the corresponding PDF page.
                 * Returns a data URL `data:application/pdf;base64,...` when successful.
                 */
                async function exportAnnotatedPDF(originalBlob) {
                    try {
                        console.log('[AutoSave] exportAnnotatedPDF: starting');
                        const arrayBuffer = await originalBlob.arrayBuffer();
                        const pdfDoc = await window.PDFLib.PDFDocument.load(arrayBuffer);

                        for (let i = 0; i < pdfDoc.getPageCount(); i++) {
                            const page = pdfDoc.getPage(i);
                            const canvas = window.canvasEditions && window.canvasEditions[i];
                            if (!canvas) continue;

                            // Get PNG image from canvas
                            const dataUrl = canvas.toDataURL('image/png');
                            const base64Data = dataUrl.split(',')[1];
                            const pngBytes = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
                            try {
                                const pngImage = await pdfDoc.embedPng(pngBytes);
                                const { width, height } = page.getSize();
                                // PDF coordinate origin is bottom-left; draw image to cover full page
                                page.drawImage(pngImage, {
                                    x: 0,
                                    y: 0,
                                    width: width,
                                    height: height
                                });
                            } catch (embedErr) {
                                console.warn('[AutoSave] Could not embed PNG for page', i, embedErr);
                            }
                        }

                        const pdfBytes = await pdfDoc.save();
                        let binary = '';
                        const len = pdfBytes.length;
                        for (let i = 0; i < len; i++) {
                            binary += String.fromCharCode(pdfBytes[i]);
                        }
                        const base64 = btoa(binary);
                        console.log('[AutoSave] exportAnnotatedPDF: done');
                        return 'data:application/pdf;base64,' + base64;
                    } catch (e) {
                        console.warn('[AutoSave] exportAnnotatedPDF failed:', e);
                        throw e;
                    }
                }

                /**
                 * Compress canvas objects by keeping only essential properties
                 */
                function compressCanvasObjects(canvasObjects) {
                    const compressed = {};
                    for (const [index, data] of Object.entries(canvasObjects)) {
                        if (!data.objects) continue;
                        compressed[index] = {
                            objects: data.objects.map(obj => ({
                                type: obj.type,
                                left: obj.left,
                                top: obj.top,
                                width: obj.width,
                                height: obj.height,
                                angle: obj.angle,
                                scaleX: obj.scaleX,
                                scaleY: obj.scaleY,
                                fill: obj.fill,
                                stroke: obj.stroke,
                                svgOrigin: obj.svgOrigin,
                                text: obj.text
                            })),
                            width: data.width,
                            height: data.height
                        };
                    }
                    return compressed;
                }

                /**
                 * Calculate storage size of data
                 */
                function calculateStorageSize(jsonString) {
                    return new Blob([jsonString]).size;
                }

                /**
                 * Restore canvas state from localStorage
                 */
                function restoreState() {
                    try {
                        const stateData = localStorage.getItem(getStorageKey('canvas'));
                        if (!stateData) {
                            console.log('[AutoSave] No backup to restore');
                            return false;
                        }

                        const state = JSON.parse(stateData);
                        console.log('[AutoSave] Restoring state from', new Date(state.timestamp).toLocaleString());

                        // Restore canvas objects
                        if (state.canvasObjects && window.canvasEditions && canvasEditions.length > 0) {
                            restoreCanvasObjects(state.canvasObjects);
                        }

                        // Restore SVG collections
                        if (state.svgCollections && Array.isArray(state.svgCollections)) {
                            window.svgCollections = state.svgCollections;
                            if (typeof displaysSVG === 'function') {
                                displaysSVG();
                            }
                        }

                        // Restore watermark
                        if (state.pdfState) {
                            const watermarkInput = document.querySelector('input[name=watermark]');
                            if (watermarkInput && state.pdfState.watermark) {
                                watermarkInput.value = state.pdfState.watermark;
                            }

                            const watermarkColor = document.querySelector('#watermark-color-picker');
                            if (watermarkColor && state.pdfState.watermarkColor) {
                                watermarkColor.value = state.pdfState.watermarkColor;
                            }

                            const flattenInput = document.querySelector('input[name=flatten]');
                            if (flattenInput) {
                                flattenInput.checked = state.pdfState.flatten;
                            }

                            if (typeof updateWatermark === 'function') {
                                updateWatermark();
                            }
                        }

                        // Mark as unmodified since we're restoring
                        if (typeof setIsChanged === 'function') {
                            setIsChanged(true); // Mark as changed so user can save
                        }

                        storeCollections();
                        showRestoreSuccessMessage();
                        return true;

                    } catch (e) {
                        console.error('[AutoSave] Error restoring state:', e);
                        return false;
                    }
                }

                /**
                 * Get the saved PDF blob from localStorage
                 * Returns a Blob if found, null otherwise
                 */
                async function getSavedPDFBlob() {
                    try {
                        const base64PDF = localStorage.getItem(getStorageKey('pdfBlob'));
                        if (!base64PDF || base64PDF === 'absent') {
                            // Try to locate any saved PDF under different session keys first
                            const found = findSavedPDFEntry();
                            if (found && found.pdfBlob) return found.pdfBlob;
                            // Next, check for pointer to IndexedDB entry
                            const idbKey = localStorage.getItem(getStorageKey('pdfBlobIdb'));
                            if (idbKey) {
                                const stored = await idbGet(idbKey).catch(e => null);
                                if (stored) {
                                    const byteCharacters = atob(stored.split(',')[1]);
                                    const bytes = Uint8Array.from(byteCharacters, c => c.charCodeAt(0));
                                    return new Blob([bytes], { type: 'application/pdf' });
                                }
                            }
                            // Lastly, fallback to global annotated PDF
                            const globalRef = getGlobalAnnotatedPDF();
                            if (globalRef && globalRef.base64) {
                                const gbase = globalRef.base64;
                                const byteCharacters = atob(gbase.split(',')[1]);
                                const bytes = Uint8Array.from(byteCharacters, c => c.charCodeAt(0));
                                return new Blob([bytes], { type: 'application/pdf' });
                            }
                            return null;
                        }

                        // Convert base64 back to Blob
                        const byteCharacters = atob(base64PDF.split(',')[1]);
                        const byteNumbers = new Array(byteCharacters.length);
                        for (let i = 0; i < byteCharacters.length; i++) {
                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        const byteArray = new Uint8Array(byteNumbers);
                        const pdfBlob = new Blob([byteArray], { type: 'application/pdf' });

                        console.log('[AutoSave] Retrieved saved PDF from localStorage');
                        return pdfBlob;
                    } catch (e) {
                        console.warn('[AutoSave] Error retrieving saved PDF:', e);
                        return null;
                    }
                }

                /**
                 * Search localStorage for any autosave PDF entries and return the most recent one.
                 * Returns an object { pdfBlob, pdfName, sessionKey } or null if none found.
                 */
                function findSavedPDFEntry() {
                    try {
                        let latest = null;
                        for (let i = 0; i < localStorage.length; i++) {
                            const key = localStorage.key(i);
                            if (!key) continue;
                            if (!key.endsWith('_pdfBlob')) continue;
                            const base64PDF = localStorage.getItem(key);
                            if (!base64PDF || base64PDF === 'absent') continue;
                            const session = key.replace(/_pdfBlob$/, '');
                            const tsKey = session + '_timestamp';
                            const ts = parseInt(localStorage.getItem(tsKey) || '0', 10) || 0;
                            if (!latest || ts > latest.ts) {
                                // convert base64 to blob
                                try {
                                    const byteCharacters = atob(base64PDF.split(',')[1]);
                                    const byteNumbers = new Array(byteCharacters.length);
                                    for (let j = 0; j < byteCharacters.length; j++) {
                                        byteNumbers[j] = byteCharacters.charCodeAt(j);
                                    }
                                    const byteArray = new Uint8Array(byteNumbers);
                                    const blob = new Blob([byteArray], { type: 'application/pdf' });
                                    const name = localStorage.getItem(session + '_pdfName') || 'restored.pdf';
                                    latest = { pdfBlob: blob, pdfName: name, sessionKey: session, ts: ts };
                                } catch (err) {
                                    console.warn('[AutoSave] Error converting stored PDF for', key, err);
                                }
                            }
                        }
                        return latest;
                    } catch (e) {
                        console.warn('[AutoSave] Error searching for saved PDF entries:', e);
                        return null;
                    }
                }

                /* IndexedDB fallback helpers */
                function openIDB() {
                    return new Promise((resolve, reject) => {
                        try {
                            const request = indexedDB.open('signaturepdf_autosave', 1);
                            request.onupgradeneeded = (e) => {
                                const db = e.target.result;
                                if (!db.objectStoreNames.contains('files')) {
                                    db.createObjectStore('files', { keyPath: 'key' });
                                }
                            };
                            request.onsuccess = (e) => resolve(e.target.result);
                            request.onerror = (e) => reject(e.target.error);
                        } catch (e) {
                            reject(e);
                        }
                    });
                }

                function idbPut(key, value) {
                    return openIDB().then(db => new Promise((resolve, reject) => {
                        try {
                            const tx = db.transaction(['files'], 'readwrite');
                            const store = tx.objectStore('files');
                            const req = store.put({ key, value, ts: Date.now() });
                            req.onsuccess = () => resolve(true);
                            req.onerror = (e) => reject(e.target.error);
                        } catch (err) {
                            reject(err);
                        }
                    }));
                }

                function idbGet(key) {
                    return openIDB().then(db => new Promise((resolve, reject) => {
                        try {
                            const tx = db.transaction(['files'], 'readonly');
                            const store = tx.objectStore('files');
                            const req = store.get(key);
                            req.onsuccess = (e) => resolve(e.target.result ? e.target.result.value : null);
                            req.onerror = (e) => reject(e.target.error);
                        } catch (err) {
                            reject(err);
                        }
                    }));
                }

                function idbDelete(key) {
                    return openIDB().then(db => new Promise((resolve, reject) => {
                        try {
                            const tx = db.transaction(['files'], 'readwrite');
                            const store = tx.objectStore('files');
                            const req = store.delete(key);
                            req.onsuccess = () => resolve(true);
                            req.onerror = (e) => reject(e.target.error);
                        } catch (err) {
                            reject(err);
                        }
                    }));
                }

                /**
                 * Return the global last edited annotated PDF saved by AutoSave, if any.
                 * Returns { base64, ts, name } or null.
                 */
                function getGlobalAnnotatedPDF() {
                    try {
                        const base64 = localStorage.getItem('autosave_last_edited_pdf');
                        if (!base64) return null;
                        const ts = parseInt(localStorage.getItem('autosave_last_edited_pdf_ts') || '0', 10) || 0;
                        const name = localStorage.getItem('autosave_last_edited_pdf_name') || 'restored.pdf';
                        return { base64, ts, name };
                    } catch (e) {
                        console.warn('[AutoSave] Error reading global annotated PDF:', e);
                        return null;
                    }
                }

                async function getGlobalAnnotatedPDFAsync() {
                    try {
                        const sync = getGlobalAnnotatedPDF();
                        if (sync) return sync;
                        const idbKey = localStorage.getItem('autosave_last_edited_pdf_idb');
                        if (idbKey) {
                            const stored = await idbGet(idbKey).catch(e => null);
                            if (stored) {
                                const ts = parseInt(localStorage.getItem('autosave_last_edited_pdf_ts') || '0', 10) || 0;
                                const name = localStorage.getItem('autosave_last_edited_pdf_name') || 'restored.pdf';
                                return { base64: stored, ts, name };
                            }
                        }
                        return null;
                    } catch (e) {
                        console.warn('[AutoSave] Error reading global annotated PDF (async):', e);
                        return null;
                    }
                }

                /**
                 * Restore canvas objects from serialized data
                 */
                function restoreCanvasObjects(canvasObjects) {
                    try {
                        for (const [index, data] of Object.entries(canvasObjects)) {
                            const canvasIndex = parseInt(index);
                            if (!canvasEditions[canvasIndex] || !data.objects) {
                                continue;
                            }

                            const canvas = canvasEditions[canvasIndex];

                            // Clear existing objects
                            canvas.clear();

                            // Load objects from JSON
                            canvas.loadFromJSON(data, function () {
                                canvas.renderAll();
                                console.log('[AutoSave] Restored objects for canvas', canvasIndex);
                            });
                        }
                    } catch (e) {
                        console.warn('[AutoSave] Error restoring canvas objects:', e);
                    }
                }

                /**
                 * Show auto-save indicator in UI
                 */
                function showAutoSaveIndicator(status = 'saving') {
                    let indicator = document.getElementById('autosave-indicator');

                    // Create indicator if it doesn't exist
                    if (!indicator) {
                        indicator = document.createElement('div');
                        indicator.id = 'autosave-indicator';
                        indicator.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 9999;
                font-family: system-ui, sans-serif;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                display: none;
            `;
                        document.body.appendChild(indicator);
                    }

                    // Update status display
                    if (status === 'saving') {
                        indicator.textContent = '💾 Auto-saving...';
                        indicator.style.backgroundColor = '#fff3cd';
                        indicator.style.color = '#856404';
                        indicator.style.display = 'block';
                    } else if (status === 'saved') {
                        indicator.textContent = '✓ Auto-saved';
                        indicator.style.backgroundColor = '#d4edda';
                        indicator.style.color = '#155724';
                        indicator.style.display = 'block';

                        // Hide after 3 seconds
                        setTimeout(() => {
                            indicator.style.display = 'none';
                        }, 3000);
                    } else if (status === 'failed') {
                        indicator.textContent = '✗ Auto-save failed';
                        indicator.style.backgroundColor = '#f8d7da';
                        indicator.style.color = '#721c24';
                        indicator.style.display = 'block';
                    }
                }

                /**
                 * Show message that restore was successful
                 */
                function showRestoreSuccessMessage() {
                    const message = document.createElement('div');
                    message.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 16px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            z-index: 10000;
            font-family: system-ui, sans-serif;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        `;
                    message.textContent = '✓ Your previous work has been restored from backup';
                    document.body.appendChild(message);

                    setTimeout(() => {
                        message.remove();
                    }, 5000);
                }

                /**
                 * Store the PDF blob for potential recovery
                 */
                function setPDFBlob(blob) {
                    if (!blob) return;
                    const oldSession = sessionKey;
                    pdfBlob = blob;
                    // Ensure session key is generated from the actual filename to avoid mismatches
                    try {
                        const newSession = generateSessionKey(blob.name || 'document.pdf');
                        if (oldSession !== newSession) {
                            // Migrate any existing data from old session to new session
                            try {
                                const keysToMove = ['canvas', 'svg', 'timestamp', 'pdfBlob', 'pdfName'];
                                for (const k of keysToMove) {
                                    const oldKey = oldSession ? oldSession + '_' + k : null;
                                    const newKey = newSession + '_' + k;
                                    if (oldKey && localStorage.getItem(oldKey) !== null && localStorage.getItem(newKey) === null) {
                                        localStorage.setItem(newKey, localStorage.getItem(oldKey));
                                        localStorage.removeItem(oldKey);
                                    }
                                }
                            } catch (migErr) {
                                console.warn('[AutoSave] Migration error:', migErr);
                            }
                        }
                        sessionKey = newSession;
                        // Store the PDF filename reference
                        localStorage.setItem(getStorageKey('pdfName'), blob.name || 'document.pdf');
                    } catch (e) {
                        console.warn('[AutoSave] Could not store PDF reference:', e);
                    }
                }

                /**
                 * Regenerate the session key after PDF is loaded
                 * This ensures the key matches the actual PDF filename, not the placeholder
                 */
                function regenerateSessionKey() {
                    const oldKey = sessionKey;
                    sessionKey = generateSessionKey();

                    // If the key changed, we need to migrate old data to new key
                    if (oldKey !== sessionKey) {
                        try {
                            // Migrate canvas state
                            const canvasData = localStorage.getItem(getStorageKey('canvas'));
                            if (canvasData) {
                                // We need to temporarily use old key to migrate
                                const tempKey = sessionKey;
                                sessionKey = oldKey;
                                const oldCanvas = localStorage.getItem(getStorageKey('canvas'));
                                const oldSvg = localStorage.getItem(getStorageKey('svg'));
                                const oldPdf = localStorage.getItem(getStorageKey('pdfName'));
                                sessionKey = tempKey;

                                if (oldCanvas) localStorage.setItem(getStorageKey('canvas'), oldCanvas);
                                if (oldSvg) localStorage.setItem(getStorageKey('svg'), oldSvg);
                                if (oldPdf) localStorage.setItem(getStorageKey('pdfName'), oldPdf);

                                // Clear old key
                                sessionKey = oldKey;
                                localStorage.removeItem(getStorageKey('canvas'));
                                localStorage.removeItem(getStorageKey('svg'));
                                localStorage.removeItem(getStorageKey('pdfName'));
                            }
                        } catch (e) {
                            console.warn('[AutoSave] Could not migrate session data:', e);
                        }
                        console.log('[AutoSave] Session key updated:', oldKey, '→', sessionKey);
                    }
                }

                /**
                 * Clear the current session backup
                 */
                function clearBackup() {
                    try {
                        localStorage.removeItem(getStorageKey('canvas'));
                        localStorage.removeItem(getStorageKey('timestamp'));
                        localStorage.removeItem(getStorageKey('pdfBlob'));
                        localStorage.removeItem(getStorageKey('pdfName'));
                        console.log('[AutoSave] Backup cleared for session:', sessionKey);
                    } catch (e) {
                        console.warn('[AutoSave] Error clearing backup:', e);
                    }
                }

                /**
                 * Cleanup old sessions from localStorage
                 */
                function cleanupOldSessions(daysToKeep = 3) {
                    try {
                        const now = Date.now();
                        const keepTime = daysToKeep * 24 * 60 * 60 * 1000;
                        let removed = 0;

                        // Iterate through all localStorage keys
                        for (let i = localStorage.length - 1; i >= 0; i--) {
                            const key = localStorage.key(i);

                            // Find autosave timestamp keys
                            if (key && key.startsWith('autosave_') && key.endsWith('_timestamp')) {
                                const timestamp = parseInt(localStorage.getItem(key));

                                if (now - timestamp > keepTime) {
                                    // Extract session key and remove all related entries
                                    const sessionKey = key.replace('_timestamp', '');
                                    const prefixes = ['_canvas', '_timestamp', '_pdfBlob', '_pdfName'];

                                    prefixes.forEach(prefix => {
                                        localStorage.removeItem(sessionKey + prefix);
                                    });
                                    removed++;
                                }
                            }
                        }

                        if (removed > 0) {
                            console.log('[AutoSave] Cleaned up', removed, 'old sessions');
                        }
                    } catch (e) {
                        console.warn('[AutoSave] Error cleaning up sessions:', e);
                    }
                }

                /**
                 * Disable auto-save (for example, before signing/submitting)
                 */
                function disable() {
                    isAutoSavingEnabled = false;
                    clearTimeout(saveTimeout);
                    console.log('[AutoSave] Disabled');
                }

                /**
                 * Enable auto-save
                 */
                function enable() {
                    isAutoSavingEnabled = true;
                    console.log('[AutoSave] Enabled');
                }

                /**
                 * Public API
                 */
                return {
                    init: init,
                    save: debouncedSave,
                    saveNow: saveState,
                    restore: restoreState,
                    clear: clearBackup,
                    setPDFBlob: setPDFBlob,
                    getSavedPDFBlob: getSavedPDFBlob,
                    findSavedPDFEntry: findSavedPDFEntry,
                    getGlobalAnnotatedPDF: getGlobalAnnotatedPDF,
                    getGlobalAnnotatedPDFAsync: getGlobalAnnotatedPDFAsync,
                    exportAnnotatedPDF: exportAnnotatedPDF,
                    regenerateSessionKey: regenerateSessionKey,
                    disable: disable,
                    enable: enable,
                    getSessionKey: () => sessionKey,
                    isEnabled: () => isAutoSavingEnabled,
                    checkForBackup: checkForBackup
                };
            })();
