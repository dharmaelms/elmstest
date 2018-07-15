var getBadgeClass = function (count) {
    if (count > 0) {
        return "badge badge-success";
    } else {
        return "badge badge-grey";
    }
};

var convertTimestampToDate = function(timestampInSeconds) {
    var date = new Date(timestampInSeconds * 1000);
    return date.getDate()+"-"+(date.getMonth() + 1)+"-"+date.getFullYear();
};

var htmlEntities = function(str) {
	return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
};
