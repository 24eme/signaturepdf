async function handleFileChange() {
    const fileInput = document.getElementById('input_pdf_upload');
    await convertInputFileImagesToPDF(fileInput);

    if(fileInput.files[0].size > maxSize) {
        alert("Le PDF ne doit pas dépasser " + Math.round(maxSize/1024/1024) + " Mo");
        fileInput.value = null;
        return;
    }

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
