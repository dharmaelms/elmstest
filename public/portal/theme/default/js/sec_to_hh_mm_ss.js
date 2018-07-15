/**
 *
 * @param totalSeconds
 * @returns {string}
 */
var secondsTohHHMMSS = function(totalSeconds) {
    var hours   = Math.floor(totalSeconds / 3600);
    var minutes = Math.floor((totalSeconds - (hours * 3600)) / 60);
    var seconds = totalSeconds - (hours * 3600) - (minutes * 60);

    // round seconds
    seconds = Math.round(seconds * 100) / 100;

    var result = (hours < 10 ? "0" + hours : hours);
    result += ":" + (minutes < 10 ? "0" + minutes : minutes);
    result += ":" + (seconds  < 10 ? "0" + seconds : seconds);
    return result;
};

/**
 *
 * @param HH_MM_SS
 * @returns {number}
 */
var HHMMSSToSeconds = function (HH_MM_SS) {
    var timeFragments = HH_MM_SS.split(":");
    var hoursInSeconds = parseInt(timeFragments[0]) * 3600;
    var minutesInSeconds = parseInt(timeFragments[1]) * 60;
    var seconds = parseInt(timeFragments[2]);
    return hoursInSeconds + minutesInSeconds + seconds;
};
