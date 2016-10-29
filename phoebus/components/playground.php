<?php
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this
// file, You can obtain one at http://mozilla.org/MPL

// == | Vars | ================================================================

$strRequestMode = funcHTTPGetValue('mode');

// ============================================================================

function funcConvertAddon($_type, $_slug) {
    $addonManifest = funcReadManifest($_type, $_slug, 0, false);
    
    $addonNewManifestINI = '[addon]
type="' . $addonManifest['type'] . '"
id="' . $addonManifest['guid'] . '"
release="' . $addonManifest['xpi'] . '"
unstable="none"

[metadata]
name="' . $addonManifest['name'] . '"
slug="' . $_slug . '"
author="' . $addonManifest['author'] . '"
shortDescription="' . 'unknown' . '"
licence="none"
homepageURL="none"
supportURL="none"

[' . $addonManifest['xpi'] . ']
version="' . $addonManifest['version'] . '"
minAppVersion="' . $addonManifest['minVer'] . '"
maxAppVersion="' . $addonManifest['maxVer'] . '"';

    $addonContent = $addonManifest["description"];
    $addonContent = str_replace('<p>', '', $addonContent);
    $addonContent = str_replace('<br />', "\n", $addonContent);
    $addonContent = str_replace('</p>', "\n", $addonContent);
    
    return array($addonNewManifestINI, $addonContent);

}

// ============================================================================

if ($strRequestMode == null) {
    funcError('Mode is null.. Dumbass');
}

if ($strRequestMode == 'convert') {
    include_once($arrayModules['readManifest']);
    include_once($arrayModules['dbExtensions']);
    include_once($arrayModules['dbThemes']);
    
    header('Content-Type: text/plain');
    foreach ($arrayExtensionsDB as $_key => $_value) {
        print('== | ' . $_value . ' | =============' . "\n");
        $arrayOut = funcConvertAddon('extension', $_value);
        print('phoebus.manifest:' . "\n" . $arrayOut[0] . "\n\n" . 'phoebus.content:' . "\n" . $arrayOut[1] . "\n");
        print('===================================' . "\n\n");

    }
    foreach ($arrayThemesDB as $_key => $_value) {
        print('== | ' . $_value . ' | =============' . "\n");
        $arrayOut = funcConvertAddon('theme', $_value);
        print('phoebus.manifest:' . "\n" . $arrayOut[0] . "\n\n" . 'phoebus.content:' . "\n" . $arrayOut[1] . "\n");
        print('===================================' . "\n\n");
    }
}
else {
    funcError('Invalid Mode');
}

// ============================================================================
?>