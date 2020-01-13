"use strict";

$("[data-toggle='tooltip']").tooltip();

let rcms = {};

rcms.refreshPjaxById = function (id, async = false) {
    $.pjax.reload({container: '#' + id, async: async});
};