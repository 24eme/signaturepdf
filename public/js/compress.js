async function handleFileChange() {
    document.getElementById('size_compressed').classList.add('invisible');
    document.getElementById('error_message').classList.add('d-none');
    const fileInput = document.getElementById('input_pdf_upload');

    if(fileInput.files[0].size > maxSize) {
        alert("Le PDF ne doit pas dépasser " + convertOctet2MegoOctet(maxSize) + " Mo");
        fileInput.value = null;
        return;
    }

    document.querySelector('#size_upload span').innerText = convertOctet2MegoOctet(fileInput.files[0].size);
    document.getElementById('size_upload').classList.remove('invisible');


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
        startProcessingMode(document.getElementById('compressBtn'));
        const form = e.target;
        const formData = new FormData(form);
        formData.set(e.submitter.name, e.submitter.value);
        console.log(formData);

        fetch(form.action, { method: form.method, body: formData })
        .then(async function(response) {
          if (response.ok) {
              let filename = response.headers.get('content-disposition').replace('attachment; filename=', '');
              let blob = await response.blob();

              document.querySelector('#size_compressed span').innerText = convertOctet2MegoOctet(blob.size);
              document.getElementById('size_compressed').classList.remove('invisible');
              document.querySelector('#size_compressed small span').innerText = 100 - Math.round((blob.size * 100)/ document.getElementById('input_pdf_upload').files[0].size);

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
