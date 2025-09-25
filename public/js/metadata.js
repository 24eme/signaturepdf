let pages = [];
let pdfRenderTasks = [];
let pdffile = null
let deletedMetadata = [];
let isLocalPath = false;
let hasModifications = false;

function responsiveDisplay() {
    let menu = document.getElementById('sidebarTools');
    let menuOffcanvas = new bootstrap.Offcanvas(menu);

    if(is_mobile()) {
        menu.classList.remove('show');
        menuOffcanvas.hide();
    } else {
        menuOffcanvas.show();
    }
    menu.classList.remove('d-md-block');
    menu.classList.remove('d-none');
};

async function loadPDF(pdfBlob) {
    showLoading('Loading')

    const filename = pdfBlob.name;
    const url = URL.createObjectURL(pdfBlob);
    document.title = filename + ' - ' + document.title;
    document.querySelector('#text_document_name span').innerText = filename;

    pdffile = pdfBlob

    const loadingTask = pdfjsLib.getDocument(url);
    const pdf = await loadingTask.promise;
    const metadata = await pdf.getMetadata()
    const attachments = await pdf.getAttachments();

    for(fieldKey in defaultFields) {
        addMetadata(fieldKey, null, defaultFields[fieldKey]['type'], false);
    }

    for(metaKey in metadata.info) {
        if(metaKey == "Custom" || metaKey == "PDFFormatVersion" || metaKey.match(/^Is/) || metaKey == "Trapped") {
            continue;
        }
        addMetadata(metaKey, metadata.info[metaKey], "text", false);
    }

    for(metaKey in metadata.info.Custom) {
        if(metaKey == "sha256") {
            continue;
        }

        addMetadata(metaKey, metadata.info.Custom[metaKey], "text", false);
    }

    if (attachments) {
        Object.entries(attachments).forEach(([_key, value]) => {
            if (value.filename.startsWith('factur-x') === false) {
                return
            }
            const decodedAttachment = new TextDecoder().decode(value.content)
            const parser = new DOMParser();
            const xml = parser.parseFromString(decodedAttachment, "application/xml")
            const error = xml.querySelector('parseerror')
            if (error) {
                console.log(error)
                return
            }

            const walker = xml.createTreeWalker(xml.firstChild, NodeFilter.SHOW_TEXT)
            while(walker.nextNode()) {
                const node = walker.currentNode

                const treeKey = []
                treeKey.push(node.parentNode.localName)

                let root = node.parentNode
                while (! (root.parentNode instanceof XMLDocument)) {
                    root = root.parentNode
                    treeKey.push(root.localName) // nodeName si on veut le namespace
                }

                const newInput = addMetadata(treeKey.join(' « '), node.textContent.trim(), "text", false, true)
                newInput.dataset.fromAttachment = value.filename
                newInput.disabled = true
            }
        })
    }

    for(let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
        pdf.getPage(pageNumber).then(function(page) {
            let pageIndex = (page.pageNumber - 1);
            pages[pageIndex] = page;
            pageRender(pageIndex);
        });
    }

    if(document.querySelector('.input-metadata input')) {
        document.querySelector('.input-metadata input').focus();
    } else {
        document.getElementById('input_metadata_key').focus();
    }

    endLoading();

    return loadingTask;
}

async function pageRender(pageIndex) {

  let page = pages[pageIndex];

  let viewport = page.getViewport({scale: 1});
  let sizeWidth = document.getElementById('container-pages').offsetWidth;
  let scaleWidth = sizeWidth / viewport.width;
  let viewportWidth = page.getViewport({scale: scaleWidth });

  document.documentElement.style.setProperty('--scale-factor', scaleWidth) // needed to scale the textLayer
                                                                           // to the canvas size (var used in style attribute)

  viewport = viewportWidth;

  const containerPagePDF = document.createElement('div')
  const canvasPDF = document.createElement('canvas')
  const wrapperPDF = document.createElement('div')
  const textPDF = document.createElement('div')

  document.getElementById('container-pages').appendChild(containerPagePDF)
  containerPagePDF.appendChild(wrapperPDF)
  wrapperPDF.appendChild(canvasPDF)
  wrapperPDF.appendChild(textPDF)

  const context = canvasPDF.getContext('2d')

  canvasPDF.height = viewport.height;
  canvasPDF.width = viewport.width;
  canvasPDF.classList.add('shadow-sm');

  containerPagePDF.classList.add('page')
  containerPagePDF.setAttribute('id', 'container-page-'+pageIndex)

  wrapperPDF.classList.add('canvasWrapper');
  wrapperPDF.style.position = 'relative'

  textPDF.classList.add('textLayer')

  if(pdfRenderTasks[pageIndex]) {
    pdfRenderTasks[pageIndex].cancel();
  }
  pdfRenderTasks[pageIndex] = await page.render({
    canvasContext: context,
    viewport: viewport,
  });

  pdfRenderTasks[pageIndex].promise.then(function () {
    return page.getTextContent()
  }).then(function (textContent) {
      const textLayer = new pdfjsLib.TextLayer({
          textContentSource: textContent,
          viewport: viewport,
          container: textPDF,
      });

      textLayer.render()
  })
}


window.addEventListener('message', function(event) {
    if (event.data.action === 'addMetadata' && event.data.key) {
        if (!event.data.value) {
            event.data.value = '';
        }
        console.log('addMetadata via message: '+event.data.key+'='+event.data.value);
        input = document.getElementsByName(event.data.key)[0];
        if (input) {
            input.focus();
            if (event.data.value) {
                setTimeout('document.getElementsByName("'+event.data.key+'")[0].value = "'+event.data.value+'";', 500);
            }
        }else{
            addMetadata(event.data.key, event.data.value);
            setTimeout('document.getElementsByName("'+event.data.key+'")[0].focus();', 100);
        }
        setIsChanged(true);
    }
});

function addMetadata(key, value, type, focus, forceCreation = false) {
    if (! forceCreation) {
        let input = document.querySelector('.input-metadata input[name="'+key+'"]');

        if(input && !input.value) {
            input.value = value;
        }
        if(input && focus) {
            input.focus();
        }
        if(input) {
            return input;
        }
    }

    let div = document.createElement('div');
    div.classList.add('form-floating', 'mt-3', 'input-metadata');

    input = document.createElement('input');
    input.value = value;
    input.type = type;
    input.name = key;
    input.classList.add('form-control');

    let label = document.createElement('label');
    label.innerText = key;

    let deleteButton = document.createElement('div')
    deleteButton.title = "Supprimer cette metadonnée"
    deleteButton.innerHTML = "×"
    deleteButton.classList.add('delete-metadata')

    div.appendChild(input);
    div.appendChild(label);
    div.appendChild(deleteButton);
    document.getElementById('form-metadata-container').appendChild(div);

    if(focus) {
        input.focus();
    }

    return input
}

function deleteMetadata(el) {
    if (confirm("Souhaitez-vous supprimer ce champ ?") === false) return;

    const input = el.closest('.input-metadata')
    const label = input.querySelector('label').innerText
    deletedMetadata.push(label)
    input.remove()
}

function setIsChanged(changed) {
    hasModifications = changed;
    document.getElementById('save_local').disabled = !changed;
    document.getElementById('save_mobile_local').disabled = !changed;
    document.getElementById('save_local').classList.toggle('btn-primary', changed);
    document.getElementById('save_local').classList.toggle('btn-outline-primary', !changed);
    document.getElementById('save_mobile_local').classList.toggle('btn-primary', changed);
    document.getElementById('save_mobile_local').classList.toggle('btn-outline-primary', !changed);
}

async function save() {
    const PDFDocument = window['PDFLib'].PDFDocument
    const PDFHexString = window['PDFLib'].PDFHexString
    const PDFName = window['PDFLib'].PDFName

    const pdf = await PDFDocument.load(await pdffile.arrayBuffer(), { ignoreEncryption: true, password: "", updateMetadata: false });

    deletedMetadata.forEach(function (el) {
        pdf.getInfoDict().delete(PDFName.of(el))
    });

    ([...document.getElementsByClassName('input-metadata')] || []).forEach(function (el) {
        const label = el.querySelector('label').innerText
        const input = el.querySelector('input').value

        if ('fromAttachment' in el.querySelector('input').dataset) {
            return;
        }

        pdf.getInfoDict().set(PDFName.of(label), PDFHexString.fromText(input));
    });

    const newPDF = new Blob([await pdf.save()], {type: "application/pdf"});

    if(window.location.hash && window.location.hash.match(/^\#local/)) {
        let apiUrl = window.location.origin + "/api/file/save?path=" + window.location.hash.replace(/^\#local:/, '');
        fetch(apiUrl, {
          method: 'PUT',
          body: newPDF,
        });
        return ;
    }
    download(newPDF, document.getElementById('input_pdf_upload').files[0].name)
}

function createEventsListener() {
    document.getElementById('form-metadata').addEventListener('keypress', function(e) {
        if(e.target.tagName == "INPUT") {
            setIsChanged(true);
        }
    })
    document.getElementById('form-metadata').addEventListener('change', function(e) {
        if(e.target.tagName == "INPUT") {
            setIsChanged(true);
        }
    })
    document.getElementById('form_metadata_add').addEventListener('submit', function(e) {
        let formData = new FormData(this);
        addMetadata(formData.get('metadata_key'), "", "text", true);
        setIsChanged(true);
        this.classList.add('invisible');
        setTimeout(function() { document.getElementById('form_metadata_add').classList.remove('invisible'); }, 400);
        this.reset();
        e.preventDefault();
    });
    document.getElementById('input_metadata_value').addEventListener('focus', function(e) {
        if(document.getElementById('input_metadata_key').value) {
            document.querySelector('#form_metadata_add button').click();
        }
    });
    document.addEventListener('click', function (event) {
        if (event.target.closest(".delete-metadata")) {
            deleteMetadata(event.target)
            setIsChanged(true)
        }
    })

    document.getElementById('save').addEventListener('click', async function (e) {
        startProcessingMode(this);
        await save()
        setTimeout(function() {endProcessingMode(document.getElementById('save'))}, 500);
    })
    document.getElementById('save_mobile').addEventListener('click', async function (e) {
        startProcessingMode(this);
        await save()
        setTimeout(function() {endProcessingMode(document.getElementById('save_mobile'))}, 500);
    })
    document.getElementById('save_local').addEventListener('click', async function (e) {
        startProcessingMode(this);
        await save()
        setTimeout(function() {endProcessingMode(document.getElementById('save_local')); setIsChanged(false);}, 500);
    })
    document.getElementById('save_mobile_local').addEventListener('click', async function (e) {
        startProcessingMode(this);
        await save()
        setTimeout(function() {endProcessingMode(document.getElementById('save_mobile_local')); setIsChanged(false);}, 500);
    })
}

async function pageUpload() {
    document.querySelector('body').classList.remove('bg-light');
    document.getElementById('input_pdf_upload').value = '';
    document.getElementById('page-upload').classList.remove('d-none');
    document.getElementById('page-metadata').classList.add('d-none');
    document.getElementById('input_pdf_upload').focus();
    window.addEventListener('hashchange', function() {
        window.location.reload();
    })
    document.getElementById('input_pdf_upload').addEventListener('change', async function(event) {
        if(await canUseCache()) {
            const file = document.getElementById('input_pdf_upload').files[0]
            storeFileInCache(file, file.name);
            history.pushState({}, '', `${REVERSE_PROXY_URL ? '/': ''}${REVERSE_PROXY_URL}/metadata#${file.name}`);
        }
        pageMetadata(null);
    });
}

async function pageMetadata(url) {
    document.querySelector('body').classList.add('bg-light');
    document.getElementById('page-upload').classList.add('d-none');
    document.getElementById('page-metadata').classList.remove('d-none');
    if(isLocalPath) {
        document.getElementById('save').classList.add('d-none');
        document.getElementById('save_mobile').classList.add('d-none');
        document.getElementById('save_local').classList.remove('d-none');
        document.getElementById('save_mobile_local').classList.remove('d-none');
    }

    if(url && url.match(/^cache:\/\//)) {
        await loadFileFromCache(url.replace(/^cache:\/\//, ''));
    } else if (url) {
        await loadFileFromUrl(url);
    }

    if(!document.getElementById('input_pdf_upload').files.length) {
        alert("Chargement du PDF impossible");
        document.location = '/metadata';
        return;
    }

    responsiveDisplay();
    createEventsListener();
    await convertInputFileImagesToPDF(document.getElementById('input_pdf_upload'));
    await loadPDF(document.getElementById('input_pdf_upload').files[0]).catch(function (reason) {
        console.error(reason);
    });
};


document.addEventListener('DOMContentLoaded', function () {
    if(window.location.hash && window.location.hash.match(/^\#http/)) {
        pageMetadata(window.location.hash.replace(/^\#/, ''));
    } else if(window.location.hash && window.location.hash.match(/^\#local/)) {
        isLocalPath = true;
        pageMetadata(window.location.origin + "/api/file/get?path=" + window.location.hash.replace(/^\#local:/, ''), '/metadata', window.location.hash.replace(/^\#/, ''));
    } else if(window.location.hash) {
        pageMetadata('cache:///pdf/'+window.location.hash.replace(/^\#/, ''));
    } else {
        pageUpload();
    }

    window.addEventListener('hashchange', function() {
        window.location.reload();
    })
});
