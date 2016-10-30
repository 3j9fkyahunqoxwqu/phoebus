<?php
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this
// file, You can obtain one at http://mozilla.org/MPL

// == | Vars | ================================================================

$boolAMOKillSwitch = false;
$boolAMOWhiteList = false;

$arrayIncludes = array(
    $arrayModules['dbExtensions'],
    $arrayModules['dbThemes'],
    $arrayModules['dbLangPacks'],
    $arrayModules['dbAUSExternals'],
    $arrayModules['readManifest'],
    $arrayModules['vc']
);

$strRequestAddonID = funcHTTPGetValue('id');
$strRequestAddonVersion = funcHTTPGetValue('version');
$strRequestAppID = funcHTTPGetValue('appID');
$strRequestAppVersion = funcHTTPGetValue('appVersion');
$strRequestCompatMode = funcHTTPGetValue('compatMode');

// ============================================================================

// == | funcGenerateUpdateXML | ===============================================

function funcGenerateUpdateXML($_addonManifest) {
    $_strUpdateXMLHead = '<?xml version="1.0"?>' . "\n" . '<RDF:RDF xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:em="http://www.mozilla.org/2004/em-rdf#">';
    $_strUpdateXMLTail = '</RDF:RDF>';

    header('Content-Type: text/xml');

    print($_strUpdateXMLHead);

    if ($_addonManifest != null) {
            print("\n");
            
            $_strUpdateXMLBody = file_get_contents('./phoebus/components/aus/update-body.xml');
            
            if ($_addonManifest['isNewManifest'] == true) {
                $_arrayFilterSubstitute = array(
                    '@ADDON_TYPE@' => $_addonManifest['addon']['type'],
                    '@ADDON_ID@' => $_addonManifest['addon']['id'],
                    '@ADDON_VERSION@' => $_addonManifest['xpi'][$_addonManifest['addon']['release']]['version'],
                    '@PALEMOON_ID@' => $GLOBALS['strPaleMoonID'],
                    '@ADDON_MINVERSION@' => $_addonManifest['xpi'][$_addonManifest['addon']['release']]['minAppVersion'],
                    '@ADDON_MAXVERSION@' => $_addonManifest['xpi'][$_addonManifest['addon']['release']]['maxAppVersion'],
                    '@ADDON_XPI@' => $_addonManifest['addon']['baseURL'] . $_addonManifest['addon']['release'],
                    '@ADDON_HASH@' => $_addonManifest['addon']['hash']
            );
            }
            elseif ($_addonManifest['isNewManifest'] == false) {
                $_arrayFilterSubstitute = array(
                    '@ADDON_TYPE@' => $_addonManifest['type'],
                    '@ADDON_ID@' => $_addonManifest['guid'],
                    '@ADDON_VERSION@' => $_addonManifest['version'],
                    '@PALEMOON_ID@' => $GLOBALS['strPaleMoonID'],
                    '@ADDON_MINVERSION@' => $_addonManifest['minVer'],
                    '@ADDON_MAXVERSION@' => $_addonManifest['maxVer'],
                    '@ADDON_XPI@' => $_addonManifest['baseurl'] . $_addonManifest['xpi'],
                    '@ADDON_HASH@' => $_addonManifest['hash']
                );
            }
            foreach ($_arrayFilterSubstitute as $_key => $_value) {
                $_strUpdateXMLBody = str_replace($_key, $_value, $_strUpdateXMLBody);
            }
            
            print("\n");
            print($_strUpdateXMLBody);
    }
    
    print($_strUpdateXMLTail);
    
    // We are done here...
    exit();
}

// ============================================================================

// == | Main | ================================================================

// funcGenerateUpdateXML(funcReadAddonManifest('extension', 'adblock-latitude', 1));

// Sanity
if ($strRequestAddonID == null || $strRequestAddonVersion == null ||
    $strRequestAppID == null || $strRequestAppVersion == null ||
    $strRequestCompatMode == null) {
    funcError('Missing minimum required arguments.');
}

if ($strRequestAppID != $strPaleMoonID) {
    funcError('Invalid Application ID');
}

// Include modules
foreach($arrayIncludes as $_value) {
    include_once($_value);
}
unset($arrayIncludes);

// Search for add-ons in our databases
// Extensions
if (array_key_exists($strRequestAddonID, $arrayExtensionsDB)) {
    funcGenerateUpdateXML(funcReadManifest('extension', $arrayExtensionsDB[$strRequestAddonID], 1, true));
}
elseif(array_key_exists($strRequestAddonID, $arrayExtensionsOverrideDB)) {
    funcGenerateUpdateXML(funcReadManifest('extension', $arrayExtensionsOverrideDB[$strRequestAddonID], 1, true));
}
// Themes
elseif (array_key_exists($strRequestAddonID, $arrayThemesDB)) {
    funcGenerateUpdateXML(funcReadManifest('theme', $arrayThemesDB[$strRequestAddonID], 1, true));
}
// Language Packs
elseif (array_key_exists($strRequestAddonID, $arrayLangPackDB)) {
    $arrayLangPack = array(
        'addon' => array(
                    'type' => 'item',
                    'id' => $strRequestAddonID,
                    'release' => $arrayLangPackDB[$strRequestAddonID]['locale'] . '.xpi',
                    'baseURL' => 'http://relmirror.palemoon.org/langpacks/26.x/',
                    'hash' => $arrayLangPackDB[$strRequestAddonID]['hash']),
        'xpi' => array(
                    $arrayLangPackDB[$strRequestAddonID]['locale'] . '.xpi' => array(
                        'version' => $arrayLangPackDB[$strRequestAddonID]['version'],
                        'minAppVersion' => '26.0.0a1',
                        'maxAppVersion' => '26.*')),
        'isNewManifest' => true
    );
    
    funcGenerateUpdateXML($arrayLangPack);
}
// Externals
elseif (array_key_exists($strRequestAddonID, $arrayExternalsDB)) {
    funcRedirect($strRequestAddonID);
}
// Unknown - Send to AMO or to 'bad' update xml
else {
    if ($boolAMOKillSwitch == false) {
        $intVcResult = ToolkitVersionComparator::compare($strRequestAppVersion, '27.0.0a1');
        $_strFirefoxVersion = $strFirefoxVersion;
        
        if ($intVcResult < 0) {
            $_strFirefoxVersion = '24.9';
        }
        
        $strAMOLink = 'https://versioncheck.addons.mozilla.org/update/VersionCheck.php?reqVersion=2' .
        '&id=' . $strRequestAddonID .
        '&version=' . $strRequestAddonVersion .
        '&appID=' . $strFirefoxID .
        '&appVersion=' . $_strFirefoxVersion .
        '&compatMode=' . $strRequestCompatMode;
        
        funcRedirect($strAMOLink);
    }
    else {
        funcGenerateUpdateXML(null);
    }
}

// ============================================================================
?>