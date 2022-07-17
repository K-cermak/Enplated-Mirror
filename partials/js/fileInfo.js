function loadFile(fileName) {
    document.querySelector('.selectedInfo h4').innerHTML = "Selected file info:";
    document.querySelector('.selectedInfo div').innerHTML = "<h6>Loading info, please wait</h6>";

    //send request to api.php with fileName as post fileInfo
    let request = new XMLHttpRequest(); 
    request.open('POST', 'api.php', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.send('fileInfo=' + currentFolder + fileName.querySelector("h6").innerText);
    request.onload = function () {
        if (request.status >= 200 && request.status < 400) {
            let data = JSON.parse(request.responseText);
            let img = "";

            if (data.extension == "jpg" || data.extension == "png" || data.extension == "jpeg" || data.extension == "svg") {
                img = `<div class="text-center"><img src="` + webUrl + fileName.querySelector("h6").innerText + `" class="img-fluid mt-2 mb-4" alt="` + data.name + `" style="max-width:100%;max-height:250px;"></div>`;
            }

            document.querySelector('.selectedInfo div').innerHTML = `
            <h6><strong>Name:</strong> ` + data.name + `</h6>
            `+ img +`
            <h6><strong>Path on server:</strong> ` + currentFolder + fileName.querySelector("h6").innerText + `</h6>
            <h6 class="mt-4"><strong>Web URL:</strong> ` + webUrl + fileName.querySelector("h6").innerText + ` </h6>
            
            <h6 class="mt-5"><strong>File size:</strong> ` + calcSize(data.size) + ` MB (` + data.size + ` B)</h6>
            <h6><strong>File type:</strong> ` + data.type + `</h6>
            <h6><strong>File extension:</strong> ` + data.extension + `</h6>

            <h6 class="mt-5"><strong>Last modified:</strong> ` + data.date + `</h6>
            <h6><strong>File permission:</strong> ` + data.permissions + `</h6>

            <div class="text-center mt-4 mb-5">
                <button type="button" class="btn btn-primary mt-2 mx-1 downloadButton" onclick="downloadFile('` + webUrl + fileName.querySelector("h6").innerText + `', this)"><i class="bi bi-download"></i> Download File</button>
                <button type="button" class="btn btn-secondary mt-2 mx-1" onclick="copyLink('` + webUrl + fileName.querySelector("h6").innerText + `', this)"><i class="bi bi-link-45deg"></i> Copy Link</button>
                <button type="button" class="btn btn-dark mt-2 mx-1" onclick="openLink('` + webUrl + fileName.querySelector("h6").innerText + `')"><i class="bi bi-box-arrow-up-right"></i> Open</button>
            </div>
            `;

        } else {
            document.querySelector('.selectedInfo div').innerHTML = "<h6>Error loading file info</h6>";
        }
    };
}

function loadFolder(folderName) {
    document.querySelector('.selectedInfo h4').innerHTML = "Selected folder info:";
    document.querySelector('.selectedInfo div').innerHTML = "<h6>Loading info, please wait</h6>";

    //send request to api.php with folderName as post fileInfo
    let request = new XMLHttpRequest(); 
    request.open('POST', 'api.php', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.send('folderInfo=' + currentFolder + folderName.querySelector("h6").innerText);
    request.onload = function () {
        if (request.status >= 200 && request.status < 400) {
            let data = JSON.parse(request.responseText);
            document.querySelector('.selectedInfo div').innerHTML = `
            <h6><strong>Name:</strong> ` + data.name + `</h6>
            <h6><strong>Path on server:</strong> ` + currentFolder + folderName.querySelector("h6").innerText + `</h6>
            <h6 class="mt-4"><strong>Web URL:</strong> ` + webUrl + folderName.querySelector("h6").innerText + ` </h6>
            
            <h6 class="mt-5"><strong>Folder size:</strong> ` + calcSize(data.size) + ` MB (` + data.size + ` B)</h6>
            <h6><strong>Number of files in dir:</strong> ` + data.files + `</h6>

            <h6 class="mt-5"><strong>Last modified:</strong> ` + data.date + `</h6>
            <h6><strong>Folder permission:</strong> ` + data.permissions + `</h6>
            
            <div class="text-center mt-4 mb-5">
            <button type="button" class="btn btn-secondary mt-2 mx-1" onclick="copyLink('` + webUrl + folderName.querySelector("h6").innerText + `', this)"><i class="bi bi-link-45deg"></i> Copy Link</button>
            <button type="button" class="btn btn-dark mt-2 mx-1" onclick="openLink('` + webUrl + folderName.querySelector("h6").innerText + `')"><i class="bi bi-box-arrow-up-right"></i> Open</button>
        </div>
            `;

        } else {
            document.querySelector('.selectedInfo div').innerHTML = "<h6>Error loading folder info</h6>";
        }
    };
}

function noFileSelected() {
    document.querySelector('.selectedInfo h4').innerHTML = "Selected file info:";
    document.querySelector('.selectedInfo div').innerHTML = "<h6>No file selected</h6>";
}

function calcSize(bytes) {
    //convert to integer
    bytes = parseInt(bytes);
    let size = bytes / 1024 / 1024;

    //round to 2 decimals
    size = Math.round((size + Number.EPSILON) * 100) / 100
    return size;
}

function copyLink(text, event) {
    event.style.backgroundColor = '#048504';
    event.style.color = '#fff';
    event.innerHTML = '<i class="bi bi-check-circle"></i> Copy Link';
    navigator.clipboard.writeText(text);
    setTimeout(function () {
        event.removeAttribute('style');
        event.innerHTML = '<i class="bi bi-link-45deg"></i> Copy Link';
    }, 1000);
}


function openLink(url) {
    window.open(url, '_blank');
}

function downloadFile(urlDownload, event) {
    if (event != null) {
        event.style.backgroundColor = '#048504';
        event.style.color = '#fff';
        event.innerHTML = '<i class="bi bi-hourglass-split"></i> Downloading...';
    }

    fetch(urlDownload)
        .then(resp => resp.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = urlDownload.split('/').pop();
            document.body.appendChild(a);
            a.classList.add('download-file');
            a.click();
            window.URL.revokeObjectURL(url);
            setTimeout(function () {
                if (event != null) {
                    event.removeAttribute('style');
                    event.innerHTML = '<i class="bi bi-download"></i> Download File';
                }
            }, 1000);
    }).catch(() => {
        alert('Error downloading file');
        if (event != null) {
            event.removeAttribute('style');
            event.innerHTML = '<i class="bi bi-download"></i> Download File';
        }
    });
}