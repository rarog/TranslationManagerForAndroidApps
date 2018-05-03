/*!
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
var curResources = resources;
var refreshTranslation = false;

function enableBootstrapTooltips() {
    $(function() {
        $('[data-toggle="tooltip"]').tooltip({container: "body"});
    });
}

function hideSelectionHint(hide) {
    var node = $("#selectionHint");
    if (hide) {
        node.addClass("hidden");
    } else {
        node.removeClass("hidden");
    }
}

function hideSpinner(hide) {
    var node = $("#spinner");
    if (hide) {
        node.addClass("hidden");
    } else {
        node.removeClass("hidden");
    }
}

function hideTranslationRow(hide) {
    var node = $("#translationRow");
    if (hide) {
        node.addClass("hidden");
        $("translationTableBody").empty();
    } else {
        node.removeClass("hidden");
        var table = $("#translations").DataTable();
    }
}

function setSelectionNeededState() {
    hideSelectionHint(false);
    hideSpinner(true);
    hideTranslationRow(true);
}

function showModalError() {
	$("#modalError").modal();
}

function hideModalSpinner(hide) {
    var node = $("#modalSpinner");
    if (hide) {
    	node.css("display", "none");
    } else {
    	node.css("display", "block");
    }
}

function hideBootstrapTooltips() {
    $(".tooltip").tooltip("hide"); // Hide all currently visible tooltips
}

function addModalAlertMessage() {
    var messages = $("#modalMessages");
    messages.append("<div class=\"alert alert-danger alert-dismissable\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a><i class=\"fa fa-exclamation-circle fa-fw\" aria-hidden=\"true\"></i><span class=\"sr-only\">' .  $this->translate('Error:') . '</span> An unexpected error has occurred.</div>");
    $("#modalDetails").animate({
        scrollTop: messages.offset().top
    });
}

function showSuggestionAddEdit(id, suggestion) {
    var editArea = $("#modalContainer #suggestionAddEdit");
    var button = $("#modalContainer #suggestionAddEdit");
    var suggestionDeleteButton = $("#modalContainer #suggestionAddEdit #suggestionDeleteButton");
    var focusElement = null;

    editArea.find(".suggestionAddEditSubmit").data("suggestionid", id);
    editArea.find(".suggestionDelete").data("suggestionid", id);
    if ($.type(suggestion) === "object") {
        suggestionDeleteButton.removeClass("disabled");
    } else {
    	suggestionDeleteButton.addClass("disabled");
    }

    if (suggestionType == "String") {
        var value = "";
        if (($.type(suggestion) === "object") && ($.type(suggestion.value) === "string")) {
            value = suggestion.value;
        }

        var textArea = $("#modalContainer #suggestionAddEditText");
        textArea.val(value);
        focusElement = textArea;
    }

    editArea.collapse("show");
    $("#modalDetails").animate({
        scrollTop: editArea.offset().top
    });

    if (focusElement !== null) {
        focusElement.focus();
    }
}

function refreshTranslationEntry(app, resource, entry) {
    hideModalSpinner(false);

    $.ajax({
        url: translationsPath + "/app/" + app + "/resource/" + resource + "/entry/" + entry,
        dataType: "json",
        method: "GET"
    })
    .done(function(data) {
        if ($.type(data) == "array" && data.length == 1) {
            var table = $("#translations").DataTable();

            table.row("#translation-" + entry)
                .data(data[0])
                .draw(false);

            hideModalSpinner(true);
            enableBootstrapTooltips();
        } else {
            hideModalSpinner(true);
            showModalError();
        }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        hideModalSpinner(true);
        showModalError();
    });
}

$("#showAll").on("change", function (event) {
	var appSelect = $("#app");
	appSelect.selectpicker("deselectAll");
    $("option", appSelect).remove();

    curApps = (event.target.checked) ? appsAll : apps;
    curResources = (event.target.checked) ? resourcesAll : resources;

    $.each(curApps, function(index, value) {
    	appSelect.append('<option value="' + index + '">' + value + '</option>');
    });
});

$("#app").on("changed.bs.select", function(event, clickedIndex, newValue, oldValue) {
    setSelectionNeededState();

    var resourceSelect = $("#resource");
    $("option", resourceSelect).remove();
    if (newValue) {
        var key = $(this).val();
        if (key in curResources) {
            $.each(curResources[key], function(index, value) {
                resourceSelect.append('<option value="' + index + '">' + value + '</option>');
            });
        }
    }
    resourceSelect.selectpicker("refresh");
});

$("#resource").on("changed.bs.select", function(event, clickedIndex, newValue, oldValue) {
    if (newValue) {
        hideSelectionHint(true);
        hideSpinner(false);
        hideTranslationRow(true);

        var app = $("#app").val();
        var resource = $(this).val();

        $.ajax({
            url: translationsPath + "/app/" + app + "/resource/" + resource,
            dataType: "json",
            method: "GET"
        })
        .done(function(data) {
            var table = $("#translations").DataTable();

            table.clear()
                .rows.add(data)
                .draw();
            hideSelectionHint(true);
            hideSpinner(true);
            hideTranslationRow(false);
            enableBootstrapTooltips();
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            setSelectionNeededState();
        });
    } else {
        setSelectionNeededState();
    }
});

$("#translations").on("click", ".translationDetails", function(event) {
    var app = $("#app").val();
    var resource = $("#resource").val();
    var entry = $(event.target).data("entryid");

    $.ajax({
        url: detailsPath + "/app/" + app + "/resource/" + resource + "/entry/" + entry,
        dataType: "json",
        method: "GET"
    })
    .done(function(data) {
        if ($.type(data) == "object" && data["modal"]) {
            $("#modalContainer").html(data["modal"]);
            $("#modalDetails").on("shown.bs.modal", function (e) {
            	initSuggestions();
                $("#modalContainer #suggestions").DataTable()
                    .rows
                    .add(suggestionData)
                    .draw();
                enableBootstrapTooltips();
            }).on("show.bs.modal", function (e) {
                refreshTranslation = false;
            }).on("hidden.bs.modal", function (e) {
            	$("#modalContainer #suggestions").DataTable().destroy();
            	if (refreshTranslation) {
            	    refreshTranslationEntry(app, resource, entry);
            	}
                enableBootstrapTooltips();
            }).modal();
        } else {
            showModalError();
        }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
    	showModalError();
    });
});

$("#modalContainer").on("click", ".toggleNotificationStatus", function(event) {
    var button = $(this);

	if (button.hasClass("disabled")) {
        return;
    }

    var app = $("#app").val();
    var resource = $("#resource").val();
    var entry = button.data("entryid");
    var notificationstatus = button.data("notificationstatus");

    if (notificationstatus < 0) {
    	notificationstatus = 0;
    } else if (notificationstatus > 1) {
    	notificationstatus = 1;
    }

    if (notificationstatus == 0) {
    	notificationstatus = 1;
    } else {
    	notificationstatus = 0;
    }

    hideBootstrapTooltips();
	hideModalSpinner(false);

    $.ajax({
        url: setnotificationstatusPath + "/app/" + app + "/resource/" + resource + "/entry/" + entry + "/notificationstatus/" + notificationstatus,
        dataType: "json",
        method: "GET"
    })
    .done(function(data) {
        if ($.type(data) == "object" && data.hasOwnProperty("notificationStatus")) {
        	button.data("notificationstatus", data["notificationStatus"]);
        	var newClass = "btn-default";
        	if (data["notificationStatus"] == 1) {
        		newClass = "btn-warning";
        	}
        	button.removeClass("btn-default btn-warning").addClass(newClass);
            enableBootstrapTooltips();
            refreshTranslation = true;
        } else {
            addModalAlertMessage();
        }
    	hideModalSpinner(true);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        addModalAlertMessage();
    	hideModalSpinner(true);
    });
});

$("#modalContainer").on("click", ".suggestionVote", function(event) {
    var button = $(this);

	if (button.hasClass("disabled")) {
        return;
    }

    var app = $("#app").val();
    var resource = $("#resource").val();
    var suggestion = button.data("suggestionid");
    var vote = button.data("vote");

    hideBootstrapTooltips();
	hideModalSpinner(false);

    $.ajax({
        url: suggestionvotePath + "/app/" + app + "/resource/" + resource + "/entry/" + suggestionEntryId + "/suggestion/" + suggestion + "/vote/" + vote,
        dataType: "json",
        method: "GET"
    })
    .done(function(data) {
        if ($.type(data) == "object" && data["suggestion"]) {
            $("#modalContainer #suggestions").DataTable()
                .row("#suggestion-" + suggestion)
                .data(data["suggestion"])
                .draw(false);
            enableBootstrapTooltips();
        } else {
            addModalAlertMessage();
        }
    	hideModalSpinner(true);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        addModalAlertMessage();
    	hideModalSpinner(true);
    });
});

$("#modalContainer").on("click", ".suggestionEdit", function(event) {
    var button = $(this);

	if (button.hasClass("disabled")) {
        return;
    }

    var id = 0;
    var suggestion = button.data("suggestion");
    if ($.type(suggestion) === "object" && $.type(suggestion.id) === "number") {
        id = suggestion.id;
    } else {
        suggestion = null;
    }

    hideBootstrapTooltips();

	var edit = $("#modalContainer #suggestionAddEdit");
	if (edit.hasClass("in")) {
	    edit.one("hidden.bs.collapse", function () {
	        showSuggestionAddEdit(id, suggestion);
	    }).collapse("hide");
	} else {
	    showSuggestionAddEdit(id, suggestion);
	}
});

$("#modalContainer").on("click", ".suggestionAddEditSubmit", function(event) {
    var button = $(this);

	if (button.hasClass("disabled")) {
        return;
    }

    var app = $("#app").val();
    var resource = $("#resource").val();
    var suggestion = button.data("suggestionid");
    var data = {};

    if (suggestionType == "String") {
        data.value = $("#modalContainer #suggestionAddEditText").val();
    }

    hideBootstrapTooltips();
	hideModalSpinner(false);

    $.ajax({
        url: suggestionaddeditPath + "/app/" + app + "/resource/" + resource + "/entry/" + suggestionEntryId + "/suggestion/" + suggestion,
        data: data,
        dataType: "json",
        method: "POST"
    })
    .done(function(data) {
        if ($.type(data) == "object" && data["suggestion"]) {
            var table = $("#modalContainer #suggestions").DataTable();
            if (suggestion == 0) {
                table.row
                	.add(data["suggestion"]);
            } else {
                table.row("#suggestion-" + suggestion)
                  .data(data["suggestion"]);
        	}
            table.draw(false);

            $("#modalContainer #suggestionAddEdit").collapse("hide");
            enableBootstrapTooltips();
            refreshTranslation = true;
        } else {
            addModalAlertMessage();
        }
    	hideModalSpinner(true);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        addModalAlertMessage();
    	hideModalSpinner(true);
    });
});

$("#modalContainer").on("click", ".suggestionDelete", function(event) {
    var button = $(this);

	if (button.hasClass("disabled")) {
        return;
    }

    var app = $("#app").val();
    var resource = $("#resource").val();
    var suggestion = $(this).data("suggestionid");

	if (suggestion == 0) {
        return;
    }

    hideBootstrapTooltips();
	hideModalSpinner(false);

    $.ajax({
        url: suggestiondeletePath + "/app/" + app + "/resource/" + resource + "/entry/" + suggestionEntryId + "/suggestion/" + suggestion,
        dataType: "json",
        method: "GET"
    })
    .done(function(data) {
        if (($.type(data) == "object") && ("deleted" in data)) {
            if (data["deleted"]) {
                var table = $("#modalContainer #suggestions").DataTable();
                table.row("#suggestion-" + suggestion)
                  .remove();
                table.draw(false);
        	}

            $("#modalContainer #suggestionAddEdit").collapse("hide");
            refreshTranslation = true;
        } else {
            addModalAlertMessage();
        }
    	hideModalSpinner(true);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        addModalAlertMessage();
    	hideModalSpinner(true);
    });
});

$("#modalContainer").on("click", ".suggestionAccept", function(event) {
    var app = $("#app").val();
    var resource = $("#resource").val();
    var suggestion = $(this).data("suggestionid");

    hideBootstrapTooltips();
    hideModalSpinner(false);

    $.ajax({
        url: suggestionacceptPath + "/app/" + app + "/resource/" + resource + "/entry/" + suggestionEntryId + "/suggestion/" + suggestion,
        dataType: "json",
        method: "GET"
    })
    .done(function(data) {
        if (($.type(data) == "object") && ("accepted" in data)) {
            if (data["accepted"]) {
                refreshTranslation = true;
            }
            $("#modalDetails").modal("hide");
        } else {
            addModalAlertMessage();
        }
        hideModalSpinner(true);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        addModalAlertMessage();
        hideModalSpinner(true);
    });
});
