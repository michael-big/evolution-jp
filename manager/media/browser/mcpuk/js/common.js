/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * File Name: common.js
 * 	Common objects and functions shared by all pages that compose the
 * 	File Browser dialog window.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

function AddSelectOption(selectElement, optionText, optionValue, sel) {
    var oOption = document.createElement("OPTION");

    oOption.text = optionText;
    oOption.value = optionValue;
    if (sel) oOption.selected = true;

    selectElement.options.add(oOption);

    return oOption;
}

function GetUrlParam(paramName) {
    var oRegex = new RegExp('[\?&]' + paramName + '=([^&]+)', 'i');
    var oMatch = oRegex.exec(unescape(window.top.location.search));

    if (oMatch && oMatch.length > 1)
        return oMatch[1];
    else
        return '';
}

function GetMyUrlParam(paramName) {
    var oRegex = new RegExp('[\?&]' + paramName + '=([^&]+)', 'i');
    var oMatch = oRegex.exec(window.location.search);

    if (oMatch && oMatch.length > 1)
        return oMatch[1];
    else
        return '';
}

var oConnector = {};
oConnector.CurrentFolder = '/';
oConnector.UploadHandler = GetUrlParam('UploadHandler');
var pathname = window.location.pathname.replace(/manager\/media\/browser\/mcpuk\/.*$/, '');
oConnector.ConnectorUrl = pathname + 'manager/media/browser/mcpuk/connectors/connector.php';
oConnector.ResourceType = GetUrlParam('Type');
oConnector.ExtraParams = GetUrlParam('ExtraParams');
oConnector.Editor = GetUrlParam('editor');

if ((oConnector.UploadHandler == '') || (oConnector.UploadHandler == 'undefined')) oConnector.UploadHandler = oConnector.ConnectorUrl;


oConnector.SendCommand = function (command, params, callBackFunction) {
    var sUrl = this.ConnectorUrl + '?Command=' + command;
    sUrl += '&Type=' + this.ResourceType;
    sUrl += '&ExtraParams=' + this.ExtraParams;
    sUrl += '&CurrentFolder=' + escape(this.CurrentFolder);
    sUrl += '&editor=' + escape(this.Editor);

    if (params) sUrl += '&' + params;

    var oXML = new FCKXml();
    if (callBackFunction)
        oXML.LoadUrl(sUrl, callBackFunction);	// Asynchronous load.
    else
        return oXML.LoadUrl(sUrl);
}

var oIcons = {};

oIcons.AvailableIconsArray = [
    'ai', 'avi', 'bmp', 'cs', 'dll', 'doc', 'exe', 'fla', 'gif', 'htm', 'html', 'jpg', 'js',
    'mdb', 'mp3', 'pdf', 'ppt', 'rdp', 'swf', 'swt', 'txt', 'vsd', 'xls', 'xml', 'zip'];

oIcons.AvailableIcons = {};

for (var i = 0; i < oIcons.AvailableIconsArray.length; i++)
    oIcons.AvailableIcons[oIcons.AvailableIconsArray[i]] = true;

oIcons.GetIcon = function (fileName) {
    var sExtension = fileName.substr(fileName.lastIndexOf('.') + 1).toLowerCase();

    if (this.AvailableIcons[sExtension] == true)
        return sExtension;
    else
        return 'default.icon';
}
