window.addEventListener('load', function () {
    selectBody();
    selectFolder();
    selectFile();
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
        });

        folderDataFolder.addEventListener("dblclick", event => {
            folderDataFolder.querySelector(".submitButton").click();
        });
    });
}

function selectFile() {
    let folderDataFiles = document.querySelectorAll('.folderDataFile');
    folderDataFiles.forEach(function (folderDataFile) {
        folderDataFile.addEventListener("click", event => {
            deselectAllClear();
            folderDataFile.style.backgroundColor = "#06428a";
        });
    });
}

function deselectAll(event) {
    //if clicked elements are not folders or parent is not folders, deselect all folders
    if (!event.target.classList.contains('folderDataFolder') && event.target.classList.contains('folderDataFile') && event.target.closest('.folderDataFolder') == null && event.target.closest('.folderDataFile') == null) {

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