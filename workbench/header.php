<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Language" content="UTF-8" />
        <meta http-equiv="Content-Type" content="text/xhtml; charset=UTF-8" />

        <link rel="Shortcut Icon" type="image/png" href="<?php echo getPathToStaticResource('/images/bluecube-16x16.png'); ?>" />

        <link rel="stylesheet" type="text/css" href="<?php echo getPathToStaticResource('/style/master.css'); ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo getPathToStaticResource('/style/pro_dropdown.css'); ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo getPathToStaticResource('/style/simpletree.css'); ?>" />

        <?php
        $myPage = getMyPage();
        $title = $myPage->showTitle ? ": " . $myPage->title : "";
        print "<title>Workbench$title</title>";

        print "<script type='text/javascript'>var getPathToStaticResource = " . getPathToStaticResourceAsJsFunction() . ";</script>";
        ?>
        
		<script type="text/javascript" src="<?php echo getPathToStaticResource('/script/pro_dropdown.js'); ?>"></script>
    </head>
<body>

<?php
if (WorkbenchConfig::get()->isConfigured("displayLiveMaintenanceMessage")) {
    print "<div style='background-color: orange; width: 100%; padding: 2px; font-size: 8pt; font-weight: bold;'>" .
              "Workbench is currently undergoing maintenance. The service may be intermittently unavailable during this time.</div><br/>";
}

//check for latest version
if (WorkbenchConfig::get()->value("checkForLatestVersion") && extension_loaded('curl') && (isset($_GET['autoLogin']) || 'login.php'==basename($_SERVER['PHP_SELF']))) {
    try {
        $ch = curl_init();
        if (stristr($GLOBALS["WORKBENCH_VERSION"],'beta')) {
            curl_setopt ($ch, CURLOPT_URL, 'http://forceworkbench.sourceforge.net/latestVersionAvailableBeta.txt');
        } else {
            curl_setopt ($ch, CURLOPT_URL, 'http://forceworkbench.sourceforge.net/latestVersionAvailable.txt');
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $latestVersionAvailable = trim(curl_exec($ch));
        curl_close($ch);

        if (preg_match('/^[0-9]+.[0-9]+/',$latestVersionAvailable) && !stristr($GLOBALS["WORKBENCH_VERSION"],'trunk') && !stristr($GLOBALS["WORKBENCH_VERSION"],'alpha') && !stristr($GLOBALS["WORKBENCH_VERSION"],'i')) {
            if ($latestVersionAvailable != $GLOBALS["WORKBENCH_VERSION"]) {
                print "<div style='background-color: #EAE9E4; width: 100%; padding: 2px;'><a href='http://code.google.com/p/forceworkbench/' target='_blank' style='font-size: 8pt; font-weight: bold; color: #0046ad;'>A newer version of Workbench is available for download</a></div><br/>";
            }
        }
    } catch (Exception $e) {
        //do nothing
    }
}
?>


<div id='mainBlock'>

<div id='navMenu' style="clear: both;">
    <span class="preload1"></span>
    <span class="preload2"></span>
    <ul id="nav">
    <?php
    foreach ($GLOBALS["MENUS"] as $menu => $pages) {
        if (isReadOnlyMode() && $menu == "Data") { //special-case for Data menu, since all read-only
            continue;
        }
        $menuLabel = ($menu == "WORKBENCH") ? "&nbsp;<img src='" . getPathToStaticResource('/images/workbench-3-cubed-white-small.png') . "'/>" : strtolower($menu);
        print "<li class='top'><a class='top_link'><span class='down'>" . $menuLabel ."</span></a>\n" .
                  "<ul class='sub'>";
        foreach ($pages as $href => $page) {
            if (!$page->onNavBar || (!isLoggedIn() && $page->requiresSfdcSession) || (isLoggedIn() && $page->title == 'Login') || (!$page->isReadOnly && isReadOnlyMode())) {
                continue;
            }
            print "<li><a href='$href' onmouseover=\"Tip('$page->desc')\" target=\"" . $page->window . "\">$page->title</a></li>\n";
        }
        print "</ul></li>";
    
        if(!isLoggedIn() || !termsOk()) break; //only show first "Workbench" menu in these cases
    }
    ?>
    </ul>
</div>

<?php
print "<table width='100%' border='0'><tr>";
if ($myPage->showTitle) {
    print "<td id='pageTitle'>" . $myPage->title . "</td>";
}
if (isLoggedIn()) {
    $userInfo = WorkbenchContext::get()->getUserInfo();
    $infoTips = array("Username: " . $userInfo->userName,
                      "Instance: " . WorkbenchContext::get()->getHost(),
                      "Org Id:&nbsp;&nbsp;" . substr($userInfo->organizationId, 0, 15),
                      "User Id:&nbsp;" . substr($userInfo->userId, 0, 15));

    print "<td id='myUserInfo'><a href='sessionInfo.php' onmouseover=\"Tip('". implode("<br/>", $infoTips) ."')\" >" .
           htmlspecialchars($userInfo->userFullName . " at " . $userInfo->organizationName) . " on API " . WorkbenchContext::get()->getApiVersion() . "</a></td>";
}
print "</tr></table>";

if (isset($errors)) {
    print "<p/>";
    displayError($errors, false, true);
}


if (!termsOk() && $myPage->requiresSfdcSession) {
    ?>
    <div style="margin-left: 95px; margin-top: 10px;">
        <form method="POST" action="">
            <input type="checkbox" id="termsAccepted" name="termsAccepted"/>
            <label for="termsAccepted"><a href="terms.php" target="_blank">I agree to the terms of service</a></label>
            <input type="submit" value="Continue" style="margin-left: 10px; "/>
        </form>
    </div>
    <?php
    exit;
}
?>