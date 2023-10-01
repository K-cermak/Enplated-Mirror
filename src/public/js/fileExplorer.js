//SESSION VARS SETTINGS
for (let i = 1; i < 3; i++) {
    if (sessionStorage.getItem("currentPath" + i) == null) {
        sessionStorage.setItem("currentPath" + i, "#drives#");
    }
    if (sessionStorage.getItem("currentDrive" + i) == null) {
        sessionStorage.setItem("currentDrive" + i, "-1");
    }
    if (sessionStorage.getItem("currentDriveName" + i) == null) {
        sessionStorage.setItem("currentDriveName" + i, "Drive Name");
    }
    if (sessionStorage.getItem("driveAccessLevel" + i) == null) {
        sessionStorage.setItem("driveAccessLevel" + i, "view");
    }
    if (sessionStorage.getItem("driveType" + i) == null) {
        sessionStorage.setItem("driveType" + i, "local");
    }
}

if (sessionStorage.getItem("splittedView") == null) {
    sessionStorage.setItem("splittedView", "false");
}

//onload
window.addEventListener("load", function() {
    generateFolders(1);

    if (sessionStorage.getItem("splittedView") == "true") {
        generateFolders(2);
        let arrow = document.querySelector(".panelSwitcher");
        arrow.classList.remove("bi-arrow-bar-left");
        arrow.classList.add("bi-arrow-bar-right");
        document.querySelector(".mainDrive").style.width = "50%";
        document.querySelector(".secondDrive").style.display = "flex";
    }

    document.querySelectorAll(".refreshButton").forEach(function (refreshButton) {
        refreshButton.addEventListener("click", event => {
            generateFolders(refreshButton.closest(".tabInfo").getAttribute("dataId"));
        });
    });

    document.querySelector(".panelSwitcher").addEventListener("click", event => {
        let arrow = document.querySelector(".panelSwitcher");
        if (arrow.classList.contains("bi-arrow-bar-right")) {
            arrow.classList.remove("bi-arrow-bar-right");
            arrow.classList.add("bi-arrow-bar-left");
            document.querySelector(".mainDrive").style.width = "100%";
            document.querySelector(".secondDrive").style.display = "none";
            sessionStorage.setItem("splittedView", "false");
        } else {
            arrow.classList.remove("bi-arrow-bar-left");
            arrow.classList.add("bi-arrow-bar-right");
            document.querySelector(".mainDrive").style.width = "50%";
            document.querySelector(".secondDrive").style.display = "flex";
            sessionStorage.setItem("splittedView", "true");
            generateFolders(2);
        }
    });

    document.querySelectorAll(".folders").forEach(function (folders) {
        folders.addEventListener("click", event => {
            deselectAllClear(event.target.closest(".tabInfo").getAttribute("dataId"));
        });
    });

    var uploadFileModal = new bootstrap.Modal(document.querySelector("#uploadFile"));
    var newFolderModal = new bootstrap.Modal(document.querySelector("#newFolder"));
    var selectedFolderModal;

    //close of modal
    uploadFileModal._element.addEventListener('hidden.bs.modal', function () {
        generateFolders(1);
        if (sessionStorage.getItem("splittedView") == "true") {
            generateFolders(2);
        }
    });
    
    //filepond
    const inputElement = document.querySelector('#filepondInput');
    const pond = FilePond.create(inputElement);
    FilePond.setOptions({
        server: {
            allowMultiple: true,
            instantUpload: true,
            allowRemove: false,
            allowRevert: false,
            process: {
                url: './api/fileViewer/uploadFile',
                method: 'POST',
                withCredentials: false,
                headers: {},
                ondata: (formData) => {
                    formData.append("path", sessionStorage.getItem("currentPath" + selectedFolderModal));
                    formData.append("drive", sessionStorage.getItem("currentDrive" + selectedFolderModal));
                    return formData;
                },
            },
            revert: null,
        }
    });

    document.querySelectorAll(".uploadButton").forEach(function (addUploadButton) {
        addUploadButton.addEventListener("click", event => {
            selectedFolderModal = event.target.closest(".tabInfo").getAttribute("dataId");
            uploadFileModal.show();
        });
    });
    document.querySelectorAll(".addFolderButton").forEach(function (addFolderButton) {
        addFolderButton.addEventListener("click", event => {
            selectedFolderModal = event.target.closest(".tabInfo").getAttribute("dataId");
            newFolderModal.show();
        });
    });

    document.querySelector("#newFolderSubmit").addEventListener("click", event => {
        let data = document.querySelector("#newFolderName").value;
        if (data == "") {
            return;
        }

        axios.post(baseUrl + '/api/fileViewer/createFolder', {
            path: sessionStorage.getItem("currentPath" + selectedFolderModal),
            drive: sessionStorage.getItem("currentDrive" + selectedFolderModal),
            newName: data
        })
        .then(function (response) {
            if (response.data.apiResponse.status == "success") {
                genFlashMessage("Folder created successfully.", "success", 5000);
                generateFolders(1);
                if (sessionStorage.getItem("splittedView") == "true") {
                    generateFolders(2);
                }
                newFolderModal.hide();
                document.querySelector("#newFolderName").value = "";
            } else {
                genFlashMessage("During the process, an error occurred. Please refresh page and try again.", "error", 20000)
            }
        })
        .catch(function (error) {
            genFlashMessage("During the process, an error occurred. Please refresh page and try again.", "error", 20000)
        });
    });
});

function generateFolders(panel) {
    let selector = "mainDrive";
    if (panel == 2) {
        selector = "secondDrive";
    }

    axios.post(baseUrl + '/api/fileViewer/getContent', {
        path : sessionStorage.getItem("currentPath" + panel),
        drive : sessionStorage.getItem("currentDrive" + panel)
    })
    .then(function (response) {
        document.querySelector("." + selector + " .folders").innerHTML = "";
        
        if (response.data.apiResponse.type == "drives") {
            drives = response.data.apiResponse.data;
            if (drives.length == 0) {
                document.querySelector("." + selector + " .folders").innerHTML = "<p class='text-center'>No drives found.</p>";
            }

            for (let i = 0; i < drives.length; i++) {
                let icon = "";
                let driveType = "";

                if (drives[i]["type"] == "local") {
                    driveType = "local";
                    if (drives[i]["accessLevel"] == "edit") {
                        icon = baseUrl + "/public/icons/drive.svg";
                    } else {
                        icon = baseUrl + "/public/icons/drive-viewonly.svg";
                    }
                } else if (drives[i]["type"] == "ftp") {
                    driveType = "ftp";
                    if (drives[i]["accessLevel"] == "edit") {
                        icon = baseUrl + "/public/icons/ftp.svg";
                    } else {
                        icon = baseUrl + "/public/icons/ftp-viewonly.svg";
                    }
                }


                let data =
                `<div class="card text-center folderDataDrive m-1" style="width: 7rem;" driveType='${driveType}'>
                    <img class="card-img-top mx-auto" src="${icon}" alt="Folder icon" style="max-width:4rem;">
                    <div class="card-body folderName">
                        <h6 dataId='${drives[i]["id"]}' accessLevel='${drives[i]["accessLevel"]}'>${drives[i]["driveName"]}</h6>
                    </div>
                </div>`;

                document.querySelector("." + selector + " .folders").innerHTML += data;

            }
            selectDrive(panel);
            generatePath(panel);

        } else {
            let files = response.data.apiResponse.data;
            if (files.length == 0) {
                document.querySelector("." + selector + " .folders").innerHTML = "<p class='text-center'>No data found.</p>";
            }

            for (let i = 0; i < files.length; i++) {
                let icon = renderExtension(files[i]);
                let fullName = files[i];
                let data;

                //if file name is too long, cut it, keep extension
                if (files[i].length > 15 && icon != "folder.svg") {
                    files[i] = files[i].substring(0, 15) + "..." + files[i].split('.').pop();
                }

                if (icon == "folder.svg") {
                    data =
                        `<div class="card text-center folderDataFolder m-1" style="width: 7rem;">
                            <img class="card-img-top mx-auto" src="${ baseUrl }/public/icons/${icon}" alt="Folder icon" style="max-width:4rem;">
                            <div class="card-body folderName">
                                <h6 fullName='${files[i]}'>${files[i]}</h6>
                            </div>
                        </div>`;

                } else {
                    data =
                        `<div class="card text-center folderDataFile m-1" style="width: 7rem;">
                            <img class="card-img-top mx-auto" src="${ baseUrl }/public/icons/${icon}" alt="File icon" style="max-width:4rem;">
                            <div class="card-body folderName">
                                <h6 fullName='${fullName}'>${files[i]}</h6>
                            </div>
                        </div>`;
                }
                document.querySelector("." + selector + " .folders").innerHTML += data;
            }
            if ((sessionStorage.getItem("driveType" + panel) == "local" && allowPreviews == true) || sessionStorage.getItem("driveType" + panel) == "ftp" && allowPreviewsFtp == true) {
                renderImages(panel);
            }
            selectFolder(panel);
            generatePath(panel);
        }


        document.querySelector("." + selector + " .infoData").innerHTML = "No file selected.";
        if (document.querySelectorAll("." + selector + " .folderDataFile").length > 0) {
            let plural = "s";
            if (document.querySelectorAll("." + selector + " .folderDataFile").length == 1) {
                plural = "";
            }
            document.querySelector("." + selector + " .infoData").innerHTML += " " + document.querySelectorAll("." + selector + " .folderDataFile").length + " file"+plural+" in folder.";
        }

    })
    .catch(function (error) {
        if (error.response.data.apiResponse.type !== null && (error.response.data.apiResponse.type == "error-connecting-to-server" || error.response.data.apiResponse.type == "error-logging-in")) {
            genFlashMessage("Failed to connect to FTP server. Check that the FTP server settings are still the same and contact the administrator if necessary.", "error", 20000);
        } else {
            genFlashMessage("During the process, an error occurred (you may not have access rights). We have moved you to the main directory just in case.", "error", 20000);
        }
        sessionStorage.setItem("currentPath" + panel, "#drives#");
        sessionStorage.setItem("currentDrive" + panel, "-1");
        sessionStorage.setItem("currentDriveName" + panel, "Drive Name");
        sessionStorage.setItem("driveAccessLevel" + panel, "view");
        generateFolders(1);
        if (sessionStorage.getItem("splittedView") == "true") {
            generateFolders(2);
        }
    });
}

function generatePath(panel) {
    let selector = "mainDrive";
    if (panel == 2) {
        selector = "secondDrive";
    }

    document.querySelector("." + selector + " .mainPath").innerHTML = "<button class='btn btn-secondary goDrives'>All Drives</button>";

    if (sessionStorage.getItem("currentPath" + panel) != "#drives#") {
        //split path by /
        let path = sessionStorage.getItem("currentPath" + panel).split("/");
        document.querySelector("." + selector + " .mainPath").innerHTML += '<i class="bi bi-slash-lg"></i>';
        document.querySelector("." + selector + " .mainPath").innerHTML += "<button class='btn btn-secondary goRoot'>"+sessionStorage.getItem("currentDriveName" + panel)+"</button>";

        for (let i = 0; i < path.length; i++) {
            if (path[i] != "") {
                document.querySelector("." + selector + " .mainPath").innerHTML += '<i class="bi bi-slash-lg"></i>';
                document.querySelector("." + selector + " .mainPath").innerHTML += `<button class='btn btn-secondary goPath my-1'>${path[i]}</button>`; 
            }
        }
    
        document.querySelector("." + selector + " .goRoot").addEventListener("click", event => {
            sessionStorage.setItem("currentPath" + panel, "/");
            generateFolders(panel);
        });

        let goPath = document.querySelectorAll("." + selector + " .goPath");
        for (let i = 0; i < goPath.length; i++) {
            goPath[i].addEventListener("click", event => {
                sessionStorage.setItem("currentPath" + panel, "/");
                for (let j = 0; j <= i; j++) {
                    sessionStorage.setItem("currentPath" + panel, sessionStorage.getItem("currentPath" + panel) + goPath[j].innerText + "/");
                }
                generateFolders(panel);
            });
        }
    }

    document.querySelector("." + selector + " .goDrives").addEventListener("click", event => {
        sessionStorage.setItem("currentPath" + panel, "#drives#");
        sessionStorage.setItem("currentDrive" + panel, "-1");
        generateFolders(panel);
    });

    setIcons(panel);
}

//UPLOAD AND CREATE FOLDER ICONS
function setIcons(panel) {
    let state = true;
    if (sessionStorage.getItem("currentPath" + panel) == "#drives#" || sessionStorage.getItem("driveAccessLevel" + panel) == "view") {
        state = false;
    }

    let selector = "mainDrive";
    if (panel == 2) {
        selector = "secondDrive";
    }

    if (state == false) {
        document.querySelector("." + selector +  " .uploadButton").style.display = "none";
        document.querySelector("." + selector +  " .addFolderButton").style.display = "none";
    } else {
        document.querySelector("." + selector +  " .uploadButton").style.display = "";
        document.querySelector("." + selector +  " .addFolderButton").style.display = "";
    }
}

//FOLDER OR DRIVE OR FILE CLICK
function selectDrive(panel) {
    let folderDataDrives;
    if (panel == 1) {
        folderDataDrives = document.querySelectorAll('.mainDrive .folderDataDrive');
    } else if (panel == 2) {
        folderDataDrives = document.querySelectorAll('.secondDrive .folderDataDrive');
    }
    folderDataDrives.forEach(function (folderDataDrive) {
        folderDataDrive.addEventListener("click", event => {
            deselectAllClear(panel);
            folderDataDrive.classList.add('selectedItem');
            event.stopPropagation();

            getFileInfo(panel, folderDataDrive.querySelector(".folderName h6").getAttribute("dataId"), "drive");
        });

        folderDataDrive.addEventListener("dblclick", event => {
            sessionStorage.setItem("currentDrive" + panel, folderDataDrive.querySelector(".folderName h6").getAttribute("dataId"));
            sessionStorage.setItem("currentDriveName" + panel, folderDataDrive.querySelector(".folderName h6").innerText);
            sessionStorage.setItem("driveAccessLevel" + panel, folderDataDrive.querySelector(".folderName h6").getAttribute("accessLevel"));
            sessionStorage.setItem("currentPath" + panel, "/");
            sessionStorage.setItem("driveType" + panel, folderDataDrive.getAttribute("driveType"));
            generateFolders(panel);
        });
    });
}

function selectFolder(panel) {
    let folderDataFolders;
    if (panel == 1) {
        folderDataFolders = document.querySelectorAll('.mainDrive .folderDataFolder');
    } else if (panel == 2) {
        folderDataFolders = document.querySelectorAll('.secondDrive .folderDataFolder');
    }

    folderDataFolders.forEach(function (folderDataFolder) {
        folderDataFolder.addEventListener("click", event => {
            if (event.ctrlKey == false && event.metaKey == false) {
                deselectAllClear(panel);
            }

            //if folder is already selected, deselect it
            if (folderDataFolder.classList.contains('selectedItem') && (event.ctrlKey || event.metaKey)) {
                folderDataFolder.classList.remove('selectedItem');
            } else {
                folderDataFolder.classList.add('selectedItem');
            }

            event.stopPropagation();
            getFileInfo(panel, folderDataFolder.querySelector(".folderName").innerText, "folder");
        });

        folderDataFolder.addEventListener("dblclick", event => {
            sessionStorage.setItem("currentPath" + panel, sessionStorage.getItem("currentPath" + panel) + folderDataFolder.querySelector(".folderName").innerText + "/");
            generateFolders(panel);
        });
    });

    selectFiles(panel);
}

function selectFiles(panel) {
    let folderDataFiles;
    if (panel == 1) {
        folderDataFiles = document.querySelectorAll('.mainDrive .folderDataFile');
    } else if (panel == 2) {
        folderDataFiles = document.querySelectorAll('.secondDrive .folderDataFile');
    }

    folderDataFiles.forEach(function (folderDataFile) {
        folderDataFile.addEventListener("click", event => {
            if (event.ctrlKey == false && event.metaKey == false) {
                deselectAllClear(panel);
            }

            //if file is already selected, deselect it
            if (folderDataFile.classList.contains('selectedItem') && (event.ctrlKey || event.metaKey)) {
                folderDataFile.classList.remove('selectedItem');
            } else {
                folderDataFile.classList.add('selectedItem');
            }

            event.stopPropagation();
            getFileInfo(panel, folderDataFile.querySelector(".folderName h6").getAttribute("fullName"), "file");
        });
    });
}

function deselectAllClear(panel) {
    let selector = "mainDrive";
    if (panel == 2) {
        selector = "secondDrive";
    }

    document.querySelector("." + selector + " .infoData").innerHTML = "No file selected.";

    let length = document.querySelectorAll("." + selector + " .selectedItem").length;
    for (let i = 0; i < length; i++) {
        document.querySelectorAll("." + selector + " .selectedItem")[0].classList.remove('selectedItem');
    }

    if (document.querySelectorAll("." + selector + " .folderDataFile").length > 0) {
        let plural = "s";
        if (document.querySelectorAll("." + selector + " .folderDataFile").length == 1) {
            plural = "";
        }
        document.querySelector("." + selector + " .infoData").innerHTML += " " + document.querySelectorAll("." + selector + " .folderDataFile").length + " file"+plural+" in folder.";
    }

}

function getFileInfo(panel, nameOfElement, type) {
    let selector = "mainDrive";
    if (panel == 2) {
        selector = "secondDrive";
    }

    //multiple
    let length = document.querySelectorAll("." + selector + " .selectedItem").length;
    if (length > 1) {
        //if drive type ftp
        if (sessionStorage.getItem("driveType" + panel) == "ftp") {
            document.querySelector("." + selector + " .infoData").innerHTML = "Selected " + length + " items.";
        } else if (sessionStorage.getItem("driveType" + panel) == "local") {
            let files = [];
            for (let i = 0; i < length; i++) {
                files.push(document.querySelectorAll("." + selector + " .selectedItem")[i].querySelector(".folderName h6").getAttribute("fullName"));
            }

            axios.post(baseUrl + '/api/fileViewer/getFileInfo', {
                drive : sessionStorage.getItem("currentDrive" + panel),
                type : "multiple",
                path : sessionStorage.getItem("currentPath" + panel),
                files : JSON.stringify(files)
            })
            .then(function (response) {
                let size = response.data.apiResponse.size;
                let sizeInMb = size / 1024 / 1024; //MiB
    
                size = size.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "&thinsp;");
                sizeInMb = sizeInMb.toFixed(2);
                sizeInMb = sizeInMb.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "&thinsp;");

                document.querySelector("." + selector + " .infoData").innerHTML = "Selected " + length + " items, total size: <strong data-bs-toggle='tooltip' data-bs-placement='top' title='" + size + " B, which is equivalent to " + sizeInMb + " MiB.'>" + sizeInMb + " MB</strong>";
            
                //run tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            });
            
        }
    } else if (type == "drive") {
        axios.post(baseUrl + '/api/fileViewer/getFileInfo', {
            drive : nameOfElement,
            type : "drive"
        })
        .then(function (response) {
            let data = "";
            if (response.data.apiResponse.driveType == "local") {
                data = "Drive Type: <strong>Local</strong>, Access Level: <strong>" + response.data.apiResponse.accessLevel + "</strong>";
            } else if (response.data.apiResponse.driveType == "ftp") {
                data = "Drive Type: <strong>FTP</strong>, Access Level: <strong>" + response.data.apiResponse.accessLevel + "</strong>, Ping: <strong>" + response.data.apiResponse.ping + " ms</strong>";
            }

            document.querySelector("." + selector + " .infoData").innerHTML = data;
        });

    } else if (type == "folder") {
        axios.post(baseUrl + '/api/fileViewer/getFileInfo', {
            drive : sessionStorage.getItem("currentDrive" + panel),
            type : "folder",
            path : sessionStorage.getItem("currentPath" + panel) + nameOfElement
        })
        .then (function (response) {
            let driveType = response.data.apiResponse.driveType;
            let numberOfFiles = response.data.apiResponse.numberOfFiles;
            let overflow = response.data.apiResponse.overflow;

            let size = response.data.apiResponse.size;
            let sizeInMb = size / 1024 / 1024; //MiB

            size = size.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "&thinsp;");
            sizeInMb = sizeInMb.toFixed(2);
            sizeInMb = sizeInMb.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "&thinsp;");


            let data = "";

            if (overflow == true) {
                data += "Number of files inside: <strong>More than " + numberOfFiles + "</strong>, ";
                data += "Size: <strong data-bs-toggle='tooltip' data-bs-placement='top' title='More than " + size + " B, which is equivalent to more than " + sizeInMb + " MiB.'>More than " + sizeInMb + " MB</strong>";
            } else {
                data += "Number of files inside: <strong>" + numberOfFiles + "</strong>, ";
                data += "Size: <strong data-bs-toggle='tooltip' data-bs-placement='top' title='" + size + " B, which is equivalent to " + sizeInMb + " MiB.'>" + sizeInMb + " MB</strong>";
            }

            if (driveType == "local") {
                data += "<br>Created on: <strong>" + response.data.apiResponse.creationDate + "</strong>, ";
                data += "Last modified on: <strong>" + response.data.apiResponse.lastModifiedDate + "</strong>";
            } else if (driveType == "ftp") {
                data += " (Notice: FTP does not count recursively.)"
            }

            document.querySelector("." + selector + " .infoData").innerHTML = data;

            //run tooltip
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

    } else if (type == "file") {
        axios.post(baseUrl + '/api/fileViewer/getFileInfo', {
            drive : sessionStorage.getItem("currentDrive" + panel),
            type : "file",
            path : sessionStorage.getItem("currentPath" + panel),
            file : nameOfElement
        })
        .then (function (response) {
            let driveType = response.data.apiResponse.driveType;

            let size = response.data.apiResponse.size;
            let sizeInMb = size / 1024 / 1024; //MiB

            size = size.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "&thinsp;");
            sizeInMb = sizeInMb.toFixed(2);
            sizeInMb = sizeInMb.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "&thinsp;");

            let data = "";
            data += "Size: <strong data-bs-toggle='tooltip' data-bs-placement='top' title='" + size + " B, which is equivalent to " + sizeInMb + " MiB.'>" + sizeInMb + " MB</strong>";

            if (driveType == "local") {
                data += "<br>Created on: <strong>" + response.data.apiResponse.creationDate + "</strong>, ";
                data += "Last modified on: <strong>" + response.data.apiResponse.lastModifiedDate + "</strong>";
            }

            document.querySelector("." + selector + " .infoData").innerHTML = data;

            //run tooltip
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    }
}

function renderExtension(filename) {
    let extension = filename.split('.').pop();
    let icon = "";

    do {
        if (extension == filename) {
            icon = "folder.svg";
            break;
        }
        extension = extension.toLowerCase();
        if (extension == "jpg" || extension == "jpeg" || extension == "png" || extension == "gif") {
            icon = "image.svg";
            break;
        }
        if (extension == "svg") {
            icon = "svg.svg";
            break;
        }
        if (extension == "mp3" || extension == "wav" || extension == "flac") {
            icon = "audio.svg";
            break;
        }
        if (extension == "mp4" || extension == "avi" || extension == "mkv") {
            icon = "video.svg";
            break;
        }
        if (extension == "pdf") {
            icon = "pdf.svg";
            break;
        }
        if (extension == "doc" || extension == "docx") {
            icon = "word.svg";
            break;
        }
        if (extension == "xls" || extension == "xlsx") {
            icon = "excel.svg";
            break;
        }
        if (extension == "ppt" || extension == "pptx") {
            icon = "powerpoint.svg";
            break;
        }
        if (extension == "zip" || extension == "rar") {
            icon = "zip.svg";
            break;
        }
        if (extension == "txt") {
            icon = "txt.svg";
            break;
        }
        if (extension == "html") {
            icon = "html.svg";
            break;
        }
        if (extension == "js") {
            icon = "js.svg";
            break;
        }
        if (extension == "css") {
            icon = "css.svg";
            break;
        }
        if (extension == "xml") {
            icon = "xml.svg";
            break;
        }
        if (extension == "sql") {
            icon = "sql.svg";
            break;
        }
        if (extension == "php") {
            icon = "php.svg";
            break;
        }
        if (extension == "py") {
            icon = "python.svg";
            break;
        }
        if (extension == "rb") {
            icon = "ruby.svg";
            break;
        }
        if (extension == "java") {
            icon = "java.svg";
            break;
        }
        if (extension == "c") {
            icon = "c.svg";
            break;
        }
        if (extension == "cpp") {
            icon = "cpp.svg";
            break;
        }
        if (extension == "cs") {
            icon = "cs.svg";
            break;
        }
        if (extension == "h") {
            icon = "h.svg";
            break;
        }
        if (extension == "hpp") {
            icon = "hpp.svg";
            break;
        }
        if (extension == "json") {
            icon = "json.svg";
            break;
        }

        icon = "file.svg";
    } while (false);

    return icon;
}

function renderImages(panel) {
    let images;
    if (panel == 1) {
        images = document.querySelectorAll('.mainDrive .folderDataFile');
    } else if (panel == 2) {
        images = document.querySelectorAll('.secondDrive .folderDataFile');
    }

    let i = 0;
    let drive = sessionStorage.getItem("currentDrive" + panel);
    let path = sessionStorage.getItem("currentPath" + panel);


    images.forEach(function (image) {
        let extension = image.querySelector(".folderName h6").getAttribute("fullName").split('.').pop();
        extension = extension.toLowerCase();
        if (extension == "jpg" || extension == "jpeg" || extension == "png" || extension == "gif") {
            setTimeout(function() {
                if (drive != sessionStorage.getItem("currentDrive" + panel) || path != sessionStorage.getItem("currentPath" + panel)) {
                    return;
                }

                axios.post(baseUrl + '/api/fileViewer/imagePreview', {
                    drive : drive,
                    path : path,
                    file : image.querySelector(".folderName h6").getAttribute("fullName"),
                }, { responseType: 'blob' })
                .then (function (response) {
                    var reader = new window.FileReader();
                    reader.readAsDataURL(response.data); 
                    reader.onload = function() {
                        var imageDataUrl = reader.result;
                        image.querySelector("img").src = imageDataUrl;
                        image.querySelector("img").style.width = "auto";
                        image.querySelector("img").style.height = "auto";
                        image.querySelector("img").style.maxHeight = "65px";
                        image.querySelector("img").style.maxWidth = "100%";
                    }
                });
            }, i * 1000);
            i++;
        }
    });
}