/**
 * Universal Drag & Drop Module
 * Enables drag and drop file upload functionality across all pages
 */

(function() {
    'use strict';

    let dragCounter = 0;
    let dropOverlay = null;
    let initialized = false;

    /**
     * Initialize the drag and drop functionality
     */
    function init() {
        if (initialized) return;
        initialized = true;

        createDropOverlay();
        attachEventListeners();
    }

    /**
     * Create the visual overlay that appears when dragging files
     */
    function createDropOverlay() {
        dropOverlay = document.createElement('div');
        dropOverlay.id = 'drag-drop-overlay';
        dropOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(13, 110, 253, 0.1);
            backdrop-filter: blur(2px);
            border: 4px dashed #0d6efd;
            z-index: 9999;
            display: none;
            pointer-events: none;
        `;

        const dropText = document.createElement('div');
        dropText.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
            text-align: center;
            pointer-events: none;
        `;
        dropText.innerHTML = '<i class="bi bi-cloud-arrow-up" style="font-size: 4rem;"></i><br>Suelta los archivos aquí';

        dropOverlay.appendChild(dropText);
        document.body.appendChild(dropOverlay);
    }

    /**
     * Show the drop overlay
     */
    function showOverlay() {
        if (dropOverlay) {
            dropOverlay.style.display = 'block';
        }
    }

    /**
     * Hide the drop overlay
     */
    function hideOverlay() {
        if (dropOverlay) {
            dropOverlay.style.display = 'none';
        }
    }

    /**
     * Find the active file input on the current page
     */
    function getActiveFileInput() {
        // Check if we're on the organization page with files already loaded
        const pageOrganization = document.getElementById('page-organization');
        if (pageOrganization && !pageOrganization.classList.contains('d-none')) {
            // On organization page with files loaded, use the "add more files" input
            const input2 = document.getElementById('input_pdf_upload_2');
            if (input2) {
                return input2;
            }
        }

        // Check if we're on the signature page with files already loaded
        const pageSignature = document.getElementById('page-signature');
        if (pageSignature && !pageSignature.classList.contains('d-none')) {
            // On signature page, files are already loaded, don't allow drop
            return null;
        }

        // Check if we're on the metadata page with files already loaded
        const pageMetadata = document.getElementById('page-metadata');
        if (pageMetadata && !pageMetadata.classList.contains('d-none')) {
            // On metadata page, files are already loaded, don't allow drop
            return null;
        }

        // Priority order of input IDs to check for upload pages
        const inputIds = [
            'input_pdf_upload'
        ];

        for (const id of inputIds) {
            const input = document.getElementById(id);
            if (input) {
                // Check if the input's parent page is visible
                const pageUpload = document.getElementById('page-upload');
                if (pageUpload && !pageUpload.classList.contains('d-none')) {
                    return input;
                }
            }
        }

        return null;
    }

    /**
     * Check if files are valid for the given input
     */
    function areFilesValid(files, input) {
        if (!files || files.length === 0) return false;
        if (!input) return false;

        const accept = input.getAttribute('accept');
        if (!accept) return true;

        const acceptedTypes = accept.split(',').map(type => type.trim());

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            let isValid = false;

            for (const acceptedType of acceptedTypes) {
                if (acceptedType.startsWith('.')) {
                    // Extension check
                    if (file.name.toLowerCase().endsWith(acceptedType.toLowerCase())) {
                        isValid = true;
                        break;
                    }
                } else {
                    // MIME type check
                    if (file.type === acceptedType) {
                        isValid = true;
                        break;
                    }
                }
            }

            if (!isValid) return false;
        }

        return true;
    }

    /**
     * Add files to the input element
     */
    function addFilesToInput(files, input) {
        if (!input || !files || files.length === 0) return;

        const dataTransfer = new DataTransfer();

        // If input supports multiple files and already has files, add them first
        if (input.hasAttribute('multiple') && input.files.length > 0) {
            for (let i = 0; i < input.files.length; i++) {
                dataTransfer.items.add(input.files[i]);
            }
        }

        // Add new files
        for (let i = 0; i < files.length; i++) {
            dataTransfer.items.add(files[i]);
        }

        input.files = dataTransfer.files;

        // Trigger change event
        const changeEvent = new Event('change', { bubbles: true });
        input.dispatchEvent(changeEvent);
    }

    /**
     * Handle file drop
     */
    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        dragCounter = 0;
        hideOverlay();

        const files = e.dataTransfer.files;
        const input = getActiveFileInput();

        if (!input) {
            console.warn('No file input found on page');
            return;
        }

        if (!areFilesValid(files, input)) {
            alert('Por favor, selecciona archivos válidos según el tipo aceptado.');
            return;
        }

        addFilesToInput(files, input);
    }

    /**
     * Handle drag enter event
     */
    function handleDragEnter(e) {
        e.preventDefault();
        e.stopPropagation();

        dragCounter++;

        // Only show overlay if we're dragging files
        if (e.dataTransfer.types && e.dataTransfer.types.includes('Files')) {
            showOverlay();
        }
    }

    /**
     * Handle drag leave event
     */
    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();

        dragCounter--;

        if (dragCounter === 0) {
            hideOverlay();
        }
    }

    /**
     * Handle drag over event
     */
    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * Attach event listeners to document
     */
    function attachEventListeners() {
        document.addEventListener('dragenter', handleDragEnter, false);
        document.addEventListener('dragleave', handleDragLeave, false);
        document.addEventListener('dragover', handleDragOver, false);
        document.addEventListener('drop', handleDrop, false);
    }

    /**
     * Remove event listeners (for cleanup if needed)
     */
    function cleanup() {
        document.removeEventListener('dragenter', handleDragEnter, false);
        document.removeEventListener('dragleave', handleDragLeave, false);
        document.removeEventListener('dragover', handleDragOver, false);
        document.removeEventListener('drop', handleDrop, false);

        if (dropOverlay && dropOverlay.parentNode) {
            dropOverlay.parentNode.removeChild(dropOverlay);
        }

        initialized = false;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose cleanup function globally if needed
    window.dragDropCleanup = cleanup;

})();
