/*!
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
var curResources = resources;
var refreshTranslation = false;

function enableBootstrapTooltips() {
    $(function() {
        $('[data-toggle="tooltip"]').tooltip({container: 'body'});
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
        var table = $('#translations').DataTable();
    }
}

function setSelectionNeededState() {
    hideSelectionHint(false);
    hideSpinner(true);
    hideTranslationRow(true);
}

function showModalError() {
	$('#modalError').modal();
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
    $('#modalDetails').animate({
        scrollTop: messages.offset().top
    });
}

function showSuggestionAddEdit(id, suggestion) {
    var editArea = $('#modalContainer #suggestionAddEdit');
    var button = $('#modalContainer #suggestionAddEdit');
    var focusElement = null;

    editArea.find(".suggestionAddEditSubmit").data("suggestionid", id);

    if (suggestionType == 'String') {
        var value = "";
        if (($.type(suggestion) === "object") && ($.type(suggestion.value) === "string")) {
            value = suggestion.value;
        }

        var textArea = $('#modalContainer #suggestionAddEditText');
        textArea.val(value);
        focusElement = textArea;
    }

    editArea.collapse('show');
    $('#modalDetails').animate({
        scrollTop: editArea.offset().top
    });

    if (focusElement !== null) {
        focusElement.focus();
    }
}

function refreshTranslationEntry(app, resource, entry) {
    hideModalSpinner(false);

    $.ajax({
        url: translationsPath + "/app/" + app + "/resource/" + resource + '/entry/' + entry,
        dataType: "json",
        method: "GET"
    })
    .done(function(data) {
        if ($.type(data) == 'array' && data.length == 1) {
            var table = $('#translations').DataTable();

            table.row('#translation-' + entry)
                .data(data[0])
                .draw();

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
	appSelect.selectpicker('deselectAll');
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
            var table = $('#translations').DataTable();

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
        if ($.type(data) == 'object' && data['modal']) {
            $("#modalContainer").html(data['modal']);
            $('#modalDetails').on('shown.bs.modal', function (e) {
            	initSuggestions();
                $('#modalContainer #suggestions').DataTable()
                    .rows
                    .add(suggestionData)
                    .draw();
                enableBootstrapTooltips();
            }).on('show.bs.modal', function (e) {
                refreshTranslation = false;
            }).on('hidden.bs.modal', function (e) {
            	$('#modalContainer #suggestions').DataTable().destroy();
            	if (refreshTranslation) {
            	    refreshTranslationEntry(app, resource, entry);
            	}
            }).modal();
        } else {
            showModalError();
        }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
    	showModalError();
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
        if ($.type(data) == 'object' && data['suggestion']) {
            $('#modalContainer #suggestions').DataTable()
                .row('#suggestion-' + suggestion)
                .data(data['suggestion'])
                .draw();
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

	var edit = $('#modalContainer #suggestionAddEdit');
	if (edit.hasClass("in")) {
	    edit.one('hidden.bs.collapse', function () {
	        showSuggestionAddEdit(id, suggestion);
	    }).collapse('hide');
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

    if (suggestionType == 'String') {
        data.value = $('#modalContainer #suggestionAddEditText').val();
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
        if ($.type(data) == 'object' && data['suggestion']) {
            var table = $('#modalContainer #suggestions').DataTable();
            if (suggestion == 0) {
                table.row
                	.add(data['suggestion']);
            } else {
                table.row('#suggestion-' + suggestion)
                  .data(data['suggestion']);
        	}
            table.draw();

            $('#modalContainer #suggestionAddEdit').collapse('hide');
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
