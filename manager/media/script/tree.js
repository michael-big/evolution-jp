// return window dimensions in array
function getWindowDimension() {
    var width = 0;
    var height = 0;

    if (typeof (window.innerWidth) == 'number') {
        width = window.innerWidth;
        height = window.innerHeight;
    } else if (document.documentElement &&
        (document.documentElement.clientWidth ||
            document.documentElement.clientHeight)) {
        width = document.documentElement.clientWidth;
        height = document.documentElement.clientHeight;
    } else if (document.body &&
        (document.body.clientWidth || document.body.clientHeight)) {
        width = document.body.clientWidth;
        height = document.body.clientHeight;
    }

    return {'width': width, 'height': height};
}

function resizeTree() {

    // get window width/height
    var win = getWindowDimension();

    // set tree height
    var tree = document.getElementById('treeHolder');
    var tmnu = document.getElementById('treeMenu');
    tree.style.width = (win['width'] - 20) + 'px';
    tree.style.height = (win['height'] - tree.offsetTop - 6) + 'px';
    tree.style.overflow = 'auto';
}

function getScrollY() {
    var scrOfY = 0;
    if (typeof (window.pageYOffset) == 'number') {
        //Netscape compliant
        scrOfY = window.pageYOffset;
    } else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
        //DOM compliant
        scrOfY = document.body.scrollTop;
    } else if (document.documentElement &&
        (document.documentElement.scrollTop)) {
        //IE6 standards compliant mode
        scrOfY = document.documentElement.scrollTop;
    }
    return scrOfY;
}

function hideMenu() {
    if (_rc) return false;
    jQuery('#mx_contextmenu').css('visibility', 'hidden');
}

function rpcLoadData(response) {
    if (rpcNode == null) {
        return;
    }
    rpcNode.innerHTML = response;
    rpcNode.style.display = 'block';
    rpcNode.loaded = true;
    var elm = top.mainMenu.document.getElementById('buildText');
    if (elm) {
        elm.innerHTML = '';
        elm.style.display = 'none';
    }
    // check if bin is full
    if (rpcNode.id === 'treeRoot') {
        if (document.getElementById('binFull')) {
            showBinFull();
        } else {
            showBinEmpty();
        }
    }

    // check if our payload contains the login form :)
    if (document.getElementById('mx_loginbox')) {
        // yep! the seession has timed out
        rpcNode.innerHTML = '';
        top.location = 'index.php';
    }
}

function expandTree() {
    rpcNode = document.getElementById('treeRoot');
    jQuery.get('index.php', {
        "a": "1",
        "f": "nodes",
        "indent": "1",
        "parent": "0",
        "expandAll": "1"
    }, rpcLoadData);
}

function collapseTree() {
    rpcNode = document.getElementById('treeRoot');
    jQuery.get('index.php', {
        "a": "1",
        "f": "nodes",
        "indent": "1",
        "parent": "0",
        "expandAll": "0"
    }, rpcLoadData);
}

// new function used in body onload
function restoreTree() {
    rpcNode = document.getElementById('treeRoot');
    jQuery.get('index.php', {
        "a": "1",
        "f": "nodes",
        "indent": "1",
        "parent": "0",
        "expandAll": "2"
    }, rpcLoadData);
}

function setSelected(elSel) {
    var all = document.getElementsByTagName("SPAN");
    var l = all.length;

    for (var i = 0; i < l; i++) {
        el = all[i];
        cn = el.className;
        if (cn === "treeNodeSelected") {
            el.className = "treeNode";
        }
    }
    elSel.className = "treeNodeSelected";
}

function setHoverClass(el, dir) {
    if (el.className !== "treeNodeSelected") {
        if (dir == 1) {
            el.className = "treeNodeHover";
        } else {
            el.className = "treeNode";
        }
    }
}

// set Context Node State
function setCNS(n, b) {
    if (b == 1) {
        n.style.backgroundColor = "beige";
    } else {
        n.style.backgroundColor = "";
    }
}

function updateTree() {
    rpcNode = document.getElementById('treeRoot');
    var dt = document.sortFrm.dt.value;
    var t_sortby = document.sortFrm.sortby.value;
    var t_sortdir = document.sortFrm.sortdir.value;

    jQuery.get('index.php', {
        "a": "1",
        "f": "nodes",
        "indent": "1",
        "parent": "0",
        "expandAll": "2",
        "dt": dt,
        "tree_sortby": t_sortby,
        "tree_sortdir": t_sortdir
    }, rpcLoadData);
}

//Raymond: added getFolderState,saveFolderState
function getFolderState() {
    if (openedArray == [0]) {
        return "&opened=";
    }
    oarray = "&opened=";
    for (key in openedArray) {
        if (openedArray[key] == 1) {
            oarray += key + "|";
        }
    }
    return oarray.replace(/\|$/, '');
}

function saveFolderState() {
    var folderState = getFolderState();
    url = 'index.php?a=1&f=nodes&savestateonly=1' + folderState;
    jQuery.get(url);
}

function showSorter() {
    if (currSorterState === "none") {
        currSorterState = "block";
        document.getElementById('floater').style.display = currSorterState;
    } else {
        currSorterState = "none";
        document.getElementById('floater').style.display = currSorterState;
    }
}

