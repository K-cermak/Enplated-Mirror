window.addEventListener('load', function () {
    selectBody();
    selectFolder();
    selectFile();
    noFileSelected();
    setRefreshButton();
});

function selectBody() {
    document.querySelector('body').addEventListener('click', function (event) {
        deselectAll(event);
    });
}

function selectFolder() {
    let folderDataFolders = document.querySelectorAll('.folderDataFolder');
    folderDataFolders.forEach(function (folderDataFolder) {
        folderDataFolder.addEventListener("click", event => {
            deselectAllClear();
            folderDataFolder.style.backgroundColor = "#06428a";
            loadFolder(folderDataFolder);
        });

        folderDataFolder.addEventListener("dblclick", event => {
            folderDataFolder.querySelector(".submitButton").click();
        });
    });
}

function selectFile() {
    let folderDataFiles = document.querySelectorAll('.folderDataFile');
    folderDataFiles.forEach(function (folderDataFile) {
        let ext = folderDataFile.querySelector(".card-body h6").innerHTML.split('.').pop();
        if (ext == "jpg" || ext == "png" || ext == "jpeg" || ext == "svg") {
            folderDataFile.querySelector(".card-body img").setAttribute("src", webUrl + folderDataFile.querySelector(".card-body h6").innerHTML);
        }

        folderDataFile.addEventListener("click", event => {
            deselectAllClear();
            folderDataFile.style.backgroundColor = "#06428a";
            loadFile(folderDataFile);
        });

        folderDataFile.addEventListener("dblclick", event => {
            downloadFile(webUrl + folderDataFile.querySelector("h6").innerText, null);
        });
    });
}

function deselectAll(event) {
    //if clicked elements are not folders or parent is not folders, deselect all folders
    if (!event.target.classList.contains('folderDataFolder')
        && !event.target.classList.contains('folderDataFile')
        && event.target.closest('.folderDataFolder') == null
        && event.target.closest('.folderDataFile') == null
        && event.target.closest('.selectedInfo') == null
        && event.target.classList.contains('download-file') == false
    ) {
        noFileSelected();
        let folderDataFolders = document.querySelectorAll('.folderDataFolder');
        folderDataFolders.forEach(function (folderDataFolder) {
            folderDataFolder.style.backgroundColor = "";
        });

        let folderDataFiles = document.querySelectorAll('.folderDataFile');
        folderDataFiles.forEach(function (folderDataFile) {
            folderDataFile.style.backgroundColor = "";
        });
    }
    
}

function deselectAllClear() {
    let folderDataFolders = document.querySelectorAll('.folderDataFolder');
    folderDataFolders.forEach(function (folderDataFolder) {
        folderDataFolder.style.backgroundColor = "";
    });

    let folderDataFiles = document.querySelectorAll('.folderDataFile');
    folderDataFiles.forEach(function (folderDataFile) {
        folderDataFile.style.backgroundColor = "";
    });
}

function setRefreshButton() {
    document.querySelector('.refreshButton').addEventListener('click', function () {
        window.location.reload();
    });
}