<?php
// == | funcProcessContent | ==================================================

function funcProcessContent ($_input) {
    // Parse phoebus.content
    if (file_exists($_input)) {
        // Read phoebus.content
        $_addonPhoebusContent = file_get_contents($_input);
        
        // html encode phoebus.content
        $_addonPhoebusContent = htmlentities($_addonPhoebusContent, ENT_XHTML);
        
        // create a temporary array that contains the strs to simple pseudo-bbcode to real html
        $_arrayPhoebusCodeSimple = array(
            "\r\n" => "\n",
            "\n" => "<br />\n",
            '[b]' => '<strong>',
            '[/b]' => '</strong>',
            '[i]' => '<em>',
            '[/i]' => '</em>',
            '[u]' => '</p><u>',
            '[/u]' => '</u><p>',
            '[ul]' => '<ul>',
            '[/ul]' => '</ul>',
            '[li]' => '<li>',
            '[/li]' => '</li>',
            '[section]' => '</p><h3>',
            '[/section]' => '</h3><p>'
        );
        
        // create a temporary array that contains the regex to convert pseudo-bbcode to real html
        $_arrayPhoebusCodeRegex = array(
            '\<(ul|\/ul|li|\/li|p|\/p)\><br \/>' => '<$1>',
            '\[url=(.*)\](.*)\[\/url\]' => '<a href="$1" target="_blank">$2</a>',
            '\[url\](.*)\[\/url\]' => '<a href="$1" target="_blank">$1</a>',
            '\[img(.*)\](.*)\[\/img\]' => '<img src="$2"$1 />'
        );
        
        // str replace pseudo-bbcode with real html
        foreach ($_arrayPhoebusCodeSimple as $_key => $_value) {
            $_addonPhoebusContent = str_replace($_key, $_value, $_addonPhoebusContent);
        }
        
        // Regex replace pseudo-bbcode with real html
        foreach ($_arrayPhoebusCodeRegex as $_key => $_value) {
            $_addonPhoebusContent = preg_replace('/' . $_key . '/iU', $_value, $_addonPhoebusContent);
        }
        
        // Clear the temporary arrays out of memory
        unset($_arrayPhoebusCodeSimple);
        unset($_arrayPhoebusCodeRegex);
        
        return $_addonPhoebusContent;       
    }
    else {
        return null;
    }
};
// ============================================================================
?>