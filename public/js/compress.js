async function handleFileChange() {
    const fileInput = document.getElementById('input_pdf_upload');

    if(fileInput.files[0].size > maxSize) {
        alert("Le PDF ne doit pas dépasser " + Math.round(maxSize/1024/1024) + " Mo");
        fileInput.value = null;
        return;
    }

    document.getElementById('error_message').classList.add('d-none');

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

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('form_compress').addEventListener('submit', async function(e) {
        document.getElementById('error_message').classList.add('d-none');
        const form = e.target;
        const formData = new FormData(form);
        startProcessingMode(document.getElementById('compressBtn'));

        fetch(form.action, { method: form.method, body: formData })
        .then(async function(response) {
          if (response.ok) {
              let filename = response.headers.get('content-disposition').replace('attachment; filename=', '');
              let blob = await response.blob();
              await download(blob, filename);
          } else {
              document.getElementById('error_message').classList.remove('d-none');
              document.getElementById('error_message').innerText = await response.text();
          }
          endProcessingMode(document.getElementById('compressBtn'));
        })

        e.preventDefault();
        return false;
    });
})
