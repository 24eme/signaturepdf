function is_mobile() {
    return !(window.getComputedStyle(document.getElementById('is_mobile')).display === "none");
};

async function canUseCache() {
    try {
        cache = await caches.open('pdf');
        return true;
    } catch (e) {
        return false;
    }
};

async function loadFileFromCache(cacheUrl, pageUrl) {
    if(!await canUseCache()) {
        document.location = pageUrl;
        return false;
    }
    const cache = await caches.open('pdf');
    let responsePdf = await cache.match(cacheUrl);

    if(!responsePdf) {
        return;
    }

    let filename = cacheUrl.replace('/pdf/', '');

    let pdfBlob = await responsePdf.blob();

    let dataTransfer = new DataTransfer();
    dataTransfer.items.add(new File([pdfBlob], filename, {
        type: 'application/pdf'
    }));
    document.getElementById('input_pdf_upload').files = dataTransfer.files;
}

async function storeFileInCache() {
    let cache = await caches.open('pdf');
    let filename = document.getElementById('input_pdf_upload').files[0].name;
    let response = new Response(document.getElementById('input_pdf_upload').files[0], { "status" : 200, "statusText" : "OK" });
    await cache.put('/pdf/'+filename, response);
}

async function loadFileFromUrl(url, pageUrl, local = null) {
    history.replaceState({}, '', pageUrl);
    let response = await fetch(url);
    if(response.status != 200) {
        return;
    }
    let pdfBlob = await response.blob();
    let file_id = url.replace(/^.*\//, '');

    if(response.headers.has('content-disposition') && response.headers.get('Content-Disposition').match(/attachment; filename="/)) {
        file_id = response.headers.get('Content-Disposition').replace(/^[^"]*"/, "").replace(/"[^"]*$/, "").replace(/_signe-[0-9]+\x.pdf/, '.pdf');
    }

    if(pdfBlob.type != 'application/pdf' && pdfBlob.type != 'application/octet-stream') {
        return;
    }
    let dataTransfer = new DataTransfer();
    if (local) {
        file_id = local;
    }
    dataTransfer.items.add(new File([pdfBlob], file_id, {
        type: 'application/pdf'
    }));
    document.getElementById('input_pdf_upload').files = dataTransfer.files;
}

function storeSymmetricKeyCookie(hash, symmetricKey) {
    if (symmetricKey.length != 15) {
        console.error("Erreur taille cle symétrique.");
        return;
    }
    document.cookie = hash + "=" + symmetricKey + "; SameSite=Lax; Path=/;";
}

function getSymmetricKey(hash) {
    return getCookieValue(hash);
}

function getCookieValue (name) {
    return document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)')?.pop() || '';
}

function generateSymmetricKey() {
    const length = 15;
    const keySpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let key = '';

    for (let i = 0; i < length; ++i) {
        const randomIndex = Math.floor(Math.random() * keySpace.length);
        key += keySpace.charAt(randomIndex);
    }

    return key;
}

function generatePdfHash() {
    const length = 20;
    const keySpace = '0123456789abcdefghijklmnopqrstuvwxyz';
    let key = '';

    for (let i = 0; i < length; ++i) {
        const randomIndex = Math.floor(Math.random() * keySpace.length);
        key += keySpace.charAt(randomIndex);
    }

    return key;
}

function dataURLtoBlob(dataurl) {
    let arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    while(n--){
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], {type:mime});
}

function svgToDataUrl(svg) {

    return "data:image/svg+xml;base64," + btoa(svg);
}

function trimSvgWhitespace(svgContent) {
    if(!svgContent) {

        return null;
    }
    let svgContainer = document.createElement("div")
    svgContainer.classList.add('invisible');
    svgContainer.classList.add('position-absolute');
    svgContainer.classList.add('top-0');
    svgContainer.classList.add('start-0');
    svgContainer.style = "z-index: -1;";
    svgContainer.innerHTML = svgContent;
    document.body.appendChild(svgContainer);
    let svg = svgContainer.querySelector('svg');
    let box = svg.getBBox();
    svg.setAttribute("viewBox", [box.x, box.y, box.width, box.height].join(" "));
    svgContent = svgContainer.innerHTML;
    document.body.removeChild(svgContainer)

    return svgContent = svgContainer.innerHTML;
}
