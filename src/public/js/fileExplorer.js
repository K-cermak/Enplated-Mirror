//PROM SETTINGS
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


    var newFolderModal = new bootstrap.Modal(document.querySelector("#newFolder"));
    var selectedFolderModal;

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
    axios.post(baseUrl + '/api/fileViewer/getContent', {
        path : sessionStorage.getItem("currentPath" + panel),
        drive : sessionStorage.getItem("currentDrive" + panel)
    })
    .then(function (response) {
        if (panel == 1) {
            document.querySelector(".mainDrive .folders").innerHTML = "";
        } else if (panel == 2) {
            document.querySelector(".secondDrive .folders").innerHTML = "";
        }

        if (response.data.apiResponse.type == "drives") {
            drives = response.data.apiResponse.data;
            if (drives.length == 0) {
                if (panel == 1) {
                    document.querySelector(".mainDrive .folders").innerHTML = "<p class='text-center'>No drives found.</p>";
                } else if (panel == 2) {
                    document.querySelector(".secondDrive .folders").innerHTML = "<p class='text-center'>No drives found.</p>";
                }
            }

            for (let i = 0; i < drives.length; i++) {
                let icon = "";

                if (drives[i]["type"] == "local") {
                    if (drives[i]["accessLevel"] == "edit") {
                        icon = baseUrl + "/public/icons/drive.svg";
                    } else {
                        icon = baseUrl + "/public/icons/drive-viewonly.svg";
                    }
                } else if (drives[i]["type"] == "ftp") {
                    if (drives[i]["accessLevel"] == "edit") {
                        icon = baseUrl + "/public/icons/ftp.svg";
                    } else {
                        icon = baseUrl + "/public/icons/ftp-viewonly.svg";
                    }
                }


                let data =
                `<div class="card text-center folderDataDrive m-1" style="width: 8rem;">
                    <img class="card-img-top mx-auto" src="${icon}" alt="Folder icon" style="max-width:4rem;">
                    <div class="card-body folderName">
                        <h6 dataId='${drives[i]["id"]}' accessLevel='${drives[i]["accessLevel"]}'>${drives[i]["driveName"]}</h6>
                    </div>
                </div>`;
                if (panel == 1) {
                    document.querySelector(".mainDrive .folders").innerHTML += data;
                } else if (panel == 2) {
                    document.querySelector(".secondDrive .folders").innerHTML += data;
                }
            }
            selectDrive(panel);
            generatePath(panel);

        } else {
            let files = response.data.apiResponse.data;
            if (files.length == 0) {
                if (panel == 1) {
                    document.querySelector(".mainDrive .folders").innerHTML = "<p class='text-center'>No data found.</p>";
                } else if (panel == 2) {
                    document.querySelector(".secondDrive .folders").innerHTML = "<p class='text-center'>No data found.</p>";
                }
            }

            for (let i = 0; i < files.length; i++) {
                let icon = renderExtension(files[i]);
                let data;

                if (icon == "folder.svg") {
                    data =
                        `<div class="card text-center folderDataFolder m-1" style="width: 8rem;">
                            <img class="card-img-top mx-auto" src="${ baseUrl }/public/icons/${renderExtension(files[i])}" alt="Folder icon" style="max-width:4rem;">
                            <div class="card-body folderName">
                                <h6>${files[i]}</h6>
                            </div>
                        </div>`;

                } else {
                    data =
                        `<div class="card text-center folderDataFile m-1" style="width: 8rem;">
                            <img class="card-img-top mx-auto" src="${ baseUrl }/public/icons/${renderExtension(files[i])}" alt="File icon" style="max-width:4rem;">
                            <div class="card-body folderName">
                                <h6>${files[i]}</h6>
                            </div>
                        </div>`;
                }
                if (panel == 1) {
                    document.querySelector(".mainDrive .folders").innerHTML += data;
                } else if (panel == 2) {
                    document.querySelector(".secondDrive .folders").innerHTML += data;
                }
            }
            selectFolder(panel);
            generatePath(panel);
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
    let prefix = "";
    if (panel == 1) {
        prefix = ".mainDrive";
    } else if (panel == 2) {
        prefix = ".secondDrive";
    }

    document.querySelector(prefix + " .mainPath").innerHTML = "<button class='btn btn-secondary goDrives'>All Drives</button>";

    if (sessionStorage.getItem("currentPath" + panel) != "#drives#") {
        //split path by /
        let path = sessionStorage.getItem("currentPath" + panel).split("/");
        document.querySelector(prefix + " .mainPath").innerHTML += '<i class="bi bi-slash-lg"></i>';
        document.querySelector(prefix + " .mainPath").innerHTML += "<button class='btn btn-secondary goRoot'>"+sessionStorage.getItem("currentDriveName" + panel)+"</button>";

        for (let i = 0; i < path.length; i++) {
            if (path[i] != "") {
                document.querySelector(prefix + " .mainPath").innerHTML += '<i class="bi bi-slash-lg"></i>';
                document.querySelector(prefix + " .mainPath").innerHTML += `<button class='btn btn-secondary goPath my-1'>${path[i]}</button>`; 
            }
        }
    
        document.querySelector(prefix + " .goRoot").addEventListener("click", event => {
            sessionStorage.setItem("currentPath" + panel, "/");
            generateFolders(panel);
        });

        let goPath = document.querySelectorAll(prefix + " .goPath");
        for (let i = 0; i < goPath.length; i++) {
            goPath[i].addEventListener("click", event => {
                sessionStorage.setItem("currentPath" + panel, "/");
                for (let j = 0; j <= i; j++) {
                    sessionStorage.setItem("currentPath", sessionStorage.getItem("currentPath" + panel) + goPath[j].innerText + "/");
                }
                generateFolders(panel);
            });
        }
    }

    document.querySelector(prefix + " .goDrives").addEventListener("click", event => {
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

    if (state == false) {
        if (panel == 1) {
            document.querySelector(".mainDrive .addFolderButton").style.display = "none";
        } else {
            document.querySelector(".secondDrive .addFolderButton").style.display = "none";
        }
    } else {
        if (panel == 1) {
            document.querySelector(".mainDrive .addFolderButton").style.display = "";
        } else {
            document.querySelector(".secondDrive .addFolderButton").style.display = "";
        }
    }
}

//FOLDER OR DRIVE CLICK
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
        });

        folderDataDrive.addEventListener("dblclick", event => {
            sessionStorage.setItem("currentDrive" + panel, folderDataDrive.querySelector(".folderName h6").getAttribute("dataId"));
            sessionStorage.setItem("currentDriveName" + panel, folderDataDrive.querySelector(".folderName h6").innerText);
            sessionStorage.setItem("driveAccessLevel" + panel, folderDataDrive.querySelector(".folderName h6").getAttribute("accessLevel"));
            sessionStorage.setItem("currentPath" + panel, "/");
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
            deselectAllClear(panel);
            folderDataFolder.classList.add('selectedItem');
            event.stopPropagation();
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
            deselectAllClear(panel);
            folderDataFile.classList.add('selectedItem');
            event.stopPropagation();
        });
    });
}

function deselectAllClear(panel) {
    if (panel == 1) {
        if (document.querySelector(".mainDrive .selectedItem")) {
            document.querySelector(".mainDrive .selectedItem").classList.remove('selectedItem');
        }
    } else if (panel == 2) {
        if (document.querySelector(".secondDrive .selectedItem")) {
            document.querySelector(".secondDrive .selectedItem").classList.remove('selectedItem');
        }
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