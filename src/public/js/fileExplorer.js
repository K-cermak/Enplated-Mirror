var currentPath = "#drives#";
var currentDrive = "-1";
var currentDriveName = "Drive Name";
var driveAccessLevel = "view";

//onload
window.addEventListener("load", function() {
    generateFolders();

    document.querySelector(".refreshButton").addEventListener("click", event => {
        generateFolders();
    });

    document.querySelector(".folders").addEventListener("click", event => {
        deselectAllClear();
    });

    document.querySelector(".addFolderButton").addEventListener("click", event => {
        let modal = new bootstrap.Modal(document.querySelector("#newFolder"));
        modal.show();
    });

    /*document.querySelector("#newFolderSubmit").addEventListener("click", event => {
        let data = document.querySelector("#newFolderName").value;

        axios.post('{{ env('BASE_URL') }}/api/folders/create', {
            path: currentPath,
            newName: data
        })
        .then(function (response) {
            if (response.data.apiResponse.status == "success") {
                genFlashMessage("Folder created successfully.", "success", 5000);
                generateFolders();
                newFolderModal.hide();
                document.querySelector("#newFolderName").value = "";
            } else {
                genFlashMessage("During the process, an error occurred. Please refresh page and try again.", "error", 20000)
            }
        })
        .catch(function (error) {
            genFlashMessage("During the process, an error occurred. Please refresh page and try again.", "error", 20000)
        });
    });*/
});

function generateFolders() {
    axios.post(baseUrl + '/api/fileViewer/getContent', {
        path : currentPath,
        drive : currentDrive
    })
    .then(function (response) {
        document.querySelector(".folders").innerHTML = "";

        if (response.data.apiResponse.type == "drives") {
            drives = response.data.apiResponse.data;
            if (drives.length == 0) {
                document.querySelector(".folders").innerHTML = "<p class='text-center'>No drives found.</p>";
            }

            for (let i = 0; i < drives.length; i++) {
                let icon = baseUrl + "/public/icons/drive.svg";
                if (drives[i]["accessLevel"] == "view") {
                    icon = baseUrl + "/public/icons/drive-viewonly.svg";
                }

                let data =
                `<div class="card text-center folderDataDrive m-1" style="width: 8rem;">
                    <img class="card-img-top mx-auto" src="${icon}" alt="Folder icon" style="max-width:4rem;">
                    <div class="card-body folderName">
                        <h6 dataId='${drives[i]["id"]}' accessLevel='${drives[i]["accessLevel"]}'>${drives[i]["driveName"]}</h6>
                    </div>
                </div>`;
                document.querySelector(".folders").innerHTML += data;
            }
            selectDrive();
            generatePath();

        } else {
            let files = response.data.apiResponse.data;
            if (files.length == 0) {
                document.querySelector(".folders").innerHTML = "<p class='text-center'>No data found.</p>";
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

                document.querySelector(".folders").innerHTML += data;
            }
            selectFolder();
            generatePath();
        }
    })
    .catch(function (error) {
        genFlashMessage("During the process, an error occurred (you may not have access rights). Please refresh page and try again.", "error", 20000)
    });
}

function generatePath() {
    document.querySelector(".mainPath").innerHTML = "<button class='btn btn-secondary goDrives'>All Drives</button>";

    if (currentPath != "#drives#") {
        //split path by /
        let path = currentPath.split("/");
        document.querySelector(".mainPath").innerHTML += '<i class="bi bi-slash-lg"></i>';
        document.querySelector(".mainPath").innerHTML += "<button class='btn btn-secondary goRoot'>"+currentDriveName+"</button>";

        for (let i = 0; i < path.length; i++) {
            if (path[i] != "") {
                document.querySelector(".mainPath").innerHTML += '<i class="bi bi-slash-lg"></i>';
                document.querySelector(".mainPath").innerHTML += `<button class='btn btn-secondary goPath my-1'>${path[i]}</button>`; 
            }
        }
    
        document.querySelector(".goRoot").addEventListener("click", event => {
            currentPath = "/";
            generateFolders();
        });

        let goPath = document.querySelectorAll(".goPath");
        for (let i = 0; i < goPath.length; i++) {
            goPath[i].addEventListener("click", event => {
                currentPath = "/";
                for (let j = 0; j <= i; j++) {
                    currentPath += goPath[j].innerText + "/";
                }
                generateFolders();
            });
        }
    }

    document.querySelector(".goDrives").addEventListener("click", event => {
        currentPath = "#drives#";
        currentDrive = "-1";
        generateFolders();
    });

    setIcons();
}

//UPLOAD AND CREATE ICONS
function setIcons() {
    let state = true;
    if (currentPath == "#drives#" || driveAccessLevel == "view") {
        state = false;
    }

    if (state == false) {
        document.querySelector(".addFolderButton").style.display = "none";
    } else {
        document.querySelector(".addFolderButton").style.display = "";
    }
}

//FOLDER OR DRIVE CLICK
function selectDrive() {
    let folderDataDrives = document.querySelectorAll('.folderDataDrive');
    folderDataDrives.forEach(function (folderDataDrive) {
        folderDataDrive.addEventListener("click", event => {
            deselectAllClear();
            folderDataDrive.classList.add('selectedFolder');
            event.stopPropagation();
        });

        folderDataDrive.addEventListener("dblclick", event => {
            currentDrive = folderDataDrive.querySelector(".folderName h6").getAttribute("dataId");
            currentDriveName = folderDataDrive.querySelector(".folderName h6").innerText;
            driveAccessLevel = folderDataDrive.querySelector(".folderName h6").getAttribute("accessLevel");
            currentPath = "/";
            generateFolders();
        });
    });
}

function selectFolder() {
    let folderDataFolders = document.querySelectorAll('.folderDataFolder');
    folderDataFolders.forEach(function (folderDataFolder) {
        folderDataFolder.addEventListener("click", event => {
            deselectAllClear();
            folderDataFolder.classList.add('selectedFolder');
            event.stopPropagation();
        });

        folderDataFolder.addEventListener("dblclick", event => {
            currentPath += folderDataFolder.querySelector(".folderName").innerText + "/";
            generateFolders();
        });
    });
}

function deselectAllClear() {
    if (document.querySelector(".selectedFolder")) {
        document.querySelector(".selectedFolder").classList.remove('selectedFolder');
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