function responsiveDisplay() {
    if(is_mobile()) {
        document.getElementById('page-organization').classList.remove('decalage-pdf-div');
        document.getElementById('container-pages').classList.remove('vh-100');
        document.getElementById('container-btn-zoom').classList.add('d-none');
    } else {
        document.getElementById('page-organization').classList.add('decalage-pdf-div');
        document.getElementById('container-pages').classList.add('vh-100');
        document.getElementById('container-btn-zoom').classList.remove('d-none');
    }
};
function isSelectionMode() {
    return document.querySelectorAll('.canvas-container .input-select:checked').length > 0;
}
function isDraggedMode() {
    return document.querySelectorAll('.canvas-container .input-drag:checked').length > 0;
}

let nbPagePerLine = 5;
if(is_mobile()) {
    nbPagePerLine = 2;
}

let nbPDF = 0;
let pages = [];
let formats = [];
let pdfRenderTasks = [];

async function loadPDF(pdfBlob, filename, pdfIndex) {
    let url = await URL.createObjectURL(pdfBlob);

    let dataTransfer = new DataTransfer();
    let i = 0;
    for (i = 0; i < document.getElementById('input_pdf').files.length; i++) {
        dataTransfer.items.add(document.getElementById('input_pdf').files[i]);
    }
    dataTransfer.items.add(new File([pdfBlob], filename, {
        type: 'application/pdf'
    }));
    document.getElementById('input_pdf').files = dataTransfer.files;
    updateListePDF();

    let pdfLetter = String.fromCharCode(96 + i+1).toUpperCase();

    let loadingTask = pdfjsLib.getDocument(url);
    await loadingTask.promise.then(function(pdf) {
        for(var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++ ) {
            pdf.getPage(pageNumber).then(function(page) {
                let pageIndex = pdfLetter + "_" + (page.pageNumber - 1);
                pages[pageIndex] = page;
                const viewportFormat = page.getViewport({ scale: 1 });
                const widthFormat = points2mm(viewportFormat.width);
                const heightFormat = points2mm(viewportFormat.height);
                const format = [widthFormat,heightFormat].sort().join('x')

                if(!formats[format]) {
                    formats[format] = []
                }
                formats[format].push(pageIndex);
                let pageTitle = trad['Page'] + ' ' + page.pageNumber + ' - ' + widthFormat + ' x ' + heightFormat + ' mm - ' + filename;
                let pageHTML = '<div class="position-relative mt-0 ms-1 me-0 mb-1 canvas-container d-flex align-items-center justify-content-center bg-transparent bg-opacity-25 border border-2 border-transparent" id="canvas-container-' + pageIndex +'" draggable="true">';
                    pageHTML += '<canvas class="canvas-pdf shadow-sm"></canvas>';
                    pageHTML += '<div title="' + trad['Select this page'] + '" class="position-absolute top-0 start-50 translate-middle-x p-2 ps-3 pe-3 mt-2 rounded-circle btn-select d-none"><i class="bi bi-check-square"></i></div>';
                    pageHTML += '<div title="' + trad['Delete this page'] + '" class="position-absolute top-50 start-0 translate-middle-y p-2 ps-3 pe-3 ms-2 rounded-circle btn-delete d-none"><i class="bi bi-trash"></i></div>';
                    pageHTML += '<div title="' + trad['Restore this page'] + '" class="position-absolute top-50 start-50 translate-middle p-2 ps-3 pe-3 rounded-circle container-resize btn-restore d-none"><i class="bi bi-recycle"></i></div>';
                    pageHTML += '<div title="' + trad['Move this page'] + '" class="position-absolute top-50 start-50 translate-middle p-2 ps-3 pe-3 rounded-circle container-resize btn-drag d-none"><i class="bi bi-arrows-move"></i></div>';
                    pageHTML += '<div title="' + trad['Move here'] + '" class="position-absolute start-50 top-50 translate-middle p-2 ps-4 pe-4 container-resize btn-drag-here d-none"><i class="bi bi-arrows-expand-vertical"></i></div>';
                    pageHTML += '<div title="' + trad['Turn this page'] + '" class="position-absolute top-50 end-0 translate-middle-y p-2 ps-3 pe-3 me-2 rounded-circle container-rotate btn-rotate d-none"><i class="bi bi-arrow-clockwise"></i></div>';
                    pageHTML += '<div title="' + trad['Download this page'] + '" class="position-absolute bottom-0 start-50 translate-middle-x p-2 ps-3 pe-3 mb-3 rounded-circle btn-download d-none"><i class="bi bi-download"></i></div>';
                    pageHTML += '<p class="page-title position-absolute text-center w-100 ps-2 pe-2 pb-0 pt-0 mb-1 bg-white opacity-75 d-none" style="bottom: -4px; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">' + pageTitle + '</p>';
                    pageHTML += '<input form="form_pdf" class="checkbox-page d-none" role="switch" type="checkbox" checked="checked" value="'+pdfLetter+page.pageNumber+'" />';
                    pageHTML += '<input type="hidden" class="input-rotate" value="0" id="input_rotate_'+pageIndex+'" />';
                    pageHTML += '<input type="checkbox" class="input-select d-none" value="'+pdfLetter+page.pageNumber+'" id="input_select_'+pageIndex+'" />';
                    pageHTML += '<input type="checkbox" class="input-hover d-none" value="'+pdfLetter+page.pageNumber+'" id="input_select_'+pageIndex+'" />';
                    pageHTML += '<input type="checkbox" class="input-drag d-none" value="'+pdfLetter+page.pageNumber+'" id="input_drag_'+pageIndex+'" />';
                pageHTML += '</div>';

                document.getElementById('container-pages').insertAdjacentHTML('beforeend', pageHTML);

                document.querySelector('#input_rotate_'+pageIndex).value = page.rotate;

                let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
                canvasContainer.title = pageTitle;
                canvasContainer.addEventListener('click', function(e) {
                    canvasContainer.querySelector('.btn-select').click();
                });
                canvasContainer.addEventListener('mouseenter', function(e) {
                    if(is_mobile()) {
                        return false;
                    }
                    this.querySelector('input[type=checkbox].input-hover').checked = true;
                    updatePageState(this);
                });
                canvasContainer.addEventListener('mouseleave', function(e) {
                    this.querySelector('input[type=checkbox].input-hover').checked = false;
                    updatePageState(this);
                });
                canvasContainer.addEventListener('dragstart', function(e) {
                    if(is_mobile()) {
                        return false;
                    }
                    if(isDraggedMode()) {
                        return false;
                    }
                    this.querySelector('.container-resize').classList.add('d-none');
                    this.querySelector('.canvas-pdf').classList.add('shadow-lg');
                    this.querySelector('.canvas-pdf').style.border = '2px dashed #777';
                    e.dataTransfer.setData('element', this.id);
                    this.style.opacity = 0.4;
                });
                canvasContainer.addEventListener('dragend', function(e) {
                    this.querySelector('.container-resize').classList.remove('d-none');
                    this.querySelector('.canvas-pdf').classList.remove('shadow-lg');
                    this.querySelector('.canvas-pdf').style.removeProperty('border');
                    this.style.opacity = 1;
                    updatePageState(this);
                });
                canvasContainer.addEventListener('dragover', function(e) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    let pdfOver = this;
                    let pdfMoving = document.querySelector('#'+e.dataTransfer.getData('element'));

                    if(pdfOver.id == pdfMoving.id) {

                        return;
                    }

                    let leftRight = false;
                    let topBottom = false;

                    if(pdfOver.offsetTop < pdfMoving.offsetTop) {
                        topBottom = 'top';
                    }

                    if(pdfOver.offsetTop > pdfMoving.offsetTop) {
                        topBottom = 'bottom';
                    }

                    if(pdfOver.offsetLeft > pdfMoving.offsetLeft) {
                        leftRight = 'right';
                    }

                    if(pdfOver.offsetLeft < pdfMoving.offsetLeft) {
                        leftRight = 'left';
                    }

                    if (leftRight == 'left' || topBottom == 'top') {
                        pdfOver.insertAdjacentElement('beforebegin', pdfMoving);
                    }

                    if (leftRight == 'right' || topBottom == 'bottom') {
                        pdfOver.insertAdjacentElement('afterend', pdfMoving);
                    }

                    return false;
                });
                canvasContainer.querySelector('.btn-delete').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDeletePage(this.parentNode);
                });
                canvasContainer.querySelector('.btn-restore').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDeletePage(this.parentNode);
                });
                canvasContainer.querySelector('.btn-select').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleSelectPage(this.parentNode);
                });
                canvasContainer.querySelector('.btn-drag').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleSelectPage(this.parentNode);
                    document.getElementById('btn_drag_select').click();
                });
                canvasContainer.querySelector('.btn-drag-here').addEventListener('click', function(e) {
                    e.stopPropagation();
                    let btn = e.target;
                    if(!e.target.classList.contains('btn-drag-here')) {
                        btn = e.target.parentElement;
                    }
                    if(e.layerX <= btn.offsetWidth / 2) {
                        movePagesDragged(this.parentNode, 'left');
                    } else {
                        movePagesDragged(this.parentNode, 'right');
                    }
                });
                canvasContainer.querySelector('.btn-download').addEventListener('click', async function(e) {
                    e.stopPropagation();
                    startProcessingMode(canvasContainer.querySelector('.btn-download'));
                    let container = this.parentNode;
                    let pageValue = container.querySelector('.checkbox-page').value;
                    let orientation = container.querySelector('.input-rotate').value;
                    if(orientation) {
                        pageValue = pageValue + "-" + orientation;
                    }
                    document.querySelector('#input_pages').value = pageValue;
                    await save(pageValue);
                    endProcessingMode(canvasContainer.querySelector('.btn-download'));
                });
                canvasContainer.querySelector('.btn-rotate').addEventListener('click', function(e) {
                    e.stopPropagation();
                    let inputRotate = this.parentNode.querySelector('.input-rotate');
                    inputRotate.value = (parseInt(inputRotate.value) + 90) % 360;
                    pageRender(pageIndex);
                })

                pageRender(pageIndex);
            });
        }
    }, function (reason) {
        console.error(reason);
    });

    return loadingTask;
};

function pageRenderAll() {
    for(pageIndex in pages) {
        pageRender(pageIndex);
    }
}

async function pageRender(pageIndex) {
  let scrollWidth = 12;
  if(is_mobile()) {
      scrollWidth = -4;
  }
  let page = pages[pageIndex];

  let rotation = parseInt(document.querySelector('#input_rotate_'+pageIndex).value);
  let viewport = page.getViewport({scale: 1, rotation: rotation});
  let sizeWidth = Math.floor((document.getElementById('container-pages').offsetWidth - (8*(nbPagePerLine+1)) - scrollWidth) / nbPagePerLine);
  let sizeHeight = sizeWidth * 1.25;
  let scaleWidth = sizeWidth / viewport.width;
  let scaleHeight = sizeHeight / viewport.height;
  let viewportWidth = page.getViewport({scale: scaleWidth, rotation: rotation});
  let viewportHeight = page.getViewport({scale: scaleHeight, rotation: rotation});

  if(viewportWidth.height > sizeWidth) {
      viewport = viewportHeight;
  } else {
      viewport = viewportWidth;
  }

  let canvasContainer = document.getElementById('canvas-container-' + pageIndex);
  canvasContainer.style.height = (sizeHeight + 4) + "px";
  canvasContainer.style.width = (sizeWidth + 4) + "px";
  let canvasPDF = canvasContainer.querySelector('.canvas-pdf');
  let context = canvasPDF.getContext('2d');
  canvasPDF.height = viewport.height;
  canvasPDF.width = viewport.width;

  if(pdfRenderTasks[pageIndex]) {
    pdfRenderTasks[pageIndex].cancel();
  }
  pdfRenderTasks[pageIndex] = await page.render({
    canvasContext: context,
    viewport: viewport,
  });

  updateFormats();
}

function getFileIndex(page) {

    return page.id.replace('canvas-container-', '').replace(/_.*$/, '');
}

function getFilesStats() {
    let files = [];
    document.querySelectorAll('.canvas-container').forEach(function(page) {
        let fileIndex = getFileIndex(page);
        if(!files[fileIndex]) {
            files[fileIndex] = { nbPage: 0, nbPageSelected: 0, nbPageDeleted: 0};
        }

        if(isPageDeleted(page)) {
            files[fileIndex].nbPageDeleted++;
        } else {
            files[fileIndex].nbPage++;
        }

        if(isPageSelected(page)) {
            files[fileIndex].nbPageSelected++;
        }
    });

    return files;
}

function updateListePDF() {
    document.querySelector('#list_pdf').innerHTML = "";
    let nbFiles = document.querySelector('#input_pdf').files.length;
    for (var i = 0; i < nbFiles; i++) {
        let pdfLetter = getLetter(i);
        const pdfFile = document.querySelector('#input_pdf').files.item(i);
        document.querySelector('#list_pdf').insertAdjacentHTML('beforeend', '<li id="file_' + pdfLetter + '" class="list-group-item small ps-2 pe-5" title="'+decodeURI(pdfFile.name)+'" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden;"><i class="bi bi-files"></i><label class="ms-2" style="cursor: pointer; display: inline;" for="file_' + pdfLetter + '_name">'+decodeURI(pdfFile.name)+'</label> <input id="file_' + pdfLetter + '_name" class="form-check-input float-end position-absolute file-list-checkbox" type="checkbox" /> </li>');
        let fileItem = document.querySelector('#file_' + pdfLetter);
        fileItem.querySelector('input[type=checkbox]').addEventListener('change', function(e) {
            document.querySelectorAll('.canvas-container').forEach(function(page) {
                if(getFileIndex(page) == pdfLetter && !isPageDeleted(page)) {
                    selectPage(page, e.target.checked);
                }
            });
            updateGlobalState();
        });
        document.querySelector('#liste_pdf_titre_mobile').innerText = decodeURI(pdfFile.name);
        document.querySelector('#btn_liste_pdf_bar span').innerText = nbFiles;
        if(nbFiles > 1) {
            document.querySelector('#liste_pdf_titre_mobile').innerText = nbFiles + ' documents PDF';
        }
    }
    updateGlobalState();
}

function updateFormats() {
    const selectFormat = document.querySelector('#select_paper_format');
    const selectFormatOptionCurrent = document.querySelector('#select_paper_format_current');
    let formatsLabel = [];
    for(format in formats) {
        if(document.querySelector('#select_paper_format option[value="'+format+'"]')) {
            formatsLabel.push(document.querySelector('#select_paper_format option[value="'+format+'"]').innerText);
        } else {
            formatsLabel.push(format.replace('x', ' x ') + ' mm');
        }
    }

    selectFormatOptionCurrent.innerText = formatsLabel.join(', ');
    document.querySelector('#printable_paper_size_infos').innerText = selectFormat.selectedOptions[0].text;
    document.querySelector('#printable_paper_size_infos').classList.add('text-muted');
    document.querySelector('#printable_paper_size_infos').classList.remove('fw-bold');
    if(selectFormat.value) {
        document.querySelector('#printable_paper_size_infos').classList.remove('text-muted');
        document.querySelector('#printable_paper_size_infos').classList.add('fw-bold');
    }
    document.querySelector('#printable_paper_size_infos').title = document.querySelector('#printable_paper_size_infos').innerText;

    document.querySelector('#printable_formatting_infos').innerText = null;
    if(document.querySelector('#select_formatting').value) {
        document.querySelector('#printable_formatting_infos').innerText = document.querySelector('#select_formatting').selectedOptions[0].text;
    }
}

function getPagesSelected() {
    let pages = [];
    document.querySelectorAll('.canvas-container .input-select:checked').forEach(function(item) {
        pages[item.parentNode.id.replace('canvas-container-', '')] = item.parentNode;
    });

    return pages;
}

function selectPage(page, state) {
    page.querySelector('input[type=checkbox].input-select').checked = state;
    updatePageState(page);
}

function toggleSelectPage(page) {
    if(isPageDeleted(page)) {
        return;
    }

    if(!isPageDragged(page) && isDraggedMode()) {
        return;
    }

    if(isPageDragged(page)) {
        document.getElementById('btn_drag_select').click();
        return;
    }

    selectPage(page, !isPageSelected(page));

    updateGlobalState();
}

function isPageSelected(page) {

    return page.querySelector('input[type=checkbox].input-select').checked;
}

function dragPage(page, state) {
    page.querySelector('input[type=checkbox].input-drag').checked = state;
    updatePageState(page);
}

function toggleDragPage(page) {
    dragPage(page, !isPageDragged(page));
    updateGlobalState();
    document.querySelectorAll('.canvas-container').forEach(function(page) {
        updatePageState(page);
    });
}

function isPageDragged(page) {

    return page.querySelector('input[type=checkbox].input-drag').checked;
}

function movePagesDragged(pageHere, position) {
    document.querySelectorAll('.canvas-container .input-drag:checked').forEach(function(item) {
        let page = item.parentNode;
        if(position == 'right') {
            pageHere.insertAdjacentElement('afterend', page);
        } else {
            pageHere.insertAdjacentElement('beforebegin', page);
        }
    });
    document.getElementById('btn_drag_select').click();
}

function toggleDeletePage(page) {
    deletePage(page, isPageDeleted(page))
    updateGlobalState();
}

function deletePage(page, state) {
    page.querySelector('input[type=checkbox].checkbox-page').checked = state;
    page.querySelector('input[type=checkbox].input-select').checked = false;
    updatePageState(page);
}

function isPageDeleted(page) {
    return !page.querySelector('input[type=checkbox].checkbox-page').checked;
}

function isPageHover(page) {
    return page.querySelector('input[type=checkbox].input-hover').checked;
}

function updatePageState(page) {
    page.classList.remove('border-primary', 'shadow-sm', 'bg-primary', 'border-secondary', 'bg-secondary');
    page.classList.add('border-transparent', 'bg-transparent');
    page.querySelector('.canvas-pdf').style.opacity = '1';
    page.querySelector('.canvas-pdf').style.zIndex = 'inherit';
    page.querySelector('.canvas-pdf').classList.add('shadow-sm');
    page.querySelector('.canvas-pdf').classList.remove('shadow');
    page.querySelector('.btn-rotate').classList.add('d-none');
    page.querySelector('.btn-download').classList.add('d-none');
    page.querySelector('.btn-delete').classList.add('d-none');
    page.querySelector('.btn-select').classList.add('d-none');
    page.querySelector('.btn-select').classList.remove('text-primary');
    page.querySelector('.btn-drag').classList.add('d-none');
    page.querySelector('.btn-drag-here').classList.add('d-none');
    page.querySelector('.btn-restore').classList.add('d-none');
    page.querySelector('.page-title').classList.add('d-none');
    page.querySelector('.canvas-pdf').classList.remove('opacity-50');
    page.classList.remove('page-dragged');
    page.draggable = true;

    if(isSelectionMode()) {
        page.draggable = false;
    }

    if(isPageDeleted(page)) {
        page.querySelector('.canvas-pdf').style.opacity = '0.15';
    }

    if(isPageHover(page) && !isPageDeleted(page) && !isPageDragged(page) && !isPageSelected(page) && !isDraggedMode()) {
        page.querySelector('.page-title').classList.remove('d-none');
        page.classList.add('border-secondary', 'bg-secondary');
        page.classList.remove('border-transparent', 'bg-transparent');
        page.querySelector('.btn-select').classList.remove('d-none')
    }

    if(isPageHover(page) && !isPageDeleted(page) && !isPageDragged(page) && !isPageSelected(page) && !isDraggedMode() && !isSelectionMode()) {
        page.querySelector('.btn-rotate').classList.remove('d-none');
        page.querySelector('.btn-download').classList.remove('d-none');
        page.querySelector('.btn-delete').classList.remove('d-none');
        page.querySelector('.btn-drag').classList.remove('d-none');
    }

    if(isPageHover(page) && isPageDeleted(page) && !isDraggedMode()) {
        page.querySelector('.btn-restore').classList.remove('d-none');
    }

    if(is_mobile() && isPageDeleted(page) && !isDraggedMode()) {
        page.querySelector('.btn-restore').classList.remove('d-none');
    }

    if(isPageSelected(page)) {
        page.querySelector('.page-title').classList.remove('d-none');
        page.classList.add('border-primary', 'shadow-sm', 'bg-primary');
        page.classList.remove('border-transparent', 'bg-transparent', 'border-secondary', 'bg-secondary');
        page.querySelector('.btn-select').classList.add('text-primary');
        page.querySelector('.btn-select').classList.remove('d-none');
    }

    if(isPageDragged(page)) {
        page.classList.add('page-dragged');
        page.querySelector('.canvas-pdf').classList.remove('shadow-sm');
        page.querySelector('.canvas-pdf').classList.add('shadow');
    }

    if(!isPageDragged(page) && !isPageDeleted(page) && isDraggedMode()) {
        page.querySelector('.canvas-pdf').classList.add('opacity-50');
        page.querySelector('.btn-drag-here').classList.remove('d-none');
    }
}

function updateFilesState() {
    let filesStats = getFilesStats();
    for(fileIndex in filesStats) {
        let checkbox = document.querySelector('#file_'+fileIndex+' input[type=checkbox]');
        let fileStat = filesStats[fileIndex];
        checkbox.checked = (fileStat.nbPageSelected > 0 && fileStat.nbPageSelected == fileStat.nbPage);
        checkbox.indeterminate = (fileStat.nbPageSelected > 0 && fileStat.nbPageSelected < fileStat.nbPage);
        document.querySelector('#file_'+fileIndex+' label').classList.remove('text-primary');
        if(fileStat.nbPageSelected > 0) {
            document.querySelector('#file_'+fileIndex+' label').classList.add('text-primary');
        }
    }
}

function updateGlobalState() {
    updateFilesState();
    if(!is_mobile()) {
        document.querySelector('#container-btn-zoom').classList.remove('d-none');
    }
    document.querySelector('#container_btn_select').classList.add('opacity-50');
    document.querySelector('#container_btn_select').classList.remove('border-primary');
    document.querySelector('#container_btn_select .card-header').classList.remove('bg-primary', 'text-white');
    document.querySelector('#container_btn_select .card-header').classList.add('text-muted');
    document.querySelectorAll('#container_btn_select .card-body button').forEach(function(button) {
        button.classList.add('btn-outline-secondary');
        button.classList.remove('btn-outline-primary');
        button.setAttribute('disabled', 'disabled');
    });
    document.querySelector('#container_btn_select .card-header span').innerText = "0";
    document.querySelector('#container_btn_select .card-footer').classList.add('d-none');
    document.querySelector('#top_bar_action').classList.remove('d-none');
    document.querySelector('#top_bar_action_selection').classList.add('d-none');
    document.querySelector('#bottom_bar_action').classList.remove('d-none');
    document.querySelector('#bottom_bar_action_selection').classList.add('d-none');
    document.querySelector('#save').classList.remove('d-none');
    document.querySelector('#save_select').classList.add('d-none');

    if(isSelectionMode()) {
        document.querySelector('#container_btn_select .card-header span').innerText = document.querySelectorAll('.canvas-container .input-select:checked').length;
        document.querySelector('#top_bar_action_selection_recap_nb_pages').innerText = document.querySelectorAll('.canvas-container .input-select:checked').length;
        document.querySelector('#container_btn_select').classList.remove('opacity-50');
        document.querySelector('#container_btn_select').classList.add('border-primary');
        document.querySelector('#container_btn_select .card-header').classList.remove('text-muted');
        document.querySelector('#container_btn_select .card-header').classList.add('bg-primary', 'text-white');
        document.querySelectorAll('#container_btn_select .card-body button').forEach(function(button) {
            button.classList.add('btn-outline-primary');
            button.classList.remove('btn-outline-secondary');
            button.removeAttribute('disabled');
        });
        document.querySelector('#container_btn_select .card-footer').classList.remove('d-none');
        document.querySelectorAll('.canvas-container .btn-add').forEach(function(button) {
            button.classList.remove('d-none');
        });
        document.querySelector('#top_bar_action_selection').classList.remove('d-none');
        document.querySelector('#top_bar_action').classList.add('d-none');
        document.querySelector('#bottom_bar_action_selection').classList.remove('d-none');
        document.querySelector('#bottom_bar_action').classList.add('d-none');
        document.querySelector('#save').classList.add('d-none');
        document.querySelector('#save_select').classList.remove('d-none');
    }
}

async function uploadAndLoadPDF(input_upload) {
    showLoading('Loading')
    await convertInputFileImagesToPDF(input_upload)
    for (let i = 0; i < input_upload.files.length; i++) {
        nbPDF++;
        await loadPDF(input_upload.files[i], input_upload.files[i].name, nbPDF);
    }
    endLoading()
}

async function saveAll() {
    let order = [];
    let selectionMode = isSelectionMode();

    document.querySelectorAll('.canvas-container').forEach(function(canvasContainer) {
        let checkbox = canvasContainer.querySelector('.checkbox-page');
        if(selectionMode) {
            checkbox = canvasContainer.querySelector('.input-select');
        }
        let inputRotate = canvasContainer.querySelector('.input-rotate');
        let pageValue = "";
        if(checkbox.checked) {
            pageValue = checkbox.value;
        }
        let orientation = inputRotate.value;
        if(pageValue && orientation) {
            pageValue = pageValue + "-" + orientation;
        }
        if(pageValue) {
            order.push(pageValue);
        }
    });

    document.querySelector('#input_pages').value = order.join(',');

    await save(order.join(','));
}

async function save(order) {
    const PDFDocument = window['PDFLib'].PDFDocument
    const Rotation = window['PDFLib'].Rotation
    const pdf = await PDFDocument.load(await document.querySelector('#input_pdf').files.item(0).arrayBuffer(), { ignoreEncryption: true, password: "", updateMetadata: false });

    let filename = "";
    let pages = [];
    const pagesOrganize = order.split(',');

    for (let i = 0; i < document.querySelector('#input_pdf').files.length; i++) {
        if(filename) {
            filename += '_';
        }
        filename += document.querySelector('#input_pdf').files.item(i).name.replace(/\.pdf$/, '');
        const indices = [];
        const letter = getLetter(i);
        for(let k in pagesOrganize) {
            if(pagesOrganize[k].startsWith(letter)) {
                indices.push(parseInt(pagesOrganize[k].split('-')[0].replace(letter, '')) - 1)
            }
        }
        let pdfPages = [];
        if(i == 0) {
            pdfPages = await pdf.getPages();
            for(j in indices) {
                pages[letter+(indices[j]+1).toString()] = pdfPages[indices[parseInt(j)]];
            }
            for(let i in pdf.getPages()) {
                pdf.removePage(0);
            }
        } else {
            const pdfFile = await PDFDocument.load(await document.querySelector('#input_pdf').files.item(i).arrayBuffer(), { ignoreEncryption: true, password: "", updateMetadata: false });
            pdfPages = await pdf.copyPages(pdfFile, indices);
            for(j in pdfPages) {
                pages[letter+(indices[j]+1).toString()] = pdfPages[j];
            }
        }
    }

    for(let i in pagesOrganize) {
        const pageOrganize = pagesOrganize[i].split('-')[0];
        const rotation = pagesOrganize[i].split('-')[1];
        const pdfPage = pages[pageOrganize];
        const format = document.querySelector('#select_paper_format').value;
        if(rotation) {
            pdfPage.setRotation(window['PDFLib'].degrees(parseInt(rotation)));
        }
        if(format) {
            let width = mm2points(parseInt(format.split("x")[0]));
            let height = mm2points(parseInt(format.split("x")[1]));

            if(pdfPage.getHeight() > pdfPage.getWidth()) {
                resizePage(pdfPage, Math.min(height, width), Math.max(height, width));
            } else {
                resizePage(pdfPage, Math.max(height, width), Math.min(height, width));
            }
        }
        pdf.addPage(pdfPage);
    }

    if(document.querySelector('#select_formatting').value == "booklet") {
        const orgaPages = [];
        const nbPages = Math.ceil(pdf.getPages().length / 4) * 2;

        const pageWidth = pdf.getPages()[0].getWidth();
        const pageHeight = pdf.getPages()[0].getHeight();

        const pdfBooklet = await window['PDFLib'].PDFDocument.create();
        for(let i = nbPages; i > 0; i--) {
            orgaPages.push([i, 2 * nbPages - i + 1]);
        }
        for(pages of orgaPages.reverse()) {
            await merge2Pages(pdfBooklet, pdf.getPages()[pages[0] - 1], pdf.getPages()[pages[1] - 1], pageWidth, pageHeight)
        }
        let newPDF = new Blob([await pdfBooklet.save()], {type: "application/pdf"});

        await download(newPDF, filename+".pdf");
        return;
    }

    cleanPDF(pdf);
    let newPDF = new Blob([await pdf.save()], {type: "application/pdf"});
    await download(newPDF, filename+".pdf");
    await storeFileInCache(newPDF, filename+'.pdf');
}

function mm2points(mm) {

    return mm * 72 / 25.4;
}

function points2mm(points) {

    return Math.round(points * 25.4 / 72);
}

async function merge2Pages(pdf, pageA, pageB, pageWidth, pageHeight) {
    const newPageWidth =  Math.max(pageWidth, pageHeight);
    const newPageHeight =  Math.min(pageWidth, pageHeight);
    const page = pdf.addPage([newPageWidth, newPageHeight]);
    if(pageA) {
        const pageEmbeddedA = await pdf.embedPage(pageA, {
            left: 0,
            bottom: 0,
            right: pageA.getWidth(),
            top: pageA.getHeight(),
        });

        const pageEmbeddedDimsA = pageEmbeddedA.scale((newPageWidth / 2) / pageA.getWidth());
        page.drawPage(pageEmbeddedA, {
          ...pageEmbeddedDimsA,
          x: 0,
          y: 0,
        });
    }
    if(pageB) {
        const pageEmbeddedB = await pdf.embedPage(pageB, {
            left: 0,
            bottom: 0,
            right: pageB.getWidth(),
            top: pageB.getHeight(),
        });

        const pageEmbeddedDimsB = pageEmbeddedB.scale((newPageWidth / 2) / pageB.getWidth());
        page.drawPage(pageEmbeddedB, {
          ...pageEmbeddedDimsB,
          x: newPageWidth / 2,
          y: 0,
        });
    }
}

function resizePage(page, newWidth, newHeight) {
    const oldWidth = page.getSize().width;
    const oldHeight = page.getSize().height;

    // Calcul des facteurs d’échelle
    const scaleX = newWidth / oldWidth;
    const scaleY = newHeight / oldHeight;

    // Utiliser le facteur le plus petit pour garder les proportions
    const scale = Math.min(scaleX, scaleY);

    // Définir la nouvelle taille
    page.setSize(newWidth, newHeight);

    // Calculer le décalage pour centrer le contenu
    const offsetX = (newWidth - (oldWidth * scale)) / 2;
    const offsetY = (newHeight - (oldHeight * scale)) / 2;

    // Appliquer la transformation au contenu
    page.scaleContent(scale, scale);
    page.translateContent(offsetX, offsetY);
}

function cleanPDF(pdf) {
    let pagesRef = [];
    for(page of pdf.getPages()) {
        pagesRef.push(page.ref.tag)
    }

    let hasPageDeleted = false;
    //Supprime les objets pages qui on été supprimés
    for(d of pdf.pageMap.entries()) {
        for(p of d) {
            if(p.ref) {
                if(!pagesRef.includes(p.ref.tag)) {
                    hasPageDeleted = true;
                    pdf.context.indirectObjects.delete(window['PDFLib'].PDFRef.of(p.ref.objectNumber));
                }
            }
        }
    }

    if(! hasPageDeleted) {
        return;
    }

    //Supprime les objets non utilisés tant qu'il y en a
    let tagsToDelete = [];
    do {
        tagsToDelete = [];
        let tags = [];
        if(pdf.context.trailerInfo.Info) {
            tags.push(pdf.context.trailerInfo.Info.tag);
        }
        tags.push(pdf.context.trailerInfo.Root.tag);
        tags.concat(getPDFTags(pdf.catalog));
        pdf.context.indirectObjects.forEach(function(object) {
            tags = tags.concat(getPDFTags(object));
        });
        for(p of pdf.getPages()) {
            tags = tags.concat(getPDFTags(p.node));
        }
        for(o of pdf.context.enumerateIndirectObjects()) {
            for(e of o) {
                if(e.tag && !tags.includes(e.tag)) {
                    tagsToDelete.push(e.tag);
                    //console.log(e.objectNumber);
                    //console.log(pdf.context.indirectObjects.get(window['PDFLib'].PDFRef.of(e.objectNumber)));
                    pdf.context.indirectObjects.delete(window['PDFLib'].PDFRef.of(e.objectNumber))
                }
            }
        }
    } while(tagsToDelete.length);
}

function getPDFTags(node) {
    let tags = [];

    if(node.tag) {
        tags.push(node.tag);
    }
    if(node.array) {
        for(item of node.array) {
            tags = tags.concat(getPDFTags(item));
        }
    }
    if(node.dict) {
        for(dict of node.dict.entries()) {
            for(object of dict) {
                tags = tags.concat(getPDFTags(object));
            }
        }
    }
    return tags;
}

function createEventsListener() {
    document.getElementById('save_select_mobile').addEventListener('click', async function(event) {
        event.preventDefault();
        startProcessingMode(document.getElementById('save_select_mobile'));
        await saveAll();
        endProcessingMode(document.getElementById('save_select_mobile'));
    });
    document.getElementById('btn_extract_select').addEventListener('click', async function(event) {
        event.preventDefault();
        startProcessingMode(document.getElementById('btn_extract_select'));
        await saveAll();
        endProcessingMode(document.getElementById('btn_extract_select'));
    });
    document.getElementById('save_select').addEventListener('click', async function(event) {
        event.preventDefault();
        startProcessingMode(document.getElementById('save_select'));
        await saveAll();
        endProcessingMode(document.getElementById('save_select'));
    });
    document.getElementById('save').addEventListener('click', async function(e) {
        e.preventDefault();
        startProcessingMode(this);
        await saveAll();
        endProcessingMode(this);
    });
    document.getElementById('save_mobile').addEventListener('click', async function(event) {
        event.preventDefault();
        startProcessingMode(this);
        await saveAll();
        endProcessingMode(this);
    });
    document.getElementById('input_pdf_upload_2').addEventListener('change', async function(event) {
        await uploadAndLoadPDF(this);
        this.value = '';
    });
    document.getElementById('btn-zoom-decrease').addEventListener('click', function(event) {
        nbPagePerLine++;
        pageRenderAll();
    });
    document.getElementById('btn-zoom-increase').addEventListener('click', function(event) {
        nbPagePerLine--;
        pageRenderAll();
    });
    document.getElementById('btn_cancel_select_footer').addEventListener('click', function(event) {
        document.getElementById('btn_cancel_select').click();
    });
    document.getElementById('btn_cancel_select_mobile').addEventListener('click', function(event) {
        document.getElementById('btn_cancel_select').click();
    });
    document.getElementById('btn_cancel_select').addEventListener('click', function(event) {
        if(isDraggedMode()) {
            document.getElementById('btn_drag_select').click();
        }
        document.querySelectorAll('.input-select:checked').forEach(function(input) {
            input.parentNode.querySelector('.btn-select').click();
        });
    });
    document.getElementById('btn_delete_select_mobile').addEventListener('click', function(event) {
        document.getElementById('btn_delete_select').click();
    });
    document.getElementById('btn_delete_select').addEventListener('click', function(event) {
        if(isDraggedMode()) {
            document.getElementById('btn_drag_select').click();
        }
        let pages = getPagesSelected();
        for(index in pages) {
            deletePage(pages[index]);
        }
        updateGlobalState();
    });
    document.getElementById('btn_rotate_select_mobile').addEventListener('click', function(event) {
        document.getElementById('btn_rotate_select').click();
    });
    document.getElementById('btn_rotate_select').addEventListener('click', function(event) {
        let pages = getPagesSelected();
        for(index in pages) {
            let inputRotate = pages[index].querySelector('.input-rotate');
            inputRotate.value = (parseInt(inputRotate.value) + 90) % 360;
            pageRender(index);
        }
    });
    document.getElementById('btn_drag_select').addEventListener('click', function(event) {
        let pages = getPagesSelected();
        for(index in pages) {
            toggleDragPage(pages[index]);
        }
        this.classList.toggle('active');
        document.getElementById('btn_drag_select_mobile').classList.toggle('active');
    });
    document.getElementById('btn_drag_select_mobile').addEventListener('click', function(event) {
        document.getElementById('btn_drag_select').click();
    });
    document.querySelector('#btn_liste_pdf').addEventListener('click', function(event) {
        bootstrap.Modal.getOrCreateInstance(document.querySelector('#modalFichier')).show();
        document.querySelector('#modalFichier .modal-body').insertAdjacentElement('afterbegin', document.querySelector('#list_pdf'));
    });
    document.querySelector('#btn_liste_pdf_bar').addEventListener('click', function(event) {
        document.querySelector('#btn_liste_pdf').click();
    });
    document.querySelector('body').addEventListener('click', function(event) {
        if(!event.originalTarget.classList.contains('offcanvas-header') && !event.originalTarget.classList.contains('offcanvas-body') && event.originalTarget.id != 'container-pages' && event.originalTarget.id != 'sidebarTools' && event.originalTarget.id != 'sidebarToolsLabel' && event.originalTarget.id != 'btn_container') {
            return;
        }
        document.getElementById('btn_cancel_select').click();
    });
    document.querySelector('#select_paper_format').addEventListener('change', function(event) {
        updateFormats();
    });
    document.querySelector('#select_formatting').addEventListener('change', function(event) {
        updateFormats();
    });
}

async function uploadFromUrl(url) {
    showLoading('Download')
    history.replaceState({}, '', '/organization');
    var response = await fetch(url);
    if(response.status != 200) {
        return;
    }
    var pdfBlob = await response.blob();

    if(pdfBlob.type != 'application/pdf' && pdfBlob.type != 'application/octet-stream') {
        return;
    }
    let dataTransfer = new DataTransfer();
    let filename = url.replace(/^.*\//, '');
    dataTransfer.items.add(new File([pdfBlob], filename, {
        type: 'application/pdf'
    }));
    document.getElementById('input_pdf_upload').files = dataTransfer.files;
    document.getElementById('input_pdf_upload').dispatchEvent(new Event("change"));
    endLoading('Download')
}

async function pageUpload() {
    document.querySelector('body').classList.remove('bg-light');
    document.getElementById('input_pdf_upload').value = '';
    document.getElementById('page-upload').classList.remove('d-none');
    document.getElementById('page-organization').classList.add('d-none');
    document.getElementById('input_pdf_upload').focus();
    document.getElementById('input_pdf_upload').addEventListener('change', async function(event) {
        uploadAndLoadPDF(this);
        pageOrganization();
    });
}

async function pageOrganization() {
    document.querySelector('body').classList.add('bg-light');
    document.getElementById('page-upload').classList.add('d-none');
    document.getElementById('page-organization').classList.remove('d-none');
    responsiveDisplay();
    createEventsListener();
};

document.addEventListener('DOMContentLoaded', function () {
    if(window.location.hash && window.location.hash.match(/^\#http/)) {
        let hashUrl = window.location.hash.replace(/^\#/, '');
        pageUpload();
        uploadFromUrl(hashUrl);
    } else if (window.location.hash && canUseCache()) {
        pageUpload()
        loadFileFromCache('/pdf/'+window.location.hash.replace(/^\#/, ''));
    } else {
        pageUpload();
    }
    window.addEventListener('hashchange', function() {
        window.location.reload();
    })

    if (hasTouch()) {
        disabledHoverStyle();
    }
});
