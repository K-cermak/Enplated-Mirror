<?php
    //DASHBOARD
    checkRoute('GET', '/dashboard' , function() {
        redirectNotLogin();

        $template = processTemplate("dashboard", ["pageTitle" => "Dashboard"]);
        finishRender($template);
    });


    checkRoute('GET', '/dashboard/users' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();

        $users = modelCall('users', 'getAllUsers', ['db' => getDatabaseEnvConn('sqlite')]);
        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]["privilegeLevel"] == 2 || $users[$i]["privilegeLevel"] == 0) {
                $users[$i]["groups"] = modelCall('groups', 'getGroupsByUser', ['db' => getDatabaseEnvConn('sqlite'), "userId" => $users[$i]["id"]]);
            }
        }

        $template = processTemplate("users", ["pageTitle" => "Users", "users" => $users]);
        finishRender($template);
    });


    checkRoute('POST', '/dashboard/users' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();
        $error = "";
        $success = "";

        if (isset($_GET["newUser"]) && isset($_POST["newName"]) && !empty($_POST["newName"]) && isset($_POST["newPassword"]) && !empty($_POST["newPassword"]) && isset($_POST["privilegeLevel"]) && ($_POST["privilegeLevel"] == "user" || $_POST["privilegeLevel"] == "admin")) {
            do {
                $newName = $_POST["newName"];
                $newPassword = $_POST["newPassword"];
                $privilegeLevel = $_POST["privilegeLevel"];

                //check if name does not exist
                $result = modelCall('users', 'checkIfUsernameExist', ['db' => getDatabaseEnvConn('sqlite'), "username" => $newName]);
                if ($result != false) {
                    $error = "Username already exist.";
                    break;
                }

                //check if at least 3 chars and max 20 chars
                if (strlen($newName) < 3 || strlen($newName) > 20) {
                    $error = "Username must be at least 3 characters and maximum 20 characters long.";
                    break;
                }

                //check if only letters and numbers
                if (!ctype_alnum($newName)) {
                    $error = "Username can only contain letters and numbers.";
                    break;
                }

                //check if password is at least 6 chars
                if (strlen($newPassword) < 6) {
                    $error = "Password must be at least 6 characters long.";
                    break;
                }

                if ($privilegeLevel == "user") {
                    $privilegeLevel = 2;
                } else if ($privilegeLevel == "admin") {
                    $privilegeLevel = 1;
                }

                //create user
                modelCall('users', 'createUser', ['db' => getDatabaseEnvConn('sqlite'), "name" => $newName, "password" => hash("sha256", $newPassword), "privilegeLevel" => $privilegeLevel]);
                $success = "User created succesfully.";

            } while (false);
        }

        if (isset($_GET["changeName"]) && isset($_POST["userId"]) && !empty($_POST["userId"]) && isset($_POST["newName"]) && !empty($_POST["newName"])) {
            do {
                $userId = $_POST["userId"];
                $newName = $_POST["newName"];

                //check if not logged user
                if ($_SESSION["userId"] == $userId) {
                    $error = "You are not allowed to change your username.";
                    break;
                }

                //check if user exist
                $result = modelCall('users', 'getUsersInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $userId]);
                if ($result == false) {
                    $error = "User does not exist.";
                    break;
                }

                //check if name does not exist
                $result = modelCall('users', 'checkIfUsernameExist', ['db' => getDatabaseEnvConn('sqlite'), "username" => $newName]);
                if ($result != false) {
                    $error = "Username already exist or nothing to change.";
                    break;
                }

                //check if at least 3 chars and max 20 chars
                if (strlen($newName) < 3 || strlen($newName) > 20) {
                    $error = "Username must be at least 3 characters and maximum 20 characters long.";
                    break;
                }
                
                //check if only letters and numbers
                if (!ctype_alnum($newName)) {
                    $error = "Username can only contain letters and numbers.";
                    break;
                }
                
                //update
                modelCall('users', 'changeUsername', ['db' => getDatabaseEnvConn('sqlite'), "id" => $userId, "newName" => $newName]);
                $success = "Username changed succesfully.";
            } while (false);
        }

        if (isset($_GET["changePrivilegeLevel"]) && isset($_POST["userId"]) && !empty($_POST["userId"]) && isset($_POST["newPrivilegeLevel"]) && ($_POST["newPrivilegeLevel"] == "blocked" || $_POST["newPrivilegeLevel"] == "admin" || $_POST["newPrivilegeLevel"] == "user")) {
            do {
                $userId = $_POST["userId"];
                $newPrivilegeLevel = $_POST["newPrivilegeLevel"];

                //check if not logged user
                if ($_SESSION["userId"] == $userId) {
                    $error = "You are not allowed to change your privilege level.";
                    break;
                }

                //check if user exist
                $result = modelCall('users', 'getUsersInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $userId]);
                if ($result == false) {
                    $error = "User does not exist.";
                    break;
                }

                if ($newPrivilegeLevel == "blocked") {
                    $newPrivilegeLevel = 0;
                } else if ($newPrivilegeLevel == "user") {
                    $newPrivilegeLevel = 2;
                } else if ($newPrivilegeLevel == "admin") {
                    $newPrivilegeLevel = 1;
                }

                //update
                modelCall('users', 'changePrivilegeLevel', ['db' => getDatabaseEnvConn('sqlite'), "id" => $userId, "newPrivilegeLevel" => $newPrivilegeLevel]);
                $success = "Privilege level changed succesfully.";

            } while (false);
        }

        if (isset($_GET["changePassword"]) && isset($_POST["userId"]) && !empty($_POST["userId"]) && isset($_POST["newPassword"]) && !empty($_POST["newPassword"]) && isset($_POST["newPasswordVerify"]) && !empty($_POST["newPasswordVerify"])) {
            do {
                $userId = $_POST["userId"];
                $newPassword = $_POST["newPassword"];
                $newPasswordVerify = $_POST["newPasswordVerify"];

                //check if not logged user
                if ($_SESSION["userId"] == $userId) {
                    $error = "You are not allowed to change your password.";
                    break;
                }

                //check if user exist
                $result = modelCall('users', 'getUsersInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $userId]);
                if ($result == false) {
                    $error = "User does not exist.";
                    break;
                }

                //check if newPassword is the same as newPasswordVerify
                if ($newPassword != $newPasswordVerify) {
                    $error = "New password does not match with control password.";
                    break;
                }

                //check if newPassword is at least 6 chars
                if (strlen($newPassword) < 6) {
                    $error = "New password must be at least 6 characters long.";
                    break;
                }

                //update
                modelCall('users', 'changePassword', ['db' => getDatabaseEnvConn('sqlite'), "id" => $userId, "newPassword" => hash("sha256", $newPassword)]);
                $success = "Password changed succesfully.";
            } while (false);
        }


        $users = modelCall('users', 'getAllUsers', ['db' => getDatabaseEnvConn('sqlite')]);
        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]["privilegeLevel"] == 2 || $users[$i]["privilegeLevel"] == 0) {
                $users[$i]["groups"] = modelCall('groups', 'getGroupsByUser', ['db' => getDatabaseEnvConn('sqlite'), "userId" => $users[$i]["id"]]);
            }
        }

        $template = processTemplate("users", ["pageTitle" => "Users", "users" => $users, "error" => $error, "success" => $success]);
        finishRender($template);
    });


    checkRoute('GET', '/dashboard/groups' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();

        $groups = modelCall('groups', 'getAllGroups', ['db' => getDatabaseEnvConn('sqlite')]);
        for ($i = 0; $i < count($groups); $i++) {
            $groups[$i]["users"] = modelCall('groups', 'getUsernamesInGroup', ['db' => getDatabaseEnvConn('sqlite'), "groupId" => $groups[$i]["id"]]);
        }
        $users = modelCall('users', 'getUsersWithPrivilege', ['db' => getDatabaseEnvConn('sqlite'), "privilegeLevel" => 2]);

        $template = processTemplate("groups", ["pageTitle" => "Groups", "groups" => $groups, "users" => $users]);
        finishRender($template);
    });


    checkRoute('POST', '/dashboard/groups' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();

        $error = "";
        $success = "";

        if (isset($_GET["newGroup"]) && isset($_POST["newName"]) && !empty($_POST["newName"])) {
            do {
                $newName = $_POST["newName"];

                //check if name does not exist
                $result = modelCall('groups', 'checkIfGroupNameExist', ['db' => getDatabaseEnvConn('sqlite'), "groupName" => $newName]);
                if ($result != false) {
                    $error = "Group name already exist or nothing to change.";
                    break;
                }

                //check if at least 3 chars and max 20 chars
                if (strlen($newName) < 3 || strlen($newName) > 20) {
                    $error = "Group name must be at least 3 characters and maximum 20 characters long.";
                    break;
                }

                //check if only letters and numbers
                if (!ctype_alnum($newName)) {
                    $error = "Group name can only contain letters and numbers.";
                    break;
                }

                //create group
                modelCall('groups', 'createGroup', ['db' => getDatabaseEnvConn('sqlite'), "groupName" => $newName]);
                $success = "Group created succesfully.";

            } while (false);
        }
        
        if (isset($_GET["changeName"]) && isset($_POST["newName"]) && !empty($_POST["newName"]) && isset($_POST["groupId"]) && !empty($_POST["groupId"])) {
            do {
                $newName = $_POST["newName"];
                $groupId = $_POST["groupId"];

                //check if name does not exist
                $result = modelCall('groups', 'checkIfGroupNameExist', ['db' => getDatabaseEnvConn('sqlite'), "groupName" => $newName]);
                if ($result != false) {
                    $error = "Group name already exist or nothing to change.";
                    break;
                }

                //check if group exist
                $result = modelCall('groups', 'getGroupInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $groupId]);
                if ($result == false) {
                    $error = "Group does not exist.";
                    break;
                }

                //check if at least 3 chars and max 20 chars
                if (strlen($newName) < 3 || strlen($newName) > 20) {
                    $error = "Group name must be at least 3 characters and maximum 20 characters long.";
                    break;
                }

                //check if only letters and numbers
                if (!ctype_alnum($newName)) {
                    $error = "Group name can only contain letters and numbers.";
                    break;
                }

                //update
                modelCall('groups', 'renameGroup', ['db' => getDatabaseEnvConn('sqlite'), "groupName" => $newName, "id" => $groupId]);
                $success = "Group name changed succesfully.";
                

            } while (false);
        }

        if (isset($_GET["manageUsers"]) && isset($_POST["groupId"]) && !empty($_POST["groupId"])) {
            do {
                //check if group exist
                $groupId = $_POST["groupId"];

                $result = modelCall('groups', 'getGroupInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $groupId]);
                if ($result == false) {
                    $error = "Group does not exist.";
                    break;
                }

                //remove all users from group
                modelCall('groups', 'removeAllUsersFromGroup', ['db' => getDatabaseEnvConn('sqlite'), "groupId" => $groupId]);

                if (isset($_POST["selectedUsers"])) {
                    for ($i = 0; $i < count($_POST["selectedUsers"]); $i++) {
                        //check if user exist
                        $userId = $_POST["selectedUsers"][$i];
                        $result = modelCall('users', 'getUsersInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $userId]);
                        if ($result == false) {
                            continue;
                        }

                        //check if user is not admin
                        if ($result["privilegeLevel"] == 1) {
                            continue;
                        }

                        //add user to group
                        modelCall('groups', 'addUserToGroup', ['db' => getDatabaseEnvConn('sqlite'), "groupId" => $groupId, "userId" => $userId]);
                        $success = "All changes saved.";
                    }
                }

                
            } while (false);
        }

        if (isset($_GET["deleteGroup"]) && isset($_POST["groupId"]) && !empty($_POST["groupId"])) {
            do {
                //check if group exist
                $groupId = $_POST["groupId"];

                $result = modelCall('groups', 'getGroupInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $groupId]);
                if ($result == false) {
                    $error = "Group does not exist.";
                    break;
                }

                //remove all users from group
                modelCall('groups', 'removeAllUsersFromGroup', ['db' => getDatabaseEnvConn('sqlite'), "groupId" => $groupId]);

                //remove all privileges with this group
                modelCall('privileges', 'removeAllPrivilegesWithGroup', ['db' => getDatabaseEnvConn('sqlite'), "groupId" => $groupId]);

                //delete group
                modelCall('groups', 'deleteGroup', ['db' => getDatabaseEnvConn('sqlite'), "id" => $groupId]);
                $success = "Group deleted succesfully.";

            } while (false);
        }


        $groups = modelCall('groups', 'getAllGroups', ['db' => getDatabaseEnvConn('sqlite')]);
        for ($i = 0; $i < count($groups); $i++) {
            $groups[$i]["users"] = modelCall('groups', 'getUsernamesInGroup', ['db' => getDatabaseEnvConn('sqlite'), "groupId" => $groups[$i]["id"]]);
        }
        $users = modelCall('users', 'getUsersWithPrivilege', ['db' => getDatabaseEnvConn('sqlite'), "privilegeLevel" => 2]);

        $template = processTemplate("groups", ["pageTitle" => "Groups", "groups" => $groups, "users" => $users, "error" => $error, "success" => $success]);
        finishRender($template);
    });


    checkRoute('GET', '/dashboard/drives' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();

        $drives = modelCall('drives', 'getAllDrives', ['db' => getDatabaseEnvConn('sqlite')]);

        $template = processTemplate("drives", ["pageTitle" => "Drives", "drives" => $drives]);
        finishRender($template);
    });

    checkRoute('POST', '/dashboard/drives' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();

        $error = "";
        $success = "";

        //todo
        if (isset($_GET["managePrivileges"])) {
            do {
                //get all groups and check if in post
                $groups = modelCall('groups', 'getAllGroups', ['db' => getDatabaseEnvConn('sqlite')]);

                if (!isset($_POST["selectedDrive"]) || empty($_POST["selectedDrive"])) {
                    $error = "No drive selected.";
                    break;
                }

                //check if drive exist
                $driveId = $_POST["selectedDrive"];
                $result = modelCall('drives', 'checkIfDriveExist', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $driveId]);
                if ($result == false) {
                    $error = "Drive does not exist.";
                    break;
                }


                for ($i = 0; $i < count($groups); $i++) {
                    if (isset($_POST["grp-" . $groups[$i]["id"]])) {
                        $grpId = $groups[$i]["id"];
                        $grpVal = $_POST["grp-" . $groups[$i]["id"]];

                        if ($grpVal === "none") {
                            modelCall('privileges', 'removePrivilege', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $driveId, "groupId" => $grpId]);
                        } else if ($grpVal == "-2" || $grpVal == "-1" || $grpVal == "1" || $grpVal == "2") {
                            modelCall('privileges', 'setPrivilege', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $driveId, "groupId" => $grpId, "privilegeLevel" => $grpVal]);
                        }
                    }
                }

                $success = "All changes saved.";

            } while (false);
        }

        $drives = modelCall('drives', 'getAllDrives', ['db' => getDatabaseEnvConn('sqlite')]);

        $template = processTemplate("drives", ["pageTitle" => "Drives", "drives" => $drives, "error" => $error, "success" => $success]);
        finishRender($template);
    });

    checkRoute('GET', '/dashboard/account' , function() {
        redirectNotLogin();

        $userInfo = modelCall('users', 'getUsersInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $_SESSION["userId"]]);
        $template = processTemplate("account", ["pageTitle" => "Account", "userInfo" => $userInfo]);
        finishRender($template);
    });


    checkRoute('POST', '/dashboard/account' , function() {
        redirectNotLogin();
        $userInfo = modelCall('users', 'getUsersInfo', ['db' => getDatabaseEnvConn('sqlite'), "id" => $_SESSION["userId"]]);
        $error = "";

        if (isset($_GET["changeName"]) && isset($_POST["newName"]) && !empty($_POST["newName"])) {
            do {
                $newName = $_POST["newName"];

                //check if user is admin
                if ($_SESSION["privilegeLevel"] != 1) {
                    $error = "You are not allowed to change your username.";
                    break;
                }
                
                //check if not exist
                $result = modelCall('users', 'checkIfUsernameExist', ['db' => getDatabaseEnvConn('sqlite'), "username" => $newName]);

                if ($result != false) {
                    $error = "Username already exist.";
                    break;
                }

                //check if at least 5 chars
                if (strlen($newName) < 3 || strlen($newName) > 20) {
                    $error = "Username must be at least 3 characters and maximum 20 characters long.";
                    break;
                }

                //check if only letters and numbers
                if (!ctype_alnum($newName)) {
                    $error = "Username can only contain letters and numbers.";
                    break;
                }

                //save new name
                modelCall('users', 'changeUsername', ['db' => getDatabaseEnvConn('sqlite'), "id" => $_SESSION["userId"], "newName" => $newName]);

                //logout
                session_destroy();
                header('Location: ' . getAppEnvVar("BASE_URL") . "/" . getAppEnvVar("LOGIN_URL") . "?newName");

            } while (false);
        }

        if (isset($_GET["changePassword"]) && isset($_POST["oldPassword"]) && !empty($_POST["oldPassword"]) && isset($_POST["newPassword"]) && !empty($_POST["newPassword"]) && isset($_POST["newPasswordVerify"]) && !empty($_POST["newPasswordVerify"])) {
            do {
                $oldPassword = $_POST["oldPassword"];
                $newPassword = $_POST["newPassword"];
                $newPasswordVerify = $_POST["newPasswordVerify"];

                //check if old password is correct3
                $result = modelCall('users', 'checkIfPasswordMatch', ['db' => getDatabaseEnvConn('sqlite'), "id" => $_SESSION["userId"], "password" => hash("sha256", $oldPassword)]);
                if ($result == false) {
                    $error = "Old password is incorrect.";
                    break;
                }

                //check if newPassword is the same as newPasswordVerify
                if ($newPassword != $newPasswordVerify) {
                    $error = "New password does not match with control password.";
                    break;
                }

                //check if newPassword is at least 6 chars
                if (strlen($newPassword) < 6) {
                    $error = "New password must be at least 6 characters long.";
                    break;
                }

                //update
                modelCall('users', 'changePassword', ['db' => getDatabaseEnvConn('sqlite'), "id" => $_SESSION["userId"], "newPassword" => hash("sha256", $newPassword)]);

                //logout
                session_destroy();
                header('Location: ' . getAppEnvVar("BASE_URL") . "/" . getAppEnvVar("LOGIN_URL") . "?newPassword");

            } while (false);
        }


        $template = processTemplate("account", ["pageTitle" => "Account", "userInfo" => $userInfo, "error" => $error]);
        finishRender($template);
    });


    checkRoute('GET', '/dashboard/logout' , function() {
        redirectNotLogin();

        session_destroy();
        header('Location: ' . getAppEnvVar("BASE_URL") . "/" . getAppEnvVar("LOGIN_URL") . "?logout");
    });


    function redirectNotLogin() {
        if (!isset($_SESSION["userId"])) {
            if (getAppEnvVar("REDIRECT_IF_NOT_LOGGED") == true) {
                header('Location: ' . getAppEnvVar("BASE_URL") . "/" . getAppEnvVar("LOGIN_URL"));
            } else {
                require_once "engine/errors/401.php";
            }
            die();
        } else {
            //check if something changed in user account
            $result = modelCall('users', 'verifyNothingChanged', ['db' => getDatabaseEnvConn('sqlite'), "username" => $_SESSION["username"], "privilegeLevel" => $_SESSION["privilegeLevel"], "passwordCheck" => $_SESSION["passwordCheck"]]);
            if ($result == false) {
                session_destroy();
                header('Location: ' . getAppEnvVar("BASE_URL") . "/" . getAppEnvVar("LOGIN_URL") . "?accountChanged");
                die();
            }
        }
    }

    function redirectIfNotAdmin() {
        if ($_SESSION["privilegeLevel"] != 1) {
            header('Location: ' . getAppEnvVar("BASE_URL") . "/dashboard");
            die();
        }
    }
?>