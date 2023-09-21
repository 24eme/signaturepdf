function handleFileChange() {
    const fileInput = document.getElementById('input_pdf_upload');
    const compressBtn = document.getElementById('compressBtn');
    const dropdownCompressBtn = document.getElementById('dropdownMenuReference');

    if (fileInput.files.length > 0) {
        compressBtn.disabled = false;
        dropdownCompressBtn.disabled = false;
    } else {
        compressBtn.disabled = true;
        dropdownCompressBtn.disabled = true;
    }
}