const inputFileElement = document.getElementById('input_pdf_upload');
const compressBtn = document.getElementById('compressBtn');

compressBtn.addEventListener('click', async () => {
    const inputPdfFile = inputFileElement.files[0];

    if (inputPdfFile) {
        console.log("ping");
    } else {
        alert('Please select a PDF file to compress.');
    }
})