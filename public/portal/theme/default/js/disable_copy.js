document.onkeypress = function(event) {
    event = (event || window.event);
    if (event.keyCode === enums.keyboard.F12) {
        return false;
    }
};
document.onmousedown = function(event) {
    event = (event || window.event);
    if (event.keyCode === enums.keyboard.F12) {
        return false;
    }
};
document.onkeydown = function(event) {
    event = (event || window.event);
    if (event.keyCode === enums.keyboard.F12) {
        return false;
    }
};
function cp() {return false;}
document.oncontextmenu = cp;
document.onmouseup = cp;
var isCtrl = false;
window.onkeyup = function(e) {
    if (e.which == enums.keyboard.CTRL)
    isCtrl = false;
};
window.onkeydown = function(e) {
    if (e.which == enums.keyboard.CTRL)
    isCtrl = true;
    if ((((e.which == enums.keyboard.KEY_A) || (e.which == enums.keyboard.KEY_C) || (e.which == enums.keyboard.KEY_S) || e.which == enums.keyboard.KEY_U) || (e.which == enums.keyboard.KEY_V) || (e.which == enums.keyboard.KEY_X)) && isCtrl == true) {
        return false;
    }
};
document.ondragstart = cp;  