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

    document.getElementById('input_pdf_upload').addEventListener('change', async function(event) {
        uploadAndLoadPDF(this);
    });
}

var uploadAndLoadPDF = async function(input_upload) {
    const cache = await caches.open('pdf');
    for (let i = 0; i < input_upload.files.length; i++) {
        if(input_upload.files[i].size > maxSize) {

            alert("Le PDF ne doit pas dépasser " + Math.round(maxSize/1024/1024) + " Mo");
            break;
        }
        let filename = input_upload.files[i].name;
        let response = new Response(input_upload.files[i], { "status" : 200, "statusText" : "OK" });
        let urlPdf = '/pdf/'+filename;
        await cache.put(urlPdf, response);
        let pdfBlob = await getPDFBlobFromCache(urlPdf);
        nbPDF++;
        await loadPDF(pdfBlob, filename, nbPDF);
    }
}